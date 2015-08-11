<?php

namespace Khinenw\AruPG\event\status;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Status;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class ArmorChangeEvent extends PluginEvent{
	private $player;
	private $oldArmorStatus;
	private $newArmorStatus;

	public static $handlerList;

	public function __construct(Plugin $plugin, RPGPlayer $player, Status $oldArmorStatus, Status $newArmorStatus){
		parent::__construct($plugin);

		$this->player = $player;
		$this->oldArmorStatus = $oldArmorStatus;
		$this->newArmorStatus = $newArmorStatus;
	}

	public function getNewArmorStatus(){
		return $this->newArmorStatus;
	}

	public function getPlayer(){
		return $this->player;
	}

	public function getOldArmorStatus(){
		return $this->oldArmorStatus;
	}
}
