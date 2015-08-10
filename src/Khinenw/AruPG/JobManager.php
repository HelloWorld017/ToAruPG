<?php

namespace Khinenw\AruPG;

class JobManager{
	private static $jobs = [];

	/**
	 * @method boolean registerJob(Job $job) registers an Job to JobManager. This must be done when Plugin enabled.
	 * @param Job $job The job to register
	 * @return boolean If the job registered successfully, it returns true. Otherwise, it returns false.
	 */
	public static function registerJob(Job $job){
		if(!array_key_exists($job->getId(), self::$jobs)){
			self::$jobs[$job->getId()] = $job;
			return true;
		}
		return false;
	}

	/**
	 * @method boolean forceRegisterJob(Job $job) registers an Job to JobManager regardless of whether id of job is already registered.
	 * @param Job $job The job to register.
	 */
	public static function forceRegisterJob(Job $job){
		self::$jobs[$job->getId()] = $job;
	}


	/**
	 * @method Job|null getJob(int $jobId) gets job by its id.
	 * @param int $jobId The id of job which you want to get.
	 * @return Job|null If the job is registered, it returns the job. Otherwise, it returns null
	 */
	public static function getJob($jobId){
		return isset(self::$jobs[$jobId]) ? self::$jobs[$jobId] : null;
	}
}