<?php

namespace Khinenw\AruPG\event\skill;

use Khinenw\AruPG\Skill;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class SkillDiscardEvent extends PluginEvent implements Cancellable{
	private $skill;

	public function __construct(Plugin $plugin, Skill $skill){
		parent::__construct($plugin);
		$this->skill = $skill;
	}

	public function getSkill(){
		return $this->skill;
	}
}