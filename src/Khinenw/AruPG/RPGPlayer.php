<?php

namespace Khinenw\AruPG;

use Khinenw\AruPG\event\job\JobChangeEvent;
use Khinenw\AruPG\event\skill\SkillAcquireEvent;
use Khinenw\AruPG\event\skill\SkillDiscardEvent;
use Khinenw\AruPG\event\skill\SkillStatusResetEvent;
use Khinenw\AruPG\event\status\ArmorChangeEvent;
use Khinenw\AruPG\event\status\PlayerLevelupEvent;
use pocketmine\entity\Attribute;
use pocketmine\item\Item;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use pocketmine\Server;

class RPGPlayer{

	private $skills;
	private $job;
	private $player;
	private $status;
	private $armorStatus;
	private $skillStatus;
	public $mana;
	public $health;

	const MAX_LEVEL = 100;

	public function __construct(Player $player, array $skills = [], $job = 0, array $status = [], $mana = -1, $health = -1){
		$this->player = $player;

		$this->job = JobManager::getJob($job);

		$this->status = new PlayerStatus($status, $this);
		$this->armorStatus = new Status([
			Status::MAX_HP => 0,
			Status::MAX_MP => 0,
			Status::STR => 0,
			Status::INT => 0,
			Status::DEX => 0,
			Status::LUK => 0
		]);

		$this->skillStatus = new Status([
			Status::MAX_HP => 0,
			Status::MAX_MP => 0,
			Status::STR => 0,
			Status::INT => 0,
			Status::DEX => 0,
			Status::LUK => 0
		]);

		$this->mana = ($mana === -1) ? $this->getFinalValue(Status::MAX_MP) : $mana;
		$this->health = ($health === -1) ? $health : $this->getFinalValue(Status::MAX_HP);

		$this->skills = [];
		foreach($skills as $skillTag){
			$skillData = explode(";", $skillTag);
			$skill = SkillManager::getSkill($skillData[0]);
			$skill->setPlayer($this);
			if(count($skillData) > 1){
				$skill->setLevel($skillData[1]);
			}
			$this->skills[$skill->getItem()->getId().";".$skill->getItem()->getDamage()] = $skill;

			$skill->onPassiveInit();
		}

		$this->notifyXP();
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

	/**
	 * @param $skillId
	 * @return Skill|null
	 */
	public function getSkillById($skillId){
		$skill = SkillManager::getSkill($skillId);
		if($skill === null) return false;
		$item = $skill->getItem();
		return array_key_exists($item->getId().";".$item->getDamage(), $this->skills) ? $this->skills[$item->getId().";".$item->getDamage()] : null;
	}

	public function acquireSkill(Skill $skill){
		$skillAcquireEvent = new SkillAcquireEvent(ToAruPG::getInstance(), $skill);
		Server::getInstance()->getPluginManager()->callEvent($skillAcquireEvent);
		$skill->setPlayer($this);

		if(!$skillAcquireEvent->isCancelled()){
			$item = $skill->getItem();
			$item->setCustomName(ToAruPG::getTranslation($skill->getName()));
			$this->skills[$item->getId().";".$item->getDamage()] = $skill;
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

		foreach($this->skills as $item => $skill){
			Server::getInstance()->getPluginManager()->callEvent(new SkillDiscardEvent(ToAruPG::getInstance(), $skill));
			unset($this->skills[$item]);
		}

		$level = $this->getStatus()->level;
		$this->status = new PlayerStatus([], $this);

		$this->status->level = $level;
		$this->getStatus()->sp = $this->getStatus()->level * 3;
		$this->getStatus()->ap = $this->getStatus()->level * 5;
		$this->resetSkillStatus();
	}

	public function resetSkillStatus(){
		$this->skillStatus = new Status([]);
		Server::getInstance()->getPluginManager()->callEvent(new SkillStatusResetEvent(ToAruPG::getInstance(), $this));
		foreach($this->skills as $skill){
			if($skill instanceof PassiveSkill){
				$skill->onSkillStatusReset();
			}
		}
	}

	public function addXp($amount){
		$this->status->setXp($this->status->getXp() + $amount);

		$needXp = $this->status->level * $this->status->level * 1000 + 1000;

		if($this->status->getXp() > $needXp){
			$this->levelUp();
		}

		$this->notifyXP();
	}

	public function notifyXP(){
		$lvAttribute = Attribute::getAttribute(Attribute::EXPERIENCE_LEVEL)->setMaxValue(self::MAX_LEVEL)->setValue($this->getStatus()->level);

		$needXp = $this->status->level * $this->status->level * 1000 + 1000;

		$expAttribute = Attribute::getAttribute(Attribute::EXPERIENCE)->setValue($this->getStatus()->getXp() / $needXp);

		$pk = new UpdateAttributesPacket();
		$pk->entityId = 0;
		$pk->entries = [
			$expAttribute,
			$lvAttribute
		];

		$this->getPlayer()->dataPacket($pk);
	}

	public function levelUp(){
		if($this->getStatus()->level >= self::MAX_LEVEL) return;
		Server::getInstance()->getPluginManager()->callEvent(new PlayerLevelupEvent(ToAruPG::getInstance(), $this));
		$this->status->level++;
		$this->status->sp += 3;
		$this->status->ap += 5;
		$this->status->setMaxHp($this->status->getMaxHp() + 20);
		$this->status->maxMp += 100;
	}

	public function getFinalValue($statusKey){
		return ($this->armorStatus->$statusKey + $this->status->$statusKey + $this->skillStatus->$statusKey);
	}

	public function getAdditionalValue($statusKey){
		return $this->armorStatus->$statusKey + $this->skillStatus->$statusKey;
	}

	public function setArmorStatus(Status $status){
		Server::getInstance()->getPluginManager()->callEvent(new ArmorChangeEvent(ToAruPG::getInstance(), $this, $this->armorStatus, $status));
		$this->armorStatus = $status;
	}

	public function getSkillStatus(){
		return $this->skillStatus;
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

	public function getArmorStatus(){
		return $this->armorStatus;
	}
}
