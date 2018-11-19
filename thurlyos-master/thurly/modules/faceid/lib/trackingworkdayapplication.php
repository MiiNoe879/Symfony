<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage faceid
 * @copyright  2001-2017 Thurly
 */

namespace Thurly\FaceId;

/**
 * @package    thurly
 * @subpackage faceid
 */
class TrackingWorkdayApplication extends \Thurly\Main\Authentication\Application
{
	protected $validUrls = array(
		"/thurly/components/thurly/faceid.timeman/ajax.php",
		"/thurly/tools/faceid/auth.php",
	);

	public static function checkPermission()
	{
		return \Thurly\Main\Loader::includeModule('timeman') && \CTimeMan::IsAdmin();
	}

	public static function OnApplicationsBuildList()
	{
		if (static::checkPermission())
		{
			IncludeModuleLangFile(__FILE__);

			// if admin or tm_manage_all
			return array(
				"ID" => "faceid_workday",
				"NAME" => GetMessage('TRACKING_WORKDAY_APPLICATION_NAME'),
				"DESCRIPTION" => GetMessage("TRACKING_WORKDAY_APPLICATION_DESC"),
				"SORT" => 4000,
				"CLASS" => "\\Thurly\\FaceId\\TrackingWorkdayApplication",
			);
		}
	}
}
