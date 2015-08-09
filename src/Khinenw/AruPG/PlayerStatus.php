<?php

namespace Khinenw\AruPG;

class PlayerStatus extends Status{
	public $sp = 0;
	public $ap = 0;
	public $xp = 0;
	public $level = 0;

	const SP = 5;
	const AP = 6;
	const XP = 7;
	const LEVEL = 8;

	public function __construct(array $status = []){
		parent::__construct($status);
		$this->sp = (isset($status[self::SP])) ? $status[self::SP] : $this->sp;
		$this->ap = (isset($status[self::AP])) ? $status[self::AP] : $this->ap;
		$this->xp = (isset($status[self::XP])) ? $status[self::XP] : $this->xp;
		$this->level = (isset($status[self::LEVEL])) ? $status[self::LEVEL] : $this->level;
	}
}