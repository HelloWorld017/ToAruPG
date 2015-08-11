<?php

namespace Khinenw\AruPG\event\status;

use Khinenw\AruPG\RPGPlayer;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class PlayerLevelupEvent extends PluginEvent implements Cancellable{

	private $player;

	public static $handlerList;

	public function __construct(Plugin $plugin, RPGPlayer $player){
		parent::__construct($plugin);
		$this->player = $player;
	}

	public function getPlayer(){
		return $this->player;
	}

}
