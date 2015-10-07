<?php

namespace Khinenw\AruPG;

class JobAdventure extends JobBase{
	const ID = 0;
	const NAME = "ADVENTURER";

	public static function getSkills(){
		return [];
	}

	public static function getBaseDamage(RPGPlayer $player){
		return $player->getStatus()->str;
	}

	public static function getAdditionalBaseDamage(RPGPlayer $player){
		return $player->getAdditionalValue(Status::STR);
	}

	public static function getApproximation(RPGPlayer $player){
		return $player->getStatus()->level;
	}
}
