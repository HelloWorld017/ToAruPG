<?php

namespace Khinenw\AruPG;

use Khinenw\AruPG\event\job\JobChangeEvent;
use Khinenw\AruPG\event\skill\SkillAcquireEvent;
use Khinenw\AruPG\event\skill\SkillDiscardEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;

class RPGPlayer{

	private $skills;
	private $job;
	private $player;
	private $status;
	private $armorStatus;
	public $mana;
	public $health;

	/*
	 * TODO add Mana Potion
	 * WONTFIX add str / 10 -> melee damage
	 * TODO add Skill/Job shop
	 * TODO add Hestia Knife
	 * TODO add AP, SP Stat
	 * DONE add player saving
	 * DONE add /skill command : shows description of current holding item
	 * DONE add /si command : invest 1 sp to skill whose item is current holding item
	 * DONE add ui
	 * DONE add mana regeneration
	 * DONE add skill level saving
	 * TODO remove skill items when finish
	 * TODO prevent skill items deleting
	 * TODO mana reset when player death
	 * FIXME set health won't send packet
	 */
	public function __construct(Player $player, array $skills = [], $job = 0, array $status = [], $mana = -1, $health = -1){
		$this->player = $player;
		$this->skills = [];
		foreach($skills as $skillTag){
			$skillData = explode(";", $skillTag);
			$skill = SkillManager::getSkill($skillData[0]);
			$skill->setPlayer($this);
			if(count($skillData) > 1){
				$skill->setLevel($skillData[1]);
			}
			$this->skills[$skill->getItem()->getId().";".$skill->getItem()->getDamage()] = $skill;

			if(!$this->player->getInventory()->contains($skill->getItem())){
				$this->player->getInventory()->addItem($skill->getItem());
			}
			$skill->onPassiveInit();
		}

		$this->job = JobManager::getJob($job);
		$this->mana = $mana;
		$this->health = -1;

		$this->status = new PlayerStatus($status, $this);
		$this->armorStatus = new Status([
			Status::MAX_HP => 0,
			Status::MAX_MP => 0,
			Status::STR => 0,
			Status::INT => 0,
			Status::DEX => 0,
			Status::LUK => 0
		]);

		if($this->mana === -1){
			$this->mana = $this->getFinalValue(Status::MAX_MP);
		}

		if($this->health === -1){
			$this->health = $this->getFinalValue(Status::MAX_HP);
		}
	}

	public function getSkillByItem(Item $item){
		$tag = $item->getId().";".$item->getDamage();
		return (isset($this->skills[$tag])) ? $this->skills[$tag] : null;
	}

	public function hasSkill($skillId){
		$skill = SkillManager::getSkill($skillId);
		if($skill === null) return false;
		$item = $skill->getItem();
		return array_key_exists($item->getId().";".$item->getDamage(), $this->skills);
	}

	public function acquireSkill(Skill $skill){
		$skillAcquireEvent = new SkillAcquireEvent(ToAruPG::getInstance(), $skill);
		Server::getInstance()->getPluginManager()->callEvent($skillAcquireEvent);

		if(!$skillAcquireEvent->isCancelled()){
			$this->skills[$skill->getId()] = $skill;
			$item = $skill->getItem();
			if(!$this->player->getInventory()->contains($item)){
				$this->player->getInventory()->addItem($item);
			}
		}
		$skill->onPassiveInit();
	}

	public function changeJob(Job $job){
		$jobChangeEvent = new JobChangeEvent(ToAruPG::getInstance(), $this->job, $job);
		Server::getInstance()->getPluginManager()->callEvent($jobChangeEvent);

		if($jobChangeEvent->isCancelled()) return;
		$this->job = $job;

		foreach($this->skills as $item => $skillId){
			Server::getInstance()->getPluginManager()->callEvent(new SkillDiscardEvent(ToAruPG::getInstance(), $this->skills[$item]));
			unset($this->skills[$item]);
		}

		foreach($job->getSkills() as $skill){
			Server::getInstance()->getPluginManager()->callEvent(new SkillAcquireEvent(ToAruPG::getInstance(), $skill));
			$playerSkill = $skill->setPlayer($this);
			$skills[$skill->getItem()->getId().";".$skill->getItem()->getDamage()] = $playerSkill;
		}
	}

	public function addXp($amount){
		$this->status->setXp($this->status->getXp() + $amount);

		$needXp = $this->status->level * $this->status->level * 1000 + 1000;

		if($this->status->getXp() > $needXp){
			$this->levelUp();
		}
	}

	public function levelUp(){
		$this->status->level++;
		$this->status->sp += 3;
		$this->status->ap += 3;
		$this->status->setMaxHp($this->status->getMaxHp() + 20);
		$this->status->maxMp += 100;
	}

	public function getFinalValue($statusKey){
		return ($this->armorStatus->$statusKey + $this->status->$statusKey);
	}

	public function setArmorStatus(Status $status){
		$this->armorStatus = $status;
	}

	public function getPlayer(){
		return $this->player;
	}

	public function getCurrentJob(){
		return $this->job;
	}

	public function getSaveData(){
		$saveData = [
			"skill" => [],
			"job" => $this->job->getId(),
			"mana" => $this->mana,
			"armorStatus" => $this->armorStatus->getSaveData(),
			"status" => $this->status->getSaveData(),
			"health" => $this->health
		];

		/**
		 * @var $skill Skill
		 */
		foreach($this->skills as $item => $skill){
			$saveData["skill"][] = $skill->getId().";".$skill->getLevel();
		}

		return $saveData;
	}

	public static function getFromSaveData(Player $player, array $saveData){
		$rpgPlayer = new self($player, $saveData["skill"], $saveData["job"], $saveData["status"], $saveData["mana"], $saveData["health"]);
		$rpgPlayer->setArmorStatus(new Status($saveData["armorStatus"]));
		return $rpgPlayer;
	}

	public function getStatus(){
		return $this->status;
	}
}
