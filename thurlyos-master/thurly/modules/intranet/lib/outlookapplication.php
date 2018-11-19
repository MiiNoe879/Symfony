<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage intranet
 * @copyright 2001-2014 Thurly
 */

namespace Thurly\Intranet;

use Thurly\Main\Authentication\ApplicationPasswordTable;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class OutlookApplication extends \Thurly\Main\Authentication\Application
{
	const ID = "ws_outlook";

	protected $validUrls = array(
		"/thurly/tools/ws_calendar/",
		"/thurly/tools/ws_calendar_extranet/",
		"/thurly/tools/ws_contacts/",
		"/thurly/tools/ws_contacts_crm/",
		"/thurly/tools/ws_contacts_extranet/",
		"/thurly/tools/ws_contacts_extranet_emp/",
		"/thurly/tools/ws_tasks/",
		"/thurly/tools/ws_tasks_extranet/",
		"/thurly/services/stssync/",
		"/stssync/",
	);

	public static function OnApplicationsBuildList()
	{
		return array(
			"ID" => static::ID,
			"NAME" => Loc::getMessage("WS_OUTLOOK_APP_TITLE"),
			"DESCRIPTION" => Loc::getMessage("WS_OUTLOOK_APP_DESC"),
			"SORT" => 1000,
			"CLASS" => __CLASS__,
			"OPTIONS_CAPTION" => Loc::getMessage('WS_OUTLOOK_APP_OPTIONS_CAPTION'),
			"OPTIONS" => array(
				Loc::getMessage("WS_OUTLOOK_APP_TITLE_OPTION"),
			)
		);
	}

	/**
	 * Generates AP for REST access.
	 *
	 * @param string $siteTitle Site title for AP description.
	 *
	 * @return bool|string password or false
	 * @throws \Exception
	 */
	public static function generateAppPassword($type = '')
	{
		global $USER;

		$password = ApplicationPasswordTable::generatePassword();

		$message = Loc::getMessage('WS_OUTLOOK_APP_SYSCOMMENT');
		if($type)
		{
			$typeTitle = Loc::getMessage('WS_OUTLOOK_APP_TYPE_'.$type);
			if(strlen($typeTitle) > 0)
			{
				$message = Loc::getMessage('WS_OUTLOOK_APP_SYSCOMMENT_TYPE', array(
					'#TYPE#' => $typeTitle,
				));
			}
		}

		$res = ApplicationPasswordTable::add(array(
			'USER_ID' => $USER->getID(),
			'APPLICATION_ID' => static::ID,
			'PASSWORD' => $password,
			'DATE_CREATE' => new DateTime(),
			'COMMENT' => Loc::getMessage('WS_OUTLOOK_APP_COMMENT'),
			'SYSCOMMENT' => $message,
		));

		if($res->isSuccess())
		{
			return $password;
		}

		return false;
	}
}
