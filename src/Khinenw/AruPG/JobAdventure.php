<?php

namespace Khinenw\AruPG;

class JobAdventure implements Job{
	public static function getName(){
		return "Adventurer";
	}

	public static function getId(){
		return 0;
	}

	public static function getSkills(){
		return [];
	}

}