<?php

namespace Khinenw\AruPG;

use Khinenw\AruPG\event\status\XPChangeEvent;
use Khinenw\AruPG\task\HealTask;
use Khinenw\AruPG\task\UITask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class ToAruPG extends PluginBase implements Listener{
	private static $instance = null;

	private static $translation = [];

	/**
	 * @var $players RPGPlayer[]
	 */
	private $players;

	/**
	 * @return ToAruPG
	 */
	public static function getInstance(){
		return self::$instance;
	}

	public function onEnable(){
		@mkdir($this->getDataFolder());
		self::$instance = $this;
		self::$translation = (new Config($this->getDataFolder()."translation.yml", Config::YAML))->getAll();
		$this->players = [];
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new HealTask($this), 1200);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new UITask($this), 15);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable(){
		foreach($this->players as $name => $rpgPlayer){
			file_put_contents($this->getDataFolder().$rpgPlayer->getPlayer()->getName().".player", json_encode($rpgPlayer->getSaveData()));
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
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

				$sender->sendMessage(TextFormat::GREEN."==========".self::getTranslation($skill->getName())."Lv.".$skill->getLevel()." ==========\n".$skill->getSkillDescription());
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
				}
				break;

			case "iap":
				if(count($args) < 1) return false;

				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return true;
				}

				if($this->players[$sender->getName()]->getStatus()->sp < 1){
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
				}elseif($lower === "maxmp"){
					$this->players[$sender->getName()]->getStatus()->maxMp += 100;
				}else{
					$this->players[$sender->getName()]->getStatus()->$lower++;
				}

				$this->players[$sender->getName()]->getStatus()->ap--;
				break;
		}

		return true;
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		if(isset($this->players[$event->getPlayer()->getName()])) return;
		$dataFile = $this->getDataFolder().$event->getPlayer()->getName().".player";

		if(is_file($dataFile)){
			$data = json_decode(file_get_contents($dataFile), true);
			$this->players[$event->getPlayer()->getName()] = RPGPlayer::getFromSaveData($event->getPlayer(),$data);
		}else{
			$this->players[$event->getPlayer()->getName()] = new RPGPlayer($event->getPlayer());
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event){
		if($this->isValidPlayer($event->getPlayer())){
			file_put_contents($this->getDataFolder().$event->getPlayer()->getName().".player", json_encode($this->players[$event->getPlayer()->getName()]->getSaveData()));
			unset($this->players[$event->getPlayer()->getName()]);
		}
	}

	public function onEntityDamage(EntityDamageEvent $event){
		$player = $event->getEntity();

		if(!($player instanceof Player)) return;
		if(!$this->isValidPlayer($player)) return;

		$formerHealth = $this->players[$player->getName()]->health;
		$this->players[$player->getName()]->health -= $event->getFinalDamage();

		if($this->players[$player->getName()]->health >= 20){
			$event->setDamage(0);
		}elseif($formerHealth > 20){
			$event->setDamage($event->getFinalDamage() - ($formerHealth - 20));
		}

		if(($player->getHealth() - $event->getFinalDamage()) <= 0){
			$this->players[$player->getName()]->health = $this->players[$player->getName()]->getStatus()->getMaxHp();
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
		if($this->isValidPlayer($event->getPlayer())){
			$player = $this->players[$event->getPlayer()->getName()];
			$skill = $player->getSkillByItem($event->getItem());
			/**
			 * @var $skill Skill
			 */
			if($skill !== null){
				if($player->mana >= $skill->getRequiredMana()){
					if($skill->onActiveUse($event)){
						$player->mana -= $skill->getRequiredMana();
					}
				}else{
					$player->getPlayer()->sendMessage(TextFormat::RED."NO_MANA");
				}
			}
		}
	}

	public function onPlayerItemHeld(PlayerItemHeldEvent $event){
		if($this->isValidPlayer($event->getPlayer())){
			$player = $this->players[$event->getPlayer()->getName()];
			$skill = $player->getSkillByItem($event->getItem());
			/**
			 * @var $skill Skill
			 */
			if($skill !== null){
				$event->getPlayer()->sendPopup(self::getTranslation($skill->getName()));
			}
		}
	}

	public function onXpChange(XPChangeEvent $event){
		//TODO send EXPPacket
	}

	public function heal(){
		foreach($this->players as $playerName => $rpgPlayer){
			$hp = $rpgPlayer->health + ($rpgPlayer->getFinalValue(Status::MAX_HP) / 10);
			$mp = $rpgPlayer->mana + ($rpgPlayer->getFinalValue(Status::MAX_MP) / 10);
			$rpgPlayer->mana = ($mp > $rpgPlayer->getFinalValue(Status::MAX_MP)) ? $rpgPlayer->getFinalValue(Status::MAX_MP) : $mp;
			$rpgPlayer->health = ($hp > $rpgPlayer->getFinalValue(Status::MAX_HP)) ? $rpgPlayer->getFinalValue(Status::MAX_HP) : $hp;
			if($rpgPlayer->getPlayer()->getHealth() < 20){
				if($rpgPlayer->health >= 20){
					$rpgPlayer->getPlayer()->setHealth(20);
				}else{
					$rpgPlayer->getPlayer()->setHealth($rpgPlayer->health);
				}
			}
		}
	}

	public function showUi(){
		foreach($this->players as $name => $rpg){
			$rpg->getPlayer()->sendTip(
				TextFormat::RED.
				$this->drawProgress($rpg->health, $rpg->getFinalValue(Status::MAX_HP), 30, self::getTranslation("HP"), TextFormat::RED, TextFormat::WHITE)."\n".
				TextFormat::BLUE.
				$this->drawProgress($rpg->mana, $rpg->getFinalValue(Status::MAX_MP), 30, self::getTranslation("MP"), TextFormat::BLUE, TextFormat::WHITE)
			);
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
			$translation = str_replace($argKey, $argValue, $translation);
		}

		return $translation;
	}


	public static function addTranslation($key, $value){
		self::$translation[$key] = $value;
	}
}
