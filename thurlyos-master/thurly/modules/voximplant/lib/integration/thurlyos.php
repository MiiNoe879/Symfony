<?php

namespace Thurly\Voximplant\Integration;

use Thurly\Main\Loader;
use Thurly\Main\ModuleManager;

class ThurlyOS
{
	/**
	 * Returns array of user ids of portal admins
	 * @return array
	 */
	public static function getAdmins()
	{
		if(!Loader::includeModule('thurlyos'))
			return array();

		return \CThurlyOS::getAllAdminId();
	}

	/**
	 * Returns true if portal's email is confirmed (or there is no need to confirm it)
	 */
	public static function isEmailConfirmed()
	{
		if(!Loader::includeModule('thurlyos'))
			return true;

		return \CThurlyOS::isEmailConfirmed();
	}

	/**
	 * Returns true if ThurlyOS is installed
	 * @return bool
	 */
	public static function isInstalled()
	{
		return ModuleManager::isModuleInstalled('thurlyos');
	}
}