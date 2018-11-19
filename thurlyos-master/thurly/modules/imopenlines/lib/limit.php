<?php

namespace Thurly\Imopenlines;

use Thurly\Main\Config\Option;
use Thurly\Main\ModuleManager;

class Limit
{
	const OPTION_TRACKER_LIMIT = "tracker_limit";
	const OPTION_LAST_TRACKER_COUNTER_UPDATE = "tracker_month";
	const TRACKER_COUNTER = "tracker_count";

	public static function isDemoLicense()
	{
		if (!\CModule::IncludeModule('thurlyos'))
			return false;

		return \CThurlyOS::getLicenseType() == 'demo';
	}

	public static function getLinesLimit()
	{
		if (!\CModule::IncludeModule('thurlyos'))
			return 0;

		if (\CThurlyOS::IsNfrLicense())
			return 0;

		if (in_array(\CThurlyOS::getLicenseType(), Array('company', 'demo', 'edu', 'bis_inc', 'crm', 'team')))
			return 0;

		if (in_array(\CThurlyOS::getLicenseType(), Array('project')))
			return Option::get('imopenlines', 'demo_max_openlines', 1);

		if (in_array(\CThurlyOS::getLicenseType(), Array('tf')))
			return 2;

		return Option::get('imopenlines', 'team_max_openlines', 2);
	}

	public static function getLicenseUsersLimit()
	{
		if (!\CModule::IncludeModule('thurlyos'))
			return false;

		if (\CThurlyOSBusinessTools::isLicenseUnlimited())
			return false;

		return \CThurlyOSBusinessTools::getUnlimUsers();
	}

	public static function canUseQueueAll()
	{
		if (!\CModule::IncludeModule('thurlyos'))
			return true;

		if (\CThurlyOS::getLicenseType() != 'project')
			return true;

		return !\Thurly\Main\Config\Option::get('imopenlines', 'limit_queue_all');
	}

	public static function canUseVoteClient()
	{
		if (!\CModule::IncludeModule('thurlyos'))
			return true;

		return \CThurlyOS::getLicenseType() != 'project';
	}

	public static function canUseVoteHead()
	{
		if (!\CModule::IncludeModule('thurlyos'))
			return true;

		return !in_array(\CThurlyOS::getLicenseType(), Array('project', 'tf'));
	}

	public static function getTrackerLimit()
	{
		if(!ModuleManager::isModuleInstalled('thurlyos'))
			return false;

		if (\CThurlyOS::IsLicensePaid())
			return false;

		if(\CThurlyOS::IsDemoLicense())
			return false;

		if (\CThurlyOS::IsNfrLicense())
			return false;

		return (int)Option::get('imopenlines', self::OPTION_TRACKER_LIMIT);
	}

	public static function getTrackerLimitRemainder()
	{
		$limit = self::getTrackerLimit();
		if($limit === false)
			return true;

		$month = (int)date('Ym');
		$previousMonth = (int)Option::get('imopenlines', self::OPTION_LAST_TRACKER_COUNTER_UPDATE);

		if($previousMonth !== $month)
		{
			Option::set('imopenlines', self::OPTION_LAST_TRACKER_COUNTER_UPDATE, $month);
			$counter = 0;
			\CGlobalCounter::Set(self::TRACKER_COUNTER, $counter, \CGlobalCounter::ALL_SITES, '', false);
		}
		else
		{
			$counter = \CGlobalCounter::GetValue(self::TRACKER_COUNTER, \CGlobalCounter::ALL_SITES);
		}

		return $limit - $counter;
	}

	public static function increaseTracker()
	{
		$limit = self::getTrackerLimit();
		if($limit === false)
			return false;

		\CGlobalCounter::Increment(self::TRACKER_COUNTER, \CGlobalCounter::ALL_SITES);

		return true;
	}

	public static function canRemoveCopyright()
	{
		if(!\CModule::IncludeModule('thurlyos'))
			return true;

		if(\CThurlyOS::IsDemoLicense())
			return true;

		return \CThurlyOS::IsLicensePaid();
	}

	public static function onThurlyOSLicenseChange(\Thurly\Main\Event $event)
	{
		Config::checkLinesLimit();
		QueueManager::checkBusinessUsers();
	}
}