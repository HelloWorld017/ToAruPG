<?php

namespace Khinenw\AruPG\event\status;

use Khinenw\AruPG\RPGPlayer;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class StatusInvestEvent extends PluginEvent implements Cancellable{

	const SKILL = 255;

	private $investedStat;
	private $player;
	private $investedSkill;

	public static $handlerList;

	public function __construct(Plugin $plugin, RPGPlayer $player, $investedStat, $investedSkill = null){
		parent::__construct($plugin);
		$this->investedStat = $investedStat;
		$this->investedSkill = $investedSkill;
		$this->player = $player;
	}

	/**
	 * @return int|null
	 */
	public function getInvestedSkillId(){
		return $this->investedSkill;
	}

	public function getInvestedStat(){
		return $this->investedStat;
	}

	public function getPlayer(){
		return $this->player;
	}

}
