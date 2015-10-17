<?php

namespace Khinenw\AruPG\event\skill;

use Khinenw\AruPG\RPGPlayer;
use Khinenw\AruPG\Skill;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class SkillAcquireEvent extends PluginEvent implements Cancellable{
	private $skill;
	private $player;

	public static $handlerList;

	public function __construct(Plugin $plugin, Skill $skill, RPGPlayer $player){
		parent::__construct($plugin);
		$this->skill = $skill;
        $this->player = $player;
	}

	public function getSkill(){
		return $this->skill;
	}

	public function getPlayer(){
		return $this->player;
	}
}
