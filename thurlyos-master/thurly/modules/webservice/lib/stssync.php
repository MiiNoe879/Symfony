<?php
namespace Thurly\WebService;

use Thurly\Intranet\OutlookApplication;
use Thurly\Main\Authentication\ApplicationManager;
use Thurly\Main\Authentication\ApplicationPasswordTable;
use Thurly\Main\Context;
use Thurly\Main\Loader;
use Thurly\Main\Text\HtmlFilter;
use Thurly\Main\Type\DateTime;

class StsSync
{
	const SERVICE_PATH = '/stssync/';

	public static function getUrl($type, $servicePath, $linkUrl, $prefix, $name, $guid)
	{
		\CJSCore::Init(array('stssync'));

		$port = Context::getCurrent()->getRequest()->isHttps() ? 443 : 80;

		if(Loader::includeModule('ldap'))
		{
			$port = \CLdapUtil::getTargetPort();
		}

		return 'BX.StsSync.sync(\''.$type.'\', \''.static::SERVICE_PATH.$servicePath.'\', \''.\CUtil::jsEscape($linkUrl).'\', \''.\CUtil::jsEscape($prefix).'\', \''.\CUtil::jsEscape($name).'\', \''.$guid.'\', '.intval($port).')';
	}

	public static function checkAuth($userId, $ap)
	{
		global $USER;

		if(Loader::includeModule('intranet'))
		{
			$appPassword = ApplicationPasswordTable::findPassword($userId, $ap);
			if($appPassword !== false)
			{
				if($appPassword["APPLICATION_ID"] === OutlookApplication::ID)
				{
					$appManager = ApplicationManager::getInstance();
					if($appManager->checkScope($appPassword["APPLICATION_ID"]) === true)
					{
						ApplicationPasswordTable::update($appPassword["ID"], array(
							'DATE_LOGIN' => new DateTime(),
							'LAST_IP' => Context::getCurrent()->getRequest()->getRemoteAddress(),
						));

						setSessionExpired(true);
						return $USER->Authorize($userId);
					}
				}
			}
		}

		return false;
	}

	public static function getAuth($type = '')
	{
		if(Loader::includeModule('intranet'))
		{
			return \Thurly\Intranet\OutlookApplication::generateAppPassword($type);
		}
		else
		{
			return false;
		}
	}
}