<?php

namespace Khinenw\AruPG;

class JobAdventure implements Job{
	public static function getName(){
		return "ADVENTURER";
	}

	public static function getId(){
		return 0;
	}

	public static function getSkills(){
		return [];
	}

	public static function getBaseDamage(RPGPlayer $player){
		return $player->getStatus()->str;
	}

	public static function getArmorBaseDamage(RPGPlayer $player){
		return $player->getArmorStatus()->str;
	}

}