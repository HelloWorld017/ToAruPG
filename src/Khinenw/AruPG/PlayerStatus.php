<?php

namespace Khinenw\AruPG;

class PlayerStatus extends Status{
	public $sp = 0;
	public $ap = 0;
	public $xp = 0;
	public $level = 0;

	const SP = "sp";
	const AP = "ap";
	const XP = "xp";
	const LEVEL = "level";

	public function __construct(array $status = []){
		parent::__construct($status);
		$this->sp = (isset($status[self::SP])) ? $status[self::SP] : $this->sp;
		$this->ap = (isset($status[self::AP])) ? $status[self::AP] : $this->ap;
		$this->xp = (isset($status[self::XP])) ? $status[self::XP] : $this->xp;
		$this->level = (isset($status[self::LEVEL])) ? $status[self::LEVEL] : $this->level;
	}

	public function getSaveData(){
		return [
			self::MAX_HP => $this->maxHp,
			self::MAX_MP => $this->maxMp,
			self::STR => $this->str,
			self::INT => $this->int,
			self::DEX => $this->dex,
			self::LUK => $this->luk,
			self::SP => $this->sp,
			self::AP => $this->ap,
			self::XP => $this->xp,
			self::LEVEL => $this->level
		];
	}
}