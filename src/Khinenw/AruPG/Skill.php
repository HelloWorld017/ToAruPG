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

	public function __construct(RPGPlayer $player);

	/**
	 * @method void onPassiveInit() Called when passive skill init (Player Joined, Player Acquires Skill, etc...)
	 */
	public function onPassiveInit();

	/**
	 * @method void onActiveUse(PlayerInteractEvent $event) Called when player interacts while holding skill item
	 * @param PlayerInteractEvent $event An event which used by player
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
	 * @method int[] getRequiredJob() Required Job ID list for skill acquirement
	 * @return int[] Returns list of ID of Jobs which can acquire this skill
	 */
	public static function getRequiredJob();

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
	public static function getSkillDescription();
}
