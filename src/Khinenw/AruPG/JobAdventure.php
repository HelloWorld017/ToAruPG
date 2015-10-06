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

	public static function getAdditionalBaseDamage(RPGPlayer $player){
		return $player->getAdditionalValue(Status::STR);
	}

	public static function getApproximation(RPGPlayer $player){
		return $player->getStatus()->level;
	}

	public static function getFinalDamage(RPGPlayer $player){
		ToAruPG::randomizeDamage(self::getBaseDamage($player) + self::getAdditionalBaseDamage($player), self::getApproximation($player));
	}
}
