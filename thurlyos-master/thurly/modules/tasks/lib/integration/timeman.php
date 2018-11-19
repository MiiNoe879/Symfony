<?php
namespace Thurly\Tasks\Integration;

abstract class Timeman extends \Thurly\Tasks\Integration
{
	const MODULE_NAME = 'timeman';

	public static function canUse()
	{
		return self::isInstalled()
			   && (!\CModule::IncludeModule('extranet') || !\CExtranet::IsExtranetSite());
	}
}