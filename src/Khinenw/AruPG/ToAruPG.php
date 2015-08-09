<?php

namespace Khinenw\AruPG;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class ToAruPG extends PluginBase implements Listener{
	private static $instance = null;

	public function onEnable(){
		self::$instance = $this;
	}

	public static function getInstance(){
		return self::$instance;
	}
}
