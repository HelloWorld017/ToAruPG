<?php

namespace Khinenw\AruPG;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
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

	public static function getInstance(){
		return self::$instance;
	}

	public function onEnable(){
		@mkdir($this->getDataFolder());
		self::$instance = $this;
		self::$translation = (new Config($this->getDataFolder()."translation.yml", Config::YAML))->getAll();
		$this->players = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable(){
		foreach($this->players as $name => $rpgPlayer){
			file_put_contents($this->getDataFolder().$rpgPlayer->getPlayer()->getName().".player", serialize($rpgPlayer->getSaveData()));
		}
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RED.$this->getTranslation("MUST_INGAME"));
			return;
		}
		switch($command->getName()){
			case "skill":
				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return;
				}

				$skill = $this->players[$sender->getName()]->getSkillByItem($sender->getInventory()->getItemInHand());
				if($skill === null){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NO_SKILL_ITEM"));
					return;
				}
				/**
				 * @var $skill Skill
				 */

				$sender->sendMessage(TextFormat::GREEN."==========".self::getTranslation($skill->getName())."Lv.".$skill->getLevel()." ==========\n".$skill->getSkillDescription());
				break;

			case "isp":
				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return;
				}

				if($this->players[$sender->getName()]->getStatus()->sp < 1){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("INSUFFICIENT_SP"));
					return;
				}

				$skill = $this->players[$sender->getName()]->getSkillByItem($sender->getInventory()->getItemInHand());
				if($skill === null){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NO_SKILL_ITEM"));
					return;
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
				if(!$this->isValidPlayer($sender)){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("NOT_VALID_PLAYER"));
					return;
				}

				if($this->players[$sender->getName()]->getStatus()->sp < 1){
					$sender->sendMessage(TextFormat::RED.self::getTranslation("INSUFFICIENT_AP"));
					return;
				}
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		if(isset($this->players[$event->getPlayer()->getName()])) return;
		$dataFile = $this->getDataFolder().$event->getPlayer()->getName().".player";

		if(is_file($dataFile)){
			$data = unserialize(file_get_contents($dataFile));
			$this->players[$event->getPlayer()->getName()] = new RPGPlayer($event->getPlayer(), $data["skill"], $data["job"], $data["status"], $data["mana"]);
		}else{
			$this->players[$event->getPlayer()->getName()] = new RPGPlayer($event->getPlayer());
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event){
		if($this->isValidPlayer($event->getPlayer())){
			file_put_contents($this->getDataFolder().$event->getPlayer()->getName().".player", serialize($this->players[$event->getPlayer()->getName()]->getSaveData()));
			unset($this->players[$event->getPlayer()->getName()]);
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
