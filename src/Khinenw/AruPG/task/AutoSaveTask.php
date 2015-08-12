<?php

namespace Khinenw\AruPG\task;

use Khinenw\AruPG\ToAruPG;
use pocketmine\scheduler\PluginTask;

class AutoSaveTask extends PluginTask{
	public function onRun($currentTick){
		ToAruPG::getInstance()->saveAll();
	}
}