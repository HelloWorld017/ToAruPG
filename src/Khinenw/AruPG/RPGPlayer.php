<?php

namespace Khinenw\AruPG;

use Khinenw\AruPG\event\job\JobChangeEvent;
use Khinenw\AruPG\event\skill\SkillAcquireEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;

class RPGPlayer{

	private $skills;
	private $job;
	private $player;
	private $status;
	private $armorStatus;
	private $mana;

	/*
	 * TODO add Mana Potion
	 * add str / 10 -> melee damage
	 * add LevelUp
	 * add Skill/Job shop
	 * add Hestia Knife
	 * add AP, SP Stat
	 * add player saving
	 * add /skill command : shows description of current holding item
	 */
	public function __construct(Player $player, array $skills = [], $job = 0, array $status = null, $mana = -1){
		foreach($skills as $skillId){
			$skill = SkillManager::getSkill($skillId);
			$skills[$skill->getItem()->getId().";".$skill->getItem()->getDamage()] = $skill;
		}

		$this->job = JobManager::getJob($job);
		$this->player = $player;
		$this->status = $status;
		$this->mana = $mana;

		$this->status = new PlayerStatus($status);
		$this->armorStatus = new Status([]);

		if($this->mana === -1){
			$this->mana = $this->status["maxmp"];
		}
	}

	public function getSkillByItem(Item $item){
		$tag = $item->getId().";".$item->getDamage();
		return (isset($this->skills[$tag])) ? $this->skills[$tag] : null;
	}

	public function hasSkill($skillId){
		return array_key_exists($skillId, $this->skills);
	}

	public function acquireSkill(Skill $skill){
		$skillAcquireEvent = new SkillAcquireEvent(ToAruPG::getInstance(), $skill);
		Server::getInstance()->getPluginManager()->callEvent($skillAcquireEvent);

		if(!$skillAcquireEvent->isCancelled()){
			$this->skills[$skill->getId()] = $skill;
			$item = $skill->getItem();
			$this->player->getInventory()->addItem($item);
		}
	}

	public function changeJob(Job $job){
		//TODO discard skill which cannot be used by player
		$jobChangeEvent = new JobChangeEvent(ToAruPG::getInstance(), $this->job, $job);
		Server::getInstance()->getPluginManager()->callEvent($jobChangeEvent);

		if(!$jobChangeEvent->isCancelled()){
			$this->job = $job;
		}
	}

	public function addXp($amount){
		$this->status->xp += $amount;

		$needXp = $this->status->level * $this->status->level * 1000 + 1000;

		if($this->status->xp > $needXp){
			$this->status->level++;
		}
	}

}
