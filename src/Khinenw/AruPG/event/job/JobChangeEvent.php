<?php

namespace Khinenw\AruPG\event\job;

use Khinenw\AruPG\Job;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\plugin\Plugin;

class JobChangeEvent extends PluginEvent implements Cancellable{

	private $oldJob;
	private $newJob;

	public function __construct(Plugin $plugin, Job $oldJob, Job $newJob){
		parent::__construct($plugin);
	}

	public function getOldJob(){
		return $this->oldJob;
	}

	public function getNewJob(){
		return $this->newJob;
	}

}
