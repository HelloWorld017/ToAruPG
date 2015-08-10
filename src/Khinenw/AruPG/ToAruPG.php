<?php

namespace Khinenw\AruPG;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
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
			unset($this->players[$event->getPlayer()->getName()]);
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
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
				}
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
}
