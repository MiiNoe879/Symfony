<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage intranet
 * @copyright 2001-2014 Thurly
 */

namespace Thurly\Intranet;

class PublicApplication extends \Thurly\Main\Authentication\Application
{
	protected $validUrls = array(
		"/desktop_app/",
		"/online/",
		"/docs/pub/",
		"/pub/",
	);

	public static function OnApplicationsBuildList()
	{
		return array(
			"ID" => "public",
			"NAME" => "Public application",
			"DESCRIPTION" => "",
			"SORT" => 9000,
			"CLASS" => '\Thurly\Intranet\PublicApplication',
			"VISIBLE" => false,
		);
	}

	public static function onApplicationScopeError(\Thurly\Main\Event $event)
	{
		$applicationId = $event->getParameter('APPLICATION_ID');
		if ($applicationId == 'public')
		{
			global $USER;
			if ($USER->IsAuthorized())
			{
				$applicationUri = \Thurly\Main\Application::getInstance()->getContext()->getRequest()->getDecodedUri();
				if ($applicationUri == '/')
				{
					$USER->Logout();
					LocalRedirect('/');
				}
			}
		}
	}
}
