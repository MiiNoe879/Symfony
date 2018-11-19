<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if(
	!\Thurly\Main\Loader::includeModule("rest")
	|| !\CRestUtil::isAdmin()
)
{
	return;
}

$arParams['ID'] = intval($arParams['ID']);

$arResult['ALLOW_ZIP'] = \Thurly\Main\ModuleManager::isModuleInstalled("thurlyos");

if ($arParams['ID'] > 0)
{
	$APPLICATION->SetTitle(GetMessage('MARKETPLACE_LOCAL_EDIT_TITLE'));

	$arResult["APP"] = \Thurly\Rest\AppTable::getByClientId($arParams['ID']);
	if (is_array($arResult["APP"]) && $arResult['APP']['STATUS'] === \Thurly\Rest\AppTable::STATUS_LOCAL)
	{
		if(!empty($arResult["APP"]["SCOPE"]))
		{
			$arResult["APP"]["SCOPE"] = explode(",", $arResult["APP"]["SCOPE"]);
		}

		$langNames = \Thurly\Rest\AppLangTable::getList(array(
			'filter' => array(
				'=APP_ID' => $arResult["APP"]["ID"]
			)
		));

		$arResult['APP']['MENU_NAME'] = array();

		while($langName = $langNames->fetch())
		{
			$arResult['APP']['MENU_NAME'][$langName["LANGUAGE_ID"]] = $langName["MENU_NAME"];
		}
	}
	else
	{
		ShowError(GetMessage('MARKETPLACE_LOCAL_NOT_FOUND'));
		return;
	}
}
else
{
	$APPLICATION->SetTitle(GetMessage('MARKETPLACE_LOCAL_ADD_TITLE'));
	$arResult['APP'] = array(
		'SCOPE' => array(),
	);
}

$dbRes = \Thurly\Main\Localization\LanguageTable::getList(array(
	'order' => array('DEF' => 'DESC', 'NAME' => 'ASC'),
	'filter' => array('=ACTIVE' => 'Y'),
	'select' => array('LID', 'NAME')
));

$arResult['LANG'] = array();
while($lang = $dbRes->fetch())
{
	$arResult['LANG'][$lang['LID']] = $lang['NAME'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_thurly_sessid())
{
	$arFields = array(
		"URL" => $_POST['APP_URL'],
		"URL_INSTALL" => $_POST['APP_URL_INSTALL'],
		"SCOPE" => is_array($_POST["SCOPE"]) && !empty($_POST["SCOPE"]) ? $_POST["SCOPE"] : array(),
		"APP_NAME" => trim($_POST["APP_NAME"]),
		"ONLY_API" => isset($_POST["APP_ONLY_API"]) ? "Y" : "N",
		"MOBILE" => isset($_POST["MOBILE"]) ? "Y" : "N",
	);

	if(strlen($arFields['APP_NAME']) <= 0)
	{
		$arResult["ERROR"] = \Thurly\Main\Localization\Loc::getMessage("MP_ERROR_EMPTY_NAME");
	}
	elseif(count($arFields['SCOPE']) <= 0)
	{
		$arResult["ERROR"] = \Thurly\Main\Localization\Loc::getMessage("MP_ERROR_EMPTY_SCOPE");
	}

	if(empty($arResult['ERROR']))
	{
		foreach(GetModuleEvents('rest', 'OnRestLocalAppSave', true) as $eventHandler)
		{
			$eventResult = ExecuteModuleEventEx($eventHandler, array($arResult['APP'], &$arFields));
			if($eventResult !== null)
			{
				$arResult["ERROR"] = $eventResult;
			}
		}
	}

	if(!\Thurly\Rest\OAuthService::getEngine()->isRegistered())
	{
		try
		{
			\Thurly\Rest\OAuthService::register();
		}
		catch(\Thurly\Main\SystemException $e)
		{
			$arResult['ERROR'] = $e->getCode().': '.$e->getMessage();
		}
	}

	if(empty($arResult['ERROR']))
	{
		if(strlen($arFields['URL']) <= 0)
		{
			$arResult["ERROR"] = \Thurly\Main\Localization\Loc::getMessage("MP_ERROR_INCORRECT_URL");
		}
	}

	if(empty($arResult["ERROR"]))
	{
		try
		{
			$appFields = array(
				'URL' => $arFields['URL'],
				'URL_INSTALL' => $arFields['URL_INSTALL'],
				'SCOPE' => implode(',', $arFields['SCOPE']),
				'STATUS' => \Thurly\Rest\AppTable::STATUS_LOCAL,
				'APP_NAME' => $arFields['APP_NAME'],
				'MOBILE' => $arFields['MOBILE'],
			);

			if($arResult["APP"]['ID'] > 0)
			{
				$result = \Thurly\Rest\AppTable::update($arResult['APP']['ID'], $appFields);
			}
			else
			{
				$appFields['INSTALLED'] = (!empty($arFields['URL_INSTALL']) && $arFields['ONLY_API'] !== 'Y')
					? \Thurly\Rest\AppTable::NOT_INSTALLED
					: \Thurly\Rest\AppTable::INSTALLED;

				$result = \Thurly\Rest\AppTable::add($appFields);
			}

			if($result->isSuccess())
			{
				$appId = $result->getId();

				\Thurly\Rest\AppLangTable::deleteByApp($appId);

				if($arFields['ONLY_API'] === 'N')
				{
					foreach($_POST['APP_MENU_NAME'] as $lang => $name)
					{
						\Thurly\Rest\AppLangTable::add(array(
							'APP_ID' => $appId,
							'LANGUAGE_ID' => $lang,
							'MENU_NAME' => $name
						));
					}
				}
				else
				{
					if(
						$arFields["ONLY_API"] === "Y"
						&& !empty($arFields["URL_INSTALL"])
						&& empty($arResult['APP']['URL_INSTALL'])
					)
					{
						// checkCallback is already called inside checkFields
						$result = \Thurly\Rest\EventTable::add(array(
							"APP_ID" => $appId,
							"EVENT_NAME" => "ONAPPINSTALL",
							"EVENT_HANDLER" => $arFields["URL_INSTALL"],
						));
						if($result->isSuccess())
						{
							\Thurly\Rest\Event\Sender::bind('rest', 'OnRestAppInstall');
						}
					}

					if($arResult['APP']['ID'] <= 0)
					{
						\Thurly\Rest\AppTable::install($appId);
					}
				}

				$url = $arResult['APP']['ID'] > 0
					? $APPLICATION->GetCurPageParam("success=Y", array('success'))
					: (
						$arFields['ONLY_API'] === "Y"
							? str_replace("#id#", $appId, $arParams['EDIT_URL_TPL'])
							: $arParams['LIST_URL']
					);

				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					global $CACHE_MANAGER;
					$CACHE_MANAGER->ClearByTag('sonet_group');
				}

				LocalRedirect($url);
			}
			else
			{
				$arResult["ERROR"] = implode('<br />', $result->getErrorMessages());
			}
		}
		catch (\Thurly\Rest\OAuthException $e)
		{
			$arResult["ERROR"] = $e->getMessage();
		}
	}

	$arResult['APP']['APP_NAME'] = $_POST['APP_NAME'];
	$arResult['APP']['MENU_NAME'] = $_POST['APP_MENU_NAME'];
	$arResult['APP']['SCOPE'] = !empty($_POST['SCOPE']) ? $_POST['SCOPE'] : array();
	$arResult['APP']['URL'] = $_POST['APP_URL'];
	$arResult['APP']['URL_INSTALL'] = $_POST['APP_URL_INSTALL'];
}

$arResult["SCOPE"] = \CRestUtil::getScopeList();

$this->IncludeComponentTemplate();
