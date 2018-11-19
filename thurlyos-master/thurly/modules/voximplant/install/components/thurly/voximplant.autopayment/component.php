<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var $arParams array
 * @var $arResult array
 * @var $this CThurlyComponent
 * @var $APPLICATION CMain
 * @var $USER CUser
 */

if (!CModule::IncludeModule('voximplant'))
	return;

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_SETTINGS, \Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
	return;

$autoPayAllowed = CVoxImplantConfig::isAutoPayAllowed();
if($autoPayAllowed === false)
	return false;

$arResult = array(
	'AUTOPAY_ALLOWED' => ($autoPayAllowed === 'Y')
);

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;
?>