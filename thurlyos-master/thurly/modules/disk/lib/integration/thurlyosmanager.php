<?php
namespace Thurly\Disk\Integration;

use Thurly\Main;
use Thurly\Main\Loader;
use Thurly\Main\ModuleManager;

class ThurlyOSManager
{
	/**
	 * Tells if module thurlyos is installed.
	 *
	 * @return bool
	 */
	public static function isEnabled()
	{
		return ModuleManager::isModuleInstalled('thurlyos');
	}

	/**
	 * Tells if user has access to entity by different restriction on B24.
	 *
	 * @param string $entityType Entity type.
	 * @param int $userId User id.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function isAccessEnabled($entityType, $userId)
	{
		if(!Loader::includeModule('thurlyos'))
		{
			return true;
		}

		return \CThurlyOSBusinessTools::isToolAvailable($userId, $entityType);
	}

	public static function checkAccessEnabled($entityType, $userId)
	{
		if(!Loader::includeModule('thurlyos'))
		{
			return true;
		}

		return \CThurlyOSBusinessTools::isToolAvailable($userId, $entityType, false);
	}

	/**
	 * Returns true if tariff for this portal is not free.
	 *
	 * @return bool
	 */
	public static function isLicensePaid()
	{
		if(Loader::includeModule('thurlyos'))
		{
			return \CThurlyOS::IsLicensePaid();
		}

		return false;
	}

	/**
	 * Init javascript license popup.
	 *
	 * @param string $featureGroupName
	 */
	public static function initLicenseInfoPopupJS($featureGroupName = "")
	{
		if(Loader::includeModule('thurlyos'))
		{
			\CThurlyOS::initLicenseInfoPopupJS($featureGroupName);
		}
	}
}