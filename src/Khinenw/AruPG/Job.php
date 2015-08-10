<?php

namespace Khinenw\AruPG;

interface Job{
	public function getId();

	/**
	 * @method Skill[] getSkills() Skill list which can be get
	 * @return Skill[] Skill list which can be get
	 */
	public static function getSkills();
}