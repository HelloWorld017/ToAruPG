<?php

/*
 * ////////////////External Plugins////////////////
 * WORKING add Dungeon
 * WORKING add Potions
 * WORKING add Jobs/Skills
 * DONE add Skill/Job shop
 * TODO add Hestia Knife
 */

namespace Khinenw\AruPG;

use Khinenw\AruPG\event\skill\SkillAcquireEvent;
use Khinenw\AruPG\event\status\StatusInvestEvent;
use Khinenw\AruPG\task\AutoSaveTask;
use Khinenw\AruPG\task\HealTask;
use Khinenw\AruPG\task\UITask;
use Khinenw\XcelUpdater\UpdatePlugin;
use Khinenw\XcelUpdater\XcelUpdater;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Attribute;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\FurnaceBurnEvent;
use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\BigShapedRecipe;
use pocketmine\inventory\BigShapelessRecipe;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class ToAruPG extends UpdatePlugin implements Listener{
	private static $instance = null;

	private static $translation = [];

    private static $configuration = [];

	private $respawnAdd = [];

	public static $pvpEnabled = false;

	/**
	 * @var $players RPGPlayer[]
	 */
	private $players;

    const ATTRIBUTE_HUNGER = 72;

	/**
	 * @return ToAruPG
	 */
	public static function getInstance(){
		return self::$instance;
	}

	public function onEnable(){
		@mkdir($this->getDataFolder());

        self::$instance = $this;
		self::$translation = (new Config($this->getDataFolder()."translation.yml", Config::YAML, yaml_parse(stream_get_contents($this->getResource("translation.yml")))))->getAll();
        self::$configuration = (new Config($this->getDataFolder()."config.yml", Config::YAML))->getAll();
		self::$pvpEnabled = self::getConfiguration("pvp-enabled", false);

		XcelUpdater::chkUpdate($this);

        $this->players = [];
        JobManager::registerJob(new JobAdventure());

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new HealTask($this), 1200);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new UITask($this), 15);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$autoSaveTerm = self::getConfiguration("auto-save", 10);

		if($autoSaveTerm < 0){
			$this->getLogger()->alert(TextFormat::YELLOW."Auto save turned-off!");
		}else{
			$this->getServer()->getScheduler()->scheduleRepeatingTask(new AutoSaveTask($this), $autoSaveTerm * 60 * 20);
		}

        Attribute::addAttribute(self::ATTRIBUTE_HUNGER, "player.huger", 0, 20, 20, true);

	}

	public function onDisable(){
		foreach($this->players as $name => $rpgPlayer){
			file_put_contents($this->getDataFolder().$rpgPlayer->getPlayer()->getName().".player", json_encode($rpgPlayer->getSaveData()));
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){

		if($command->getName() === "saveall"){
			$this->saveAll();
			return true;
		}

		if($command->getName() === "addtrans"){
			if(count($args) < 1) return false;

			$fileName = "translation_".$args[0].".yml";

			if(($res = $this->getResource($fileName)) !== null){
				$trans =  $res;
			}else{
				if(!is_file($fileName)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("TRANSLATION_FILE_DOESNT_EXIST"));

					return true;
				}

				$trans = fopen($this->getDataFolder().$fileName, "rb");
			}

			$this->forceAddAllTranslation($trans);
			return true;
		}

		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED.$this->getTranslation("MUST_INGAME"));
			return true;
		}

		switch($command->getName()){
			case "skill":
				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return true;
				}

				$skill = $this->players[$sender->getName()]->getSkillByItem($sender->getInventory()->getItemInHand());
				if($skill === null){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NO_SKILL_ITEM"));
					return true;
				}
				/**
				 * @var $skill Skill
				 */

				$sender->sendMessage(TextFormat::GREEN."==========".self::getTranslation($skill->getName())." ".self::getTranslation("LV").".".$skill->getLevel()." ==========\n".$skill->getSkillDescription());
				break;

			case "isp":
				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return true;
				}

				if($this->players[$sender->getName()]->getStatus()->sp < 1){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("INSUFFICIENT_SP"));
					return true;
				}

				$skill = $this->players[$sender->getName()]->getSkillByItem($sender->getInventory()->getItemInHand());
				if($skill === null){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NO_SKILL_ITEM"));
					return true;
				}

				/**
				 * @var $skill Skill
				 */

				if($skill->canInvestSP(1)){
					$skill->investSP(1);
					$sender->sendMessage(TextFormat::AQUA.self::getTranslation("INVESTED_SP"));
					$this->getServer()->getPluginManager()->callEvent(new StatusInvestEvent($this, $this->players[$sender->getName()], StatusInvestEvent::SKILL, $skill->getId()));
					$this->players[$sender->getName()]->getStatus()->sp--;
				}else{
					$sender->sendMessage(TextFormat::RED.self::getTranslation("CANNOT_INVEST_SP"));
				}

				break;

			case "iap":
				if(count($args) < 1) return false;

				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return true;
				}

				if($this->players[$sender->getName()]->getStatus()->ap < 1){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("INSUFFICIENT_AP"));
					return true;
				}

				$lower = strtolower($args[0]);
				if($lower !== "maxmp" && $lower !== "maxhp" && !isset($this->players[$sender->getName()]->getStatus()->$lower)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("UNKNOWN_STATUS"));
					return true;
				}

				if($lower === "ap" || $lower === "sp" || $lower === "xp"){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("INVALID_STATUS"));
					return true;
				}

				if($lower === "maxhp"){
					$this->players[$sender->getName()]->getStatus()->setMaxHp($this->players[$sender->getName()]->getStatus()->getMaxHp() + 20);
					$lower = "maxHp";
				}elseif($lower === "maxmp"){
					$this->players[$sender->getName()]->getStatus()->maxMp += 100;
					$lower = "maxMp";
				}else{
					$this->players[$sender->getName()]->getStatus()->$lower++;
				}

				$this->getServer()->getPluginManager()->callEvent(new StatusInvestEvent($this, $this->players[$sender->getName()], $lower));
				$this->players[$sender->getName()]->getStatus()->ap--;

				$sender->sendMessage(TextFormat::AQUA.self::getTranslation("INVESTED_AP"));
				break;

			case "ability":
				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return true;
				}

				$text = TextFormat::GREEN."==========".self::getTranslation("STATUS")."==========";

				$status = [];
				$playerStatus = $this->players[$sender->getName()]->getStatus()->getSaveData();
				foreach($playerStatus as $transkey => $stat){
					$status[$transkey] = "\n" . self::getTranslation(strtoupper($transkey)) . " : " . $stat;
				}

				$armorStatus = $this->players[$sender->getName()]->getArmorStatus()->getSaveData();
				$skillStatus = $status = $this->players[$sender->getName()]->getSkillStatus()->getSaveData();
				foreach($armorStatus as $transkey => $stat){
					if(isset($skillStatus[$transkey])){
						$stat += $skillStatus[$transkey];
					}

					if($stat != 0) $status[$transkey] .= " + ".$stat;
				}

				foreach($status as $transKey => $stat){
					$text .= "\n".TextFormat::GOLD . self::getTranslation(strtoupper($transKey)) . TextFormat::GREEN . " : " .$stat;
				}

				$baseDamage = $this->players[$sender->getName()]->getCurrentJob()->getBaseDamage($this->players[$sender->getName()]);
				$armorDamage = $this->players[$sender->getName()]->getCurrentJob()->getAdditionalBaseDamage($this->players[$sender->getName()]);
				$approximation = $this->players[$sender->getName()]->getCurrentJob()->getApproximation($this->players[$sender->getName()]);

				$text .=
					"\n" .TextFormat::GOLD .
					self::getTranslation("ATTACK_DAMAGE") . " : " .
					TextFormat::GREEN . $baseDamage .
					(($armorDamage === 0 ) ? "" : " + " . $armorDamage) .
					(($approximation === 0) ? "" : "��" . $approximation)
				;

				$sender->sendMessage($text);
				break;

			case "save":
				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return true;
				}
				file_put_contents($this->getDataFolder().$sender->getName().".player", json_encode($this->players[$sender->getName()]->getSaveData()));
				$sender->sendMessage(TextFormat::AQUA.self::getTranslation("SAVED"));
				break;

            case "loadp":
                if($this->isValidPlayer($sender)){
                    $sender->sendMessage(TextFormat::LIGHT_PURPLE.self::getTranslation("ALREADY_VALID"));
                    return true;
                }

                if(isset($this->players[$sender->getName()])){
                    unset($this->players[$sender->getName()]);
                }

                $this->createPlayer($sender);

                if($this->isValidPlayer($sender)){
                    $sender->sendMessage(TextFormat::AQUA . self::getTranslation("NOW_VALID"));
                }else{
                    $sender->sendMessage(TextFormat::RED . self::getTranslation("CANT_MAKE_VALID"));
                }

                break;
		}
		return true;
	}

	public function saveAll(){
		foreach($this->players as $rpg){
			file_put_contents($this->getDataFolder().strtolower($rpg->getPlayer()->getName()).".player", json_encode($rpg->getSaveData()));
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		if(isset($this->players[$event->getPlayer()->getName()])) return;
		$this->createPlayer($event->getPlayer());
	}

    public function createPlayer(Player $player){
        $dataFile = $this->getDataFolder().strtolower($player->getName()).".player";

        if(is_file($this->getDataFolder().$player->getName().".player") && !is_file($dataFile)){
            rename($this->getDataFolder().$player->getName().".player", $dataFile);
        }

        if(is_file($dataFile)){
            $data = json_decode(file_get_contents($dataFile), true);
            $this->players[$player->getName()] = RPGPlayer::getFromSaveData($player,$data);
        }else{
            $this->players[$player->getName()] = new RPGPlayer($player);
        }

        $player->setDisplayName(self::getTranslation("LV").".".$this->players[$player->getName()]->getStatus()->level." ".$player->getDisplayName());
    }

	public function onPlayerQuit(PlayerQuitEvent $event){
		if($this->isValidPlayer($event->getPlayer())){
			file_put_contents($this->getDataFolder().strtolower($event->getPlayer()->getName()).".player", json_encode($this->players[$event->getPlayer()->getName()]->getSaveData()));
			unset($this->players[$event->getPlayer()->getName()]);
		}
	}

	public function onEntityDamage(EntityDamageEvent $event){
		if($event->isCancelled()) return;
		$damagedEntity = $event->getEntity();

        if($event instanceof EntityDamageByEntityEvent && ($event->getFinalDamage() > $damagedEntity->getHealth())){
            $attacker = $event->getDamager();
            if($attacker instanceof Player){
                if($damagedEntity instanceof Player && !self::$pvpEnabled){
                    $event->setCancelled();
                    return;
                }

                $attackerPlayer = $this->getRPGPlayerByName($attacker->getName());
                if($attackerPlayer !== null){
                    $xp = 0;
                    $xpPercentage = 0;

                    foreach($this->getConfiguration("kill-exp", ["default" => 10]) as $id => $amount){
                        if($id === "default" || $id === $damagedEntity::NETWORK_ID){
                            $xp = $amount;
                        }
                    }

                    foreach($this->getConfiguration("kill-exp-percentage", ["default" => 0.01]) as $id => $amount){
                        if($id === "default" || $id === $damagedEntity::NETWORK_ID){
                            $xpPercentage = $amount;
                        }
                    }

                    $attackerPlayer->addXp($xp + ($attackerPlayer->getNeededXP() * ($xpPercentage / 100)));
                }
            }
        }

        if(!($damagedEntity instanceof Player)) return;
		if(!$this->isValidPlayer($damagedEntity)) return;

		$formerHealth = $this->players[$damagedEntity->getName()]->getHealth();

		$this->getRPGPlayerByName($damagedEntity->getName())->setHealth($formerHealth - $event->getFinalDamage());

        $event->setDamage(0);
        $event->setDamage(0, EntityDamageEvent::MODIFIER_STRENGTH);
        $event->setDamage(0, EntityDamageEvent::MODIFIER_WEAKNESS);
        $event->setDamage(0, EntityDamageEvent::MODIFIER_ARMOR);
        $event->setDamage(0, EntityDamageEvent::MODIFIER_RESISTANCE);
	}

	public function onPlayerDeath(PlayerDeathEvent $event){
		if(!$this->isValidPlayer($event->getEntity())) return;
		$event->getEntity()->teleport($this->getServer()->getDefaultLevel()->getSpawnLocation());
		$rpgPlayer = $this->players[$event->getEntity()->getName()];

		$rpgPlayer->setHealth(1);
		$rpgPlayer->mana = 0;

		$rpgPlayer->getStatus()->setXp($rpgPlayer->getStatus()->getXp() * (4 / 5));

		$drops = [];
		$this->respawnAdd[$event->getEntity()->getName()] = [];
		foreach($event->getDrops() as $item){
			if($rpgPlayer->getSkillByItem($item) === null){
				$drops[] = $item;
			}else{
				$this->respawnAdd[$event->getEntity()->getName()][] = $item;
			}
		}

		$event->setDrops($drops);
	}

	public function onPlayerRespawn(PlayerRespawnEvent $event){
		if(!isset($this->respawnAdd[$event->getPlayer()->getName()])) return;

		/**
		 * @var $item Item
		 */
		foreach($this->respawnAdd[$event->getPlayer()->getName()] as $item){
			if(!$event->getPlayer()->getInventory()->contains($item)){
				$event->getPlayer()->getInventory()->addItem($item);
			}
		}
		$player = $this->getRPGPlayerByName($event->getPlayer()->getName());
		if($player !== null){
			$player->notifyXP();
		}
	}

    public function onCraftItem(CraftItemEvent $event){
        /**
         * @var $player Player
         */
        $player = $event->getPlayer();

        $craftingPlayer = $this->getRPGPlayerByName($event->getPlayer());
        if($craftingPlayer === null){
            $player->sendMessage(TextFormat::RED . self::getTranslation("INVALID_PLAYER"));
            $event->setCancelled();
            return;
        }

        $recipe = $event->getRecipe();

        if($recipe instanceof ShapelessRecipe){
            foreach($recipe->getIngredientList() as $item){
                echo $item->getName() . "x" . $item->getCount();
                if($craftingPlayer->getSkillByItem($item) !== null){
                    if(!$player->getInventory()->contains(Item::get($item->getId(), $item->getDamage(), $item->getCount() + 1))){
                        echo "cancelledEvent";
                        $event->setCancelled();
                        return;
                    }
                }
            }
        }

        if($recipe instanceof ShapedRecipe){
            foreach($recipe->getIngredientMap() as $items){
                /**
                 * @var $item Item
                 */
                foreach($items as $item){
                    if($craftingPlayer->getSkillByItem($item) !== null){
                        if(!$player->getInventory()->contains(Item::get($item->getId(), $item->getDamage(), $item->getCount() + 1))){
                            $event->setCancelled();

                            return;
                        }
                    }
                }
            }
        }
    }

    public function onFurnaceBurn(FurnaceBurnEvent $event){
        $fuelSkill = SkillManager::getSkillByItem($event->getFuel());

        if(count($fuelSkill) > 0) $event->setCancelled();
    }

    public function onFurnaceSmelt(FurnaceSmeltEvent $event){
        $itemSkill = SkillManager::getSkillByItem($event->getSource());

        if(count($itemSkill) > 0) $event->setCancelled();
    }

	public function onPlayerItemDrop(PlayerDropItemEvent $event){
		if(!$this->isValidPlayer($event->getPlayer())) return;

		if($this->players[$event->getPlayer()->getName()]->getSkillByItem($event->getItem()) !== null){
			$event->setCancelled();
		}
	}

	public function onPlayerItemConsume(PlayerItemConsumeEvent $event){
		if(!$this->isValidPlayer($event->getPlayer())) return;

		if($this->players[$event->getPlayer()->getName()]->getSkillByItem($event->getItem()) !== null){
			$event->setCancelled();
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		if($this->isValidPlayer($event->getPlayer())){
			$player = $this->players[$event->getPlayer()->getName()];
			$skill = $player->getSkillByItem($event->getItem());
			if($skill !== null) $event->setCancelled();
			if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
			/**
			 * @var $skill Skill
			 */
			if($skill !== null){
				$player->useSkill($skill, $event);
			}
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		if(!$this->isValidPlayer($event->getPlayer())) return;
		$rpg = $this->players[$event->getPlayer()->getName()];
		if($rpg->getSkillByItem($event->getItem()) !== null){
			$event->setCancelled();
		}
	}

	public function onPlayerItemHeld(PlayerItemHeldEvent $event){
        if($event->getItem()->hasCompoundTag() && isset($event->getItem()->getNamedTag()->desc)){
            $event->getPlayer()->sendPopup($event->getItem()->getNamedTag()["Desc"], $event->getItem()->getCustomName());
        }

		if($this->isValidPlayer($event->getPlayer())){
			$player = $this->players[$event->getPlayer()->getName()];
			$skill = $player->getSkillByItem($event->getItem());
			/**
			 * @var $skill Skill
			 */
			if($skill !== null){
				$event->getPlayer()->sendPopup(TextFormat::AQUA . self::getTranslation("LV").".".$skill->getLevel()." ".self::getTranslation($skill->getName()));
			}
		}
	}

    public function onDataPacketSend(DataPacketSendEvent $event){
        $pk = $event->getPacket();
        if($pk instanceof SetHealthPacket){
            $attribute = Attribute::getAttribute(Attribute::MAX_HEALTH);
            $attribute->setMinValue(0)->setMaxValue($event->getPlayer()->getMaxHealth());
            $attribute->setValue($pk->health);

            $attributePacket = new UpdateAttributesPacket();
            $attributePacket->entityId = 0;
            $attributePacket->entries = [
                $attribute
            ];

            $event->setCancelled();
            $event->getPlayer()->dataPacket($attributePacket);
        }
    }

	public function onSkillAcquire(SkillAcquireEvent $event){
		$event->setCancelled(!$event->getSkill()->canBeAcquired($event->getPlayer()));
	}

	public function heal(){
		foreach($this->players as $playerName => $rpgPlayer){
			$hp = $rpgPlayer->getHealth() + ($rpgPlayer->getFinalValue(Status::MAX_HP) / 10);
			$mp = $rpgPlayer->mana + ($rpgPlayer->getFinalValue(Status::MAX_MP) / 10);
			$rpgPlayer->mana = ($mp > $rpgPlayer->getFinalValue(Status::MAX_MP)) ? $rpgPlayer->getFinalValue(Status::MAX_MP) : $mp;
			$rpgPlayer->setHealth(($hp > $rpgPlayer->getFinalValue(Status::MAX_HP)) ? $rpgPlayer->getFinalValue(Status::MAX_HP) : $hp);
		}
	}

	public function showUi(){
		foreach($this->players as $name => $rpg){
			$text = self::getTranslation("LV").".".$rpg->getStatus()->level." ".self::getTranslation($rpg->getCurrentJob()->getName())."\n".
				TextFormat::RED.
				$this->drawProgress($rpg->getHealth(), $rpg->getFinalValue(Status::MAX_HP), 30, self::getTranslation("HP"), TextFormat::RED, TextFormat::GRAY)."\n".
				TextFormat::BLUE.
				$this->drawProgress($rpg->mana, $rpg->getFinalValue(Status::MAX_MP), 30, self::getTranslation("MP"), TextFormat::BLUE, TextFormat::GRAY);

			if($rpg->getStatus()->ap > 0){
				$text .= "\n".TextFormat::YELLOW.self::getTranslation("AP")." : ".$rpg->getStatus()->ap;
			}

			if($rpg->getStatus()->sp > 0){
				$text .= "\n".TextFormat::YELLOW.self::getTranslation("SP")." : ".$rpg->getStatus()->sp;
			}
			$rpg->getPlayer()->sendTip($text);
		}
	}

	public function drawProgress($current, $max, $step, $text, $color, $secondColor){
		$progress = floor($current / $max * $step);
		$text = " ".$text."   ".$color;

		for($i = 0; $i < $step; $i++){
			if($i == $progress) $text .= $secondColor;
			$text .= ":";
		}

		$text .= $color." ".$current."/".$max;
		return $text;
	}

	public function isValidPlayer(Player $player){
		return isset($this->players[$player->getName()]);
	}

	public static function getTranslation($key, ...$args){
		if(!isset(self::$translation[$key])){
			return $key." ".implode(", ", $args);
		}
		$translation = self::$translation[$key];

		foreach($args as $argKey => $argValue){
			$translation = str_replace("%s".($argKey + 1), $argValue, $translation);
		}

		return $translation;
	}

    public static function getConfiguration($key, $defaultValue){
        if(!isset(self::$configuration[$key])){
            self::$configuration[$key] = $defaultValue;
            $conf = new Config(self::getInstance()->getDataFolder()."config.yml", Config::YAML);
            $conf->setAll(self::$configuration);
            $conf->save();

            return $defaultValue;
        }else{
            return self::$configuration[$key];
        }
    }

	public function getRPGPlayerByName($player){
		if($player instanceof Player){
			return ($this->isValidPlayer($player)) ? $this->players[$player->getName()] : null;
		}
		return ($this->isValidPlayer($this->getServer()->getPlayerExact($player))) ? $this->players[$player] : null;
	}

	public static function addTranslation($key, $value, $save = true, $force = false){
		if(!$force){
			if(isset(self::$translation[$key])) return;
		}
		self::$translation[$key] = $value;

		if($save){
			$translate = (new Config(self::getInstance()->getDataFolder()."translation.yml", Config::YAML));
			$translate->setAll(self::$translation);
			$translate->save();
		}
	}

	public static function addAllTranslation($resource){
		$translations = yaml_parse(stream_get_contents($resource));

		foreach($translations as $name => $data){
			self::addTranslation($name, $data, false);
		}

		$translate = (new Config(self::getInstance()->getDataFolder()."translation.yml", Config::YAML));
		$translate->setAll(self::$translation);
		$translate->save();

		fclose($resource);
	}

	public static function forceAddAllTranslation($resource){
		$translations = yaml_parse(stream_get_contents($resource));

		foreach($translations as $name => $data){
			self::addTranslation($name, $data, false, true);
		}

		$translate = (new Config(self::getInstance()->getDataFolder()."translation.yml", Config::YAML));
		$translate->setAll(self::$translation);
		$translate->save();

		fclose($resource);
	}

	public function compVersion($pluginVersion, $repoVersion){
		return $pluginVersion !== $repoVersion;
	}

	public function getPluginYamlURL(){
		return "https://raw.githubusercontent.com/HelloWorld017/ToAruPG/master/plugin.yml";
	}

	public static function randomizeDamage($baseDamage, $approximation){
		return $baseDamage + mt_rand(-$approximation, $approximation);
	}
}
