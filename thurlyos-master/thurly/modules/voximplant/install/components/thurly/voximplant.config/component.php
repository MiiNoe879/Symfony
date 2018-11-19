<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (!CModule::IncludeModule('voximplant'))
	return;

$permissions = \Thurly\Voximplant\Security\Permissions::createWithCurrentUser();
if(!$permissions->canPerform(\Thurly\Voximplant\Security\Permissions::ENTITY_LINE, \Thurly\Voximplant\Security\Permissions::ACTION_MODIFY))
{
	ShowError(GetMessage('COMP_VI_ACCESS_DENIED'));
	return;
}

$mode = '';
if (in_array($_REQUEST['MODE'], Array(CVoxImplantConfig::MODE_LINK, CVoxImplantConfig::MODE_RENT, CVoxImplantConfig::MODE_SIP)))
{
	if (isset($_POST['MODE']))
	{
		$mode = $_POST['MODE'];
	}
	else if (isset($_GET['MODE']))
	{
		$mode = $_GET['MODE'];
	}
}

$ViAccount = new CVoxImplantAccount();

$arResult = Array(
	'MODE_LINK' => CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_LINK),
	'MODE_RENT' => CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_RENT),
	'MODE_SIP' => CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_SIP),
	'MODE_ACTIVE' => $mode,
	'LANG' => isset($arParams['LANG'])? $arParams['LANG']: $ViAccount->GetAccountLang(),
);

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;

?>