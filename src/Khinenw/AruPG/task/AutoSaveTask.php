<?php

namespace Khinenw\AruPG\task;

use Khinenw\AruPG\ToAruPG;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class AutoSaveTask extends PluginTask{
	public function onRun($currentTick){
		ToAruPG::getInstance()->saveAll();
		ToAruPG::getInstance()->getLogger()->info(TextFormat::AQUA."Auto-saved!");
	}
}