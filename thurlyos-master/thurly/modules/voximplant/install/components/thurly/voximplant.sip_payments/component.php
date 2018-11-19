<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (!CModule::IncludeModule('voximplant'))
	return;

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_LINE, \Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
	return;

$ViHttp = new CVoxImplantHttp();
$result = $ViHttp->GetSipInfo();

$arResult = array(
	'FREE' => intval($result->FREE),
	'ACTIVE' => $result->ACTIVE,
	'DATE_END' => (strlen($result->DATE_END) > 0 ? new \Thurly\Main\Type\Date($result->DATE_END, 'd.m.Y') : ''),
);

if ($result->ACTIVE != CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_SIP))
{
	CVoxImplantConfig::SetModeStatus(CVoxImplantConfig::MODE_SIP, $result->ACTIVE? true: false);
}

$arResult['LINK_TO_BUY'] = '';
if (IsModuleInstalled('thurlyos'))
{
	if (LANGUAGE_ID != 'kz')
	{
		$arResult['LINK_TO_BUY'] = '/settings/license_phone_sip.php';
	}
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
	}
	else if (LANGUAGE_ID == 'de')
	{
		$arResult['LINK_TO_BUY'] = 'https://www.thurlyos.de/prices/self-hosted-telephony.php';
	}
	else
	{
		$arResult['LINK_TO_BUY'] = 'https://www.thurlyos.com/prices/self-hosted-telephony.php';
	}
}

$viAccount = new CVoxImplantAccount();
$arResult['ACCOUNT_NAME'] = $viAccount->GetAccountName();

$arResult['SIP_NOTICE_OLD_CONFIG_OFFICE_PBX'] = CVoxImplantConfig::GetNoticeOldConfigOfficePbx();

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;

?>