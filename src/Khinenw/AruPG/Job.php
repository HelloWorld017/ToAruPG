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
	 * @method int getAdditionalBaseDamage(RPGPlayer $player) Armor base damage which will be shown in /ability
	 * @param RPGPlayer $player the player whose base damage will be returned
	 * @return int Base damage of additional status (Mostly, it is gotten by (main ability / 2) + 3
	 */
	public static function getAdditionalBaseDamage(RPGPlayer $player);

	/**
	 * @method int getApproximation(RPGPlayer $player) Approximation of damage which will be shown in /abbility
	 * @param RPGPlayer $player the player whose approximation damage will be returned
	 * @return int Approximation Damage
	 */
	public static function getApproximation(RPGPlayer $player);

	/**
	 * @method int getFinalDamage(RPGPlayer $player) Final Damage. In most case, it is get by randomizeDamage(getBaseDamage + getAdditionalBaseDamage, getApproximation)
	 * @param RPGPlayer $player the player whose final damage will be returned
	 * @return int Final Damage
	 */
	public static function getFinalDamage(RPGPlayer $player);
}
