<?php

namespace Khinenw\AruPG;


interface Job{

	/**
	 * @method string getName() Returns name of the job
	 * @return string Returns name of the job which is key of translation.
	 */
	public static function getName();

	/**
	 * @method int getId() Returns ID of the job
	 * @return int Id of job
	 */
	public static function getId();

	/**
	 * @method int[] getSkills() Skill id list which can be gotten
	 * @return int[] Skill id list which can be gotten
	 */
	public static function getSkills();

	/**
	 * @method int getBaseDamage(RPGPlayer $player) Base damage which will be shown in /ability
	 * @param RPGPlayer $player the player whose base damage will be returned
	 * @return int Base damage (Mostly, it is gotten by (main ability / 2) + 3
	 */
	public static function getBaseDamage(RPGPlayer $player);

	/**
	 * @method int getArmorBaseDamage(RPGPlayer $player) Armor base damage which will be shown in /ability
	 * @param RPGPlayer $player the player whose base damage will be returned
	 * @return int Base damage of armor (Mostly, it is gotten by (main ability / 2) + 3
	 */
	public static function getArmorBaseDamage(RPGPlayer $player);
}