<?php

namespace Khinenw\AruPG;

abstract class PassiveSkill implements Skill{
	public abstract function onSkillStatusReset();
}
