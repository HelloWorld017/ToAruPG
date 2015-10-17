<?php

namespace Khinenw\AruPG;

abstract class SkillBase implements Skill{
	const ID = 0;
	const REQ_LEV = 0;
	const NAME = "SKILL";

	private $player;
	private $level;

	public function __construct(RPGPlayer $player = null){
		if($player !== null) $this->player = $player;
		$this->level = 1;
	}

	public function setPlayer(RPGPlayer $player){
		$this->player = $player;
	}

	/**
	 * @return RPGPlayer
	 */
	public function getPlayer(){
		return $this->player;
	}

	public static function getId(){
		return static::ID;
	}

	public static function getRequiredLevel(){
		return static::REQ_LEV;
	}

	public static function getName(){
		return static::NAME;
	}

	public function getLevel(){
		return $this->level;
	}

	public function setLevel($level){
		$this->level = $level;
	}

	public function investSP($sp){
		if($this->canInvestSP($sp)){
			$this->level += $sp;
		}
	}
}
