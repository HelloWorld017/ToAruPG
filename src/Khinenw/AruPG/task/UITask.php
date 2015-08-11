<?php

namespace Khinenw\AruPG\task;

use Khinenw\AruPG\ToAruPG;
use pocketmine\scheduler\PluginTask;

class UITask extends PluginTask{
	public function onRun($currentTick){
		ToAruPG::getInstance()->showUi();
	}
}