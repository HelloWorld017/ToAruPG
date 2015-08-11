<?php

namespace Khinenw\AruPG\event\status;

use Khinenw\AruPG\RPGPlayer;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class StatusInvestEvent extends PluginEvent implements Cancellable{

	private $investedStat;
	private $player;
	public static $handlerList;

	public function __construct(Plugin $plugin, RPGPlayer $player, $investedStat){
		parent::__construct($plugin);
		$this->investedStat = $investedStat;
		$this->player = $player;
	}

	public function getInvestedStat(){
		return $this->investedStat;
	}

	public function getPlayer(){
		return $this->player;
	}

}
