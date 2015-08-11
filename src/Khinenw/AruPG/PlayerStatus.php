<?php

namespace Khinenw\AruPG;

use Khinenw\AruPG\event\status\XPChangeEvent;
use pocketmine\Server;

class PlayerStatus extends Status{
	public $sp = 0;
	public $ap = 0;
	private $xp = 0;

	public $level = 0;

	private $owner = null;

	const SP = "sp";
	const AP = "ap";
	const XP = "xp";
	const LEVEL = "level";

	public function __construct(array $status = [], RPGPlayer $owner){
		parent::__construct($status);
		$this->owner = $owner;
		$this->sp = (isset($status[self::SP])) ? $status[self::SP] : $this->sp;
		$this->ap = (isset($status[self::AP])) ? $status[self::AP] : $this->ap;
		$this->xp = (isset($status[self::XP])) ? $status[self::XP] : $this->xp;
		$this->level = (isset($status[self::LEVEL])) ? $status[self::LEVEL] : $this->level;
	}

	public function getXp(){
		return $this->xp;
	}

	public function setXp($xp){
		Server::getInstance()->getPluginManager()->callEvent(new XPChangeEvent(ToAruPG::getInstance(), $this->owner, $this->xp, $xp));
		$this->xp = $xp;
	}

	/*public function setMaxHp($maxHp){
		parent::setMaxHp($maxHp);
		$this->getOwner()->getPlayer()->setMaxHealth($this->getOwner()->getFinalValue(self::MAX_HP));
	}*/

	public function getOwner(){
		return $this->owner;
	}

	public function getSaveData(){
		return [
			self::MAX_HP => $this->getMaxHp(),
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
