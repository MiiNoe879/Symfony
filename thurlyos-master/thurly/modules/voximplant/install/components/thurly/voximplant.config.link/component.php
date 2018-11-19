<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (!CModule::IncludeModule('voximplant'))
	return;

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_LINE, \Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
	return;

$arResult['CONFIG'] = CVoxImplantConfig::GetConfigBySearchId(CVoxImplantConfig::LINK_BASE_NUMBER);
if($arResult['CONFIG']['PHONE_NAME'] == '')
	$arResult['CONFIG']['PHONE_NAME'] = CVoxImplantConfig::GetDefaultPhoneName($arResult['CONFIG']);
else
	$arResult['CONFIG']['PHONE_NAME'] = \Thurly\Main\PhoneNumber\Parser::getInstance()->parse($arResult['CONFIG']['PHONE_NAME'])->format();

$arResult['CALLER_ID'] = CVoxImplantPhone::GetCallerId();

$arResult["TRIAL_TEXT"] = '';
if (!CVoxImplantAccount::IsPro() || CVoxImplantAccount::IsDemo())
{
	$arResult["TRIAL_TEXT"] = CVoxImplantMain::GetTrialText();
}

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;
