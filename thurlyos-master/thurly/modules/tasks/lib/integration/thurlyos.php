<?
/**
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 * 
 * @access private
 */

namespace Thurly\Tasks\Integration;

use Thurly\ThurlyOS\Feature;
use \Thurly\Tasks\Util;

abstract class ThurlyOS extends \Thurly\Tasks\Integration
{
	const MODULE_NAME = 'thurlyos';

	public static function getSettingsURL()
	{
		if(!static::includeModule())
		{
			return '';
		}

		return \CThurlyOS::PATH_CONFIGS;
	}

	public static function checkToolAvailable($toolName)
	{
		if($GLOBALS['__TASKS_DEVEL_ENV__'])
		{
			return true;
		}

		if(!static::includeModule()) // box installation, say yes
		{
			return true;
		}

		return \CThurlyOSBusinessTools::isToolAvailable(Util\User::getId(), $toolName);
	}

	public static function checkFeatureEnabled($featureName)
	{
		if($GLOBALS['__TASKS_DEVEL_ENV__'])
		{
			return true;
		}

		if(!static::includeModule()) // box installation, say yes
		{
			return true;
		}

		if(Feature::isFeatureEnabled($featureName)) // already payed, or trial is on = yes
		{
			return true;
		}

		return false;
	}

	public static function isLicensePaid()
	{
		if(!static::includeModule()) // box installation is like a free license in terms of thurlyos
		{
			return true;
		}

		return \CThurlyOS::isLicensePaid();
	}

	public static function isLicenseShareware()
	{
		if(!static::includeModule()) // box installation is not a shareware, its like a "freeware" in terms of thurlyos
		{
			return false;
		}

		$type = \CThurlyOS::getLicenseType();

		// todo: could be more custom licenses
		return $type == 'nfr' || $type == 'bis_inc' || $type == 'edu' || $type == 'startup';
	}
}