<?php
namespace Khinenw\AruPG;

class Status{
	public $maxHp = 20;
	public $maxMp = 100;
	public $str = 10;
	public $int = 10;
	public $dex = 10;
	public $luk = 10;

	const MAX_HP = "maxhp";
	const MAX_MP = "maxmp";
	const STR = "str";
	const INT = "int";
	const DEX = "dex";
	const LUK = "luk";

	public function __construct(array $status = []){
		$this->maxHp = (isset($status[self::MAX_HP])) ? $status[self::MAX_HP] : $this->maxHp;
		$this->maxMp = (isset($status[self::MAX_MP])) ? $status[self::MAX_MP] : $this->maxMp;
		$this->str = (isset($status[self::STR])) ? $status[self::STR] : $this->str;
		$this->int = (isset($status[self::INT])) ? $status[self::INT] : $this->int;
		$this->dex = (isset($status[self::DEX])) ? $status[self::DEX] : $this->dex;
		$this->luk = (isset($status[self::LUK])) ? $status[self::LUK] : $this->luk;
	}

	public function getSaveData(){
		return [
			self::MAX_HP => $this->maxHp,
			self::MAX_MP => $this->maxMp,
			self::STR => $this->str,
			self::INT => $this->int,
			self::DEX => $this->dex,
			self::LUK => $this->luk
		];
	}
}