<?php

namespace Khinenw\AruPG\event\status;

use Khinenw\AruPG\RPGPlayer;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class XPChangeEvent extends PluginEvent{

	private $oldXp;
	private $newXp;
	private $player;
	public static $handlerList;

	public function __construct(Plugin $plugin, RPGPlayer $player, $oldXp, $newXp){
		parent::__construct($plugin);
		$this->player = $player;
		$this->oldXp = $oldXp;
		$this->newXp = $newXp;
	}

	public function getNewXp(){
		return $this->newXp;
	}

	public function getOldXp(){
		return $this->oldXp;
	}

	public function getPlayer(){
		return $this->player;
	}

}
