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
	 * @method Skill[] getSkills() Skill list which can be get
	 * @return Skill[] Skill list which can be get
	 */
	public static function getSkills();
}