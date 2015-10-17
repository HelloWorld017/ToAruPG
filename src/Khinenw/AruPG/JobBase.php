<?php

namespace Khinenw\AruPG;

abstract class JobBase implements Job{
	const ID = 0;
	const NAME = "JOB";

	public static function getId(){
		return static::ID;
	}

	public static function getName(){
		return static::NAME;
	}

	public static function getFinalDamage(RPGPlayer $player){
		ToAruPG::randomizeDamage(self::getBaseDamage($player) + self::getAdditionalBaseDamage($player), self::getApproximation($player));
	}
}
