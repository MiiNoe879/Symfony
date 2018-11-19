<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

/** @global CMain $APPLICATION */

if (!CModule::includeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

if(!CCrmPerms::IsAccessEnabled())
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

$arResult['THURLY24'] = \Thurly\Main\ModuleManager::isModuleInstalled('thurlyos');
$arResult['IS_BIZPRPOC_ENABLED'] = CModule::IncludeModule('bizproc') && CBPRuntime::isFeatureEnabled();
$arResult['IS_AUTOMATION_LEAD_ENABLED'] = (
	\Thurly\Crm\Settings\LeadSettings::isEnabled()
	&&
	\Thurly\Crm\Automation\Factory::isAutomationAvailable(\CCrmOwnerType::Lead)
);
$arResult['IS_AUTOMATION_DEAL_ENABLED'] = \Thurly\Crm\Automation\Factory::isAutomationAvailable(\CCrmOwnerType::Deal);

$arResult['SMS_SENDERS'] = array();
$smsSenders = \Thurly\Crm\Integration\SmsManager::getSenderInfoList();
foreach ($smsSenders as $sender)
{
	if ($sender['isConfigurable'])
	{
		$arResult['SMS_SENDERS'][] = $sender;
	}
}

$arResult['PERM_CONFIG'] = false;
$arResult['IS_ACCESS_ENABLED'] = false;
/** @var \CCrmPerms $crmPerms */
$crmPerms = CCrmPerms::getCurrentUserPermissions();
if(!$crmPerms->HavePerm('CONFIG', BX_CRM_PERM_NONE))
	$arResult['PERM_CONFIG'] = true;
if($crmPerms->IsAccessEnabled())
	$arResult['IS_ACCESS_ENABLED'] = true;

$arResult['RAND_STRING'] = $this->randString();

$title = GetMessage(GetMessage('CRM_TITLE1'));
if (!is_string($title) || empty($title))
	$title = GetMessage('CRM_TITLE');
$APPLICATION->SetTitle($title);
$this->includeComponentTemplate();