<?php

namespace Khinenw\AruPG\event\skill;

use Khinenw\AruPG\RPGPlayer;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class SkillStatusResetEvent extends PluginEvent{
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