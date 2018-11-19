<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2013 Thurly
 *
 * @deprecated
 */

use \Thurly\Tasks\Integration\Intranet\User;

class CTaskIntranetTools
{
	/**
	 * if ($arAllowedDepartments === null) => all departments headed by user will be used
	 */
	public static function getImmediateEmployees($userId, $arAllowedDepartments = null)
	{
		if ( ! CModule::IncludeModule('intranet') )
			return (false);

		return User::getSubordinate($userId, $arAllowedDepartments);
	}

	public static function getDepartmentsUsers($arDepartmentsIds, $arFields = array('ID'))
	{
		return User::getByDepartments($arDepartmentsIds, $arFields);
	}
}