<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (!CModule::IncludeModule('voximplant'))
	return;

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_LINE, \Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
	return;

$arResult = Array();

$arResult['SIP_ENABLE'] = CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_SIP);
$arResult['LIST_SIP_NUMBERS'] = Array();

if (IsModuleInstalled('thurlyos'))
{
	$account = new CVoxImplantAccount();
	$accountLang = $account->GetAccountLang();
	$arResult['LINK_TO_BUY'] = '/settings/license_phone_sip.php';
	$arResult['LINK_TO_DOC'] = (in_array(LANGUAGE_ID, Array("ru", "kz", "ua", "by"))? 'https://dev.1c-thurly.ru/learning/course/index.php?COURSE_ID=52&CHAPTER_ID=02564': 'https://www.thurlysoft.com/support/training/course/index.php?COURSE_ID=55&LESSON_ID=6635');
}
else
{
	if (LANGUAGE_ID == 'ru')
	{
		$arResult['LINK_TO_BUY'] = 'http://www.1c-thurly.ru/buy/intranet.php#tab-call-link';
	}
	else if (LANGUAGE_ID == 'ua')
	{
		$arResult['LINK_TO_BUY'] = 'http://www.1c-thurly.ua/buy/intranet.php#tab-call-link';
	}
	else if (LANGUAGE_ID == 'kz')
	{
		$arResult['LINK_TO_BUY'] = 'https://www.1c-thurly.kz/buy/intranet.php#tab-call-link';
	}
	else if (LANGUAGE_ID == 'de')
	{
		$arResult['LINK_TO_BUY'] = 'https://www.thurlyos.de/prices/self-hosted-telephony.php';
	}
	else
	{
		$arResult['LINK_TO_BUY'] = 'https://www.thurlyos.com/prices/self-hosted-telephony.php';
	}
	$arResult['LINK_TO_DOC'] = (in_array(LANGUAGE_ID, Array("ru", "kz", "ua", "by"))? 'https://dev.1c-thurly.ru/learning/course/index.php?COURSE_ID=48&CHAPTER_ID=02699': 'https://www.thurlysoft.com/support/training/course/index.php?COURSE_ID=26&LESSON_ID=6734');
}

if(in_array(LANGUAGE_ID, array("ru", "kz", "ua", "by")))
	$arResult['LINK_TO_REST_DOC'] = 'https://www.thurlyos.ru/apps/webhooks.php';
else
	$arResult['LINK_TO_REST_DOC'] = '';

$res = Thurly\Voximplant\ConfigTable::getList(Array(
	'filter' => Array('=PORTAL_MODE' => CVoxImplantConfig::MODE_SIP)
));
while ($row = $res->fetch())
{
	if (strlen($row['PHONE_NAME']) <= 0)
	{
		$row['PHONE_NAME'] = substr($row['SEARCH_ID'], 0, 3) == 'reg'? GetMessage('VI_CONFIG_SIP_CLOUD_TITLE'): GetMessage('VI_CONFIG_SIP_OFFICE_TITLE');
		$row['PHONE_NAME'] = str_replace('#ID#', $row['ID'], $row['PHONE_NAME']);
	}
	$arResult['LIST_SIP_NUMBERS'][$row['ID']] = Array(
		'PHONE_NAME' => htmlspecialcharsbx($row['PHONE_NAME']),
	);
}

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;

?>