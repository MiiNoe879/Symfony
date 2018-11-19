<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!Thurly\Main\Loader::includeModule("thurlyos") || !Thurly\Main\Loader::includeModule("rest"))
{
	return;
}

// APPLICATIONS

$appArray = \CThurlyOS::getAppsForWizard();
if (!is_array($appArray) || empty($appArray))
{
	return;
}

if(!\Thurly\Rest\OAuthService::getEngine()->isRegistered())
{
	try
	{
		\Thurly\Rest\OAuthService::register();
	}
	catch(\Thurly\Main\SystemException $e)
	{
	}
}

// INSTALL

if(\Thurly\Rest\OAuthService::getEngine()->isRegistered())
{
	foreach($appArray as $app)
	{
		if(isset($app["MODULE_DEPENDENCY"]) && !empty($app["MODULE_DEPENDENCY"]))
		{
			foreach($app["MODULE_DEPENDENCY"] as $moduleId)
			{
				if(!IsModuleInstalled($moduleId))
				{
					continue 2;
				}
			}
		}

		if(isset($app["LANGUAGE_DEPENDENCY"]) && !empty($app["LANGUAGE_DEPENDENCY"]))
		{
			if(!in_array(CThurlyOS::getLicensePrefix(), $app["LANGUAGE_DEPENDENCY"]))
			{
				continue;
			}
		}

		$result = \Thurly\Rest\AppTable::add($app["INSTALL"]);
		if($result->isSuccess())
		{
			$ID = $result->getId();

			if(is_array($app['MENU_NAME']))
			{
				foreach($app['MENU_NAME'] as $lang => $menuName)
				{
					\Thurly\Rest\AppLangTable::add(array(
						'APP_ID' => $ID,
						'LANGUAGE_ID' => $lang,
						'MENU_NAME' => trim($menuName)
					));
				}
			}

			if(is_array($app['OPTIONS']['CLEAR_CACHE']) && !empty($app['OPTIONS']['CLEAR_CACHE']) && defined("BX_COMP_MANAGED_CACHE"))
			{
				global $CACHE_MANAGER;
				foreach($app['OPTIONS']['CLEAR_CACHE'] as $cacheTag)
				{
					$CACHE_MANAGER->ClearByTag($cacheTag);
				}
			}

			if(is_array($app['EXECUTE']) && !empty($app['EXECUTE']))
			{
				foreach($app['EXECUTE'] as $func)
				{
					call_user_func($func, Array("APP_ID" => $ID, "APP" => $app["INSTALL"]));
				}
			}
		}
	}
}

function wizardInstallBotGiphy($params)
{
	\Thurly\Main\Loader::includeModule('imbot');
	\Thurly\ImBot\Bot\Giphy::register(Array("APP_ID" => $params["APP"]["CLIENT_ID"]));
}
function wizardInstallBotProperties($params)
{
	\Thurly\Main\Loader::includeModule('imbot');
	\Thurly\ImBot\Bot\Properties::register(Array("APP_ID" => $params["APP"]["CLIENT_ID"]));
}
function wizardInstallBotPropertiesUa($params)
{
	\Thurly\Main\Loader::includeModule('imbot');
	\Thurly\ImBot\Bot\PropertiesUa::register(Array("APP_ID" => $params["APP"]["CLIENT_ID"]));
}

CAgent::AddAgent('\\Thurly\\ImOpenLines\\Security\\Helper::installRolesAgent();', "imopenlines", "N", 60, "", "Y", \ConvertTimeStamp(time()+\CTimeZone::GetOffset()+60, "FULL"));

CUserOptions::SetOption("thurlyos", "show_userinfo_spotlight", array("needShow" => "Y"), false, 1);
?>