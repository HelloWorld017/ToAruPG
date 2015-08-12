<?php

namespace Khinenw\AruPG;

use pocketmine\Item\Item;
use pocketmine\event\player\PlayerInteractEvent;

interface Skill{

	/**
	 * @method void __init() Called when skill init (Plugin loaded)
	 */
	public static function __init();

	/**
	 * @method int getId() Returns ID of skill
	 * @return int Id of skill
	 */
	public static function getId();

	public function __construct(RPGPlayer $player = null);

	public function setPlayer(RPGPlayer $player);

	/**
	 * @return RPGPlayer
	 */
	public function getPlayer();

	/**
	 * @method void onPassiveInit() Called when passive skill init (Player Joined, Player Acquires Skill, etc...)
	 */
	public function onPassiveInit();

	/**
	 * @method boolean onActiveUse(PlayerInteractEvent $event) Called when player interacts while holding skill item
	 * @param PlayerInteractEvent $event An event which used by player
	 * @return boolean whether skill used successfully (returning true decreases mana)
	 */
	public function onActiveUse(PlayerInteractEvent $event);

	/**
	 * @method int getRequiredMana() Required Mana for skill activation
	 * @return int Returns required mana for skill activation
	 */
	public function getRequiredMana();

	/**
	 * @method int getRequiredLevel() Required XP Level for skill acquirement
	 * @return int Returns required XP Level for skill acquirement
	 */
	public static function getRequiredLevel();

	/**
	 * @method boolean canBeAcquired(RPGPlayer $player) Returns can this skill is acquired by player
	 * @param RPGPlayer $player An player who intended to acquire this skill
	 * @return boolean Returns can this skill is acquired by player
	 */
	public static function canBeAcquired(RPGPlayer $player);

	/**
	 * @method string getName() Returns name of the skill
	 * @return string Returns name of the skill which is key of translation.
	 */
	public static function getName();

	/**
	 * @method Item|null getItem() Returns item of the skill which makes player activate the skill. Return null if the skill doesn't need item.
	 * @return Item|null Returns Item of the skill.
	 */
	public static function getItem();

	/**
	 * @method int getLevel() Returns level of the skill.
	 * @return int Returns level of the skill.
	 */
	public function getLevel();

	/**
	 * @method void setLevel() Sets level of the skill
	 * @param int $level level which will be set
	 */
	public function setLevel($level);

	/**
	 * @method boolean canInvestSP(int $sp) Returns whether user can invest SP to this skill.
	 * @param int $sp Amount of SP which will be invested.
	 * @return boolean Returns whether user can invest SP to this skill.
	 */
	public function canInvestSP($sp);

	/**
	 * @method void investSP(int $sp) Invests SP to this skill.
	 * @param int $sp Amount of SP which will be invested.
	 */
	public function investSP($sp);

	/**
	 * @method string getSkillDescription() Returns description of the skill
	 * @return string Returns description of the skill.
	 */
	public function getSkillDescription();
}
