<?php

namespace Khinenw\AruPG\event\status;

use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class StatusInvestEvent extends PluginEvent implements Cancellable{

	private $investedStat;

	public function __construct(Plugin $plugin, $investedStat){
		parent::__construct($plugin);
		$this->investedStat = $investedStat;
	}

	public function getInvestedStat(){
		return $this->investedStat;
	}

}
