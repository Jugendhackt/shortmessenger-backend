<?php

require_once(__DIR__."/../vendor/autoload.php");

# Time based blocking filter

function timeblock($cronRule){
	$cron = Cron\CronExpression::factory($cronRule);
	# Timestamp to the next minute
	$nextMinute = ceil(time()/60)*60;
	if(strtotime($cron->getNextRunDate()->format("Y-m-d H:i:s")) == $nextMinute){
		return false;
	}
	return true;
}
