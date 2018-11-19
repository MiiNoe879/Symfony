<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

if (!CModule::IncludeModule('subscribe'))
{
	ShowError(GetMessage('SUBSCRIBE_MODULE_NOT_INSTALLED'));
	return;
}

// 'Fileman' module always installed
CModule::IncludeModule('fileman');

$CrmPerms = new CCrmPerms($USER->GetID());
if (!$CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE'))
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

use \Thurly\Crm\Settings;

$arParams['PATH_TO_SM_CONFIG'] = CrmCheckPath('PATH_TO_SM_CONFIG', $arParams['PATH_TO_SM_CONFIG'], $APPLICATION->GetCurPage());
$arResult['ENABLE_CONTROL_PANEL'] = isset($arParams['ENABLE_CONTROL_PANEL']) ? $arParams['ENABLE_CONTROL_PANEL'] : true;

CUtil::InitJSCore();
$bVarsFromForm = false;
$sMailFrom = COption::GetOptionString('crm', 'email_from');

if (empty($sMailFrom))
{
	$sMailFrom = COption::GetOptionString('crm', 'mail', '');
}

//Disable fake address generation for ThurlyOS
if (empty($sMailFrom) && !IsModuleInstalled('thurlyos'))
{
	$sHost = $_SERVER['HTTP_HOST'];
	if (strpos($sHost, ':') !== false)
		$sHost = substr($sHost, 0, strpos($sHost, ':'));

	$sMailFrom = 'crm@'.$sHost;
}

$dupControl = \Thurly\Crm\Integrity\DuplicateControl::getCurrent();
$arResult['FORM_ID'] = 'CRM_SM_CONFIG';
if($_SERVER['REQUEST_METHOD'] == 'POST' && check_thurly_sessid())
{
	$activeTabKey = "{$arResult['FORM_ID']}_active_tab";
	$activeTabID = isset($_POST[$activeTabKey]) ? $_POST[$activeTabKey] : '';

	$bVarsFromForm = true;
	if(isset($_POST['save']) || isset($_POST['apply']))
	{
		$sError = '';

		/*Account number template settings*/
		$APPLICATION->ResetException();
		include_once($GLOBALS["DOCUMENT_ROOT"]."/thurly/components/thurly/crm.config.invoice.number/post_proc.php");
		if ($ex = $APPLICATION->GetException())
			$sError = $ex->GetString();

		$APPLICATION->ResetException();
		include_once($GLOBALS["DOCUMENT_ROOT"]."/thurly/components/thurly/crm.config.number/post_proc.php");
		if ($ex = $APPLICATION->GetException())
			$sError = $ex->GetString();

		$APPLICATION->ResetException();

		if (strlen($sError) > 0)
			ShowError($sError.'<br>');
		else
		{
			if(isset($_POST['CALENDAR_DISPLAY_COMPLETED_CALLS']))
			{
				Settings\ActivitySettings::setValue(
					Settings\ActivitySettings::KEEP_COMPLETED_CALLS,
					strtoupper($_POST['CALENDAR_DISPLAY_COMPLETED_CALLS']) === 'Y'
				);
			}

			if(isset($_POST['CALENDAR_DISPLAY_COMPLETED_MEETINGS']))
			{
				Settings\ActivitySettings::setValue(
					Settings\ActivitySettings::KEEP_COMPLETED_MEETINGS,
					strtoupper($_POST['CALENDAR_DISPLAY_COMPLETED_MEETINGS']) === 'Y'
				);
			}
			
			if(isset($_POST['CALENDAR_KEEP_REASSIGNED_CALLS']))
			{
				Settings\ActivitySettings::setValue(
					Settings\ActivitySettings::KEEP_REASSIGNED_CALLS,
					strtoupper($_POST['CALENDAR_KEEP_REASSIGNED_CALLS']) === 'Y'
				);
			}

			if(isset($_POST['CALENDAR_KEEP_REASSIGNED_MEETINGS']))
			{
				Settings\ActivitySettings::setValue(
					Settings\ActivitySettings::KEEP_REASSIGNED_MEETINGS,
					strtoupper($_POST['CALENDAR_KEEP_REASSIGNED_MEETINGS']) === 'Y'
				);
			}

			if(isset($_POST['KEEP_UNBOUND_TASKS']))
			{
				Settings\ActivitySettings::setValue(
					Settings\ActivitySettings::KEEP_UNBOUND_TASKS,
					strtoupper($_POST['KEEP_UNBOUND_TASKS']) === 'Y'
				);
			}

			if(isset($_POST['MARK_FORWARDED_EMAIL_AS_OUTGOING']))
			{
				Settings\ActivitySettings::setValue(
					Settings\ActivitySettings::MARK_FORWARDED_EMAIL_AS_OUTGOING,
					strtoupper($_POST['MARK_FORWARDED_EMAIL_AS_OUTGOING']) === 'Y'
				);
			}

			CCrmUserCounterSettings::SetValue(
				CCrmUserCounterSettings::ReckonActivitylessItems,
				isset($_POST['RECKON_ACTIVITYLESS_ITEMS_IN_COUNTERS']) && strtoupper($_POST['RECKON_ACTIVITYLESS_ITEMS_IN_COUNTERS']) !== 'N'
			);

			CCrmEMailCodeAllocation::SetCurrent(
				isset($_POST['SERVICE_CODE_ALLOCATION'])
					? intval($_POST['SERVICE_CODE_ALLOCATION'])
					: CCrmEMailCodeAllocation::Body
			);

			Settings\ActivitySettings::getCurrent()->setOutgoingEmailOwnerTypeId(
				isset($_POST['OUTGOING_EMAIL_OWNER_TYPE'])
					? intval($_POST['OUTGOING_EMAIL_OWNER_TYPE'])
					: \CCrmOwnerType::Contact
			);

			if(Thurly\Crm\Integration\ThurlyOSEmail::isEnabled()
				&& Thurly\Crm\Integration\ThurlyOSEmail::allowDisableSignature())
			{
				Thurly\Crm\Integration\ThurlyOSEmail::enableSignature(
					isset($_POST['ENABLE_B24_EMAIL_SIGNATURE']) && strtoupper($_POST['ENABLE_B24_EMAIL_SIGNATURE']) !== 'N'
				);
			}

			$isCallSettingsChanged = false;

			$oldCalltoFormat = CCrmCallToUrl::GetFormat(0);
			$newCalltoFormat = isset($_POST['CALLTO_FORMAT']) ? intval($_POST['CALLTO_FORMAT']) : CCrmCallToUrl::Slashless;
			if ($oldCalltoFormat != $newCalltoFormat)
			{
				CCrmCallToUrl::SetFormat($newCalltoFormat);
				$isCallSettingsChanged = true;
			}

			$oldCalltoSettings = $newCalltoSettings = CCrmCallToUrl::GetCustomSettings();
			if($newCalltoFormat === CCrmCallToUrl::Custom)
			{
				$newCalltoSettings['URL_TEMPLATE'] = isset($_POST['CALLTO_URL_TEMPLATE']) ? $_POST['CALLTO_URL_TEMPLATE'] : '';
				$newCalltoSettings['CLICK_HANDLER'] = isset($_POST['CALLTO_CLICK_HANDLER']) ? $_POST['CALLTO_CLICK_HANDLER'] : '';
			}
			$newCalltoSettings['NORMALIZE_NUMBER'] = isset($_POST['CALLTO_NORMALIZE_NUMBER']) && strtoupper($_POST['CALLTO_NORMALIZE_NUMBER']) === 'N' ? 'N' : 'Y';

			if (
				$oldCalltoSettings['URL_TEMPLATE'] != $newCalltoSettings['URL_TEMPLATE']
				|| $oldCalltoSettings['CLICK_HANDLER'] != $newCalltoSettings['CLICK_HANDLER']
				|| $oldCalltoSettings['NORMALIZE_NUMBER'] != $newCalltoSettings['NORMALIZE_NUMBER']
			)
			{
				CCrmCallToUrl::SetCustomSettings($newCalltoSettings);
				$isCallSettingsChanged = true;
			}

			if (defined('BX_COMP_MANAGED_CACHE') && $isCallSettingsChanged)
			{
				$GLOBALS['CACHE_MANAGER']->ClearByTag('CRM_CALLTO_SETTINGS');
			}

			if(isset($_POST['ENABLE_SIMPLE_TIME_FORMAT']))
			{
				\Thurly\Crm\Settings\LayoutSettings::getCurrent()->enableSimpleTimeFormat(
					strtoupper($_POST['ENABLE_SIMPLE_TIME_FORMAT']) === 'Y'
				);
			}

			$entityAddressFormatID = isset($_POST['ENTITY_ADDRESS_FORMAT_ID'])
				? (int)$_POST['ENTITY_ADDRESS_FORMAT_ID'] : \Thurly\Crm\Format\EntityAddressFormatter::Dflt;
			\Thurly\Crm\Format\EntityAddressFormatter::setFormatID($entityAddressFormatID);

			$personFormatID = isset($_POST['PERSON_NAME_FORMAT_ID'])
				? (int)$_POST['PERSON_NAME_FORMAT_ID'] : \Thurly\Crm\Format\PersonNameFormatter::Dflt;
			\Thurly\Crm\Format\PersonNameFormatter::setFormatID($personFormatID);

			$dupControl->enabledFor(
				CCrmOwnerType::Lead,
				isset($_POST['ENABLE_LEAD_DUP_CONTROL']) && strtoupper($_POST['ENABLE_LEAD_DUP_CONTROL']) === 'Y'
			);
			$dupControl->enabledFor(
				CCrmOwnerType::Contact,
				isset($_POST['ENABLE_CONTACT_DUP_CONTROL']) && strtoupper($_POST['ENABLE_CONTACT_DUP_CONTROL']) === 'Y'
			);
			$dupControl->enabledFor(
				CCrmOwnerType::Company,
				isset($_POST['ENABLE_COMPANY_DUP_CONTROL']) && strtoupper($_POST['ENABLE_COMPANY_DUP_CONTROL']) === 'Y'
			);
			$dupControl->save();

			CCrmStatus::EnableDepricatedTypes(
				isset($_POST['ENABLE_DEPRECATED_STATUSES']) && strtoupper($_POST['ENABLE_DEPRECATED_STATUSES']) === 'Y'
			);

			if(isset($_POST['LEAD_OPENED']))
			{
				\Thurly\Crm\Settings\LeadSettings::getCurrent()->setOpenedFlag(
					strtoupper($_POST['LEAD_OPENED']) === 'Y'
				);
			}

			if(isset($_POST['EXPORT_LEAD_PRODUCT_ROWS']))
			{
				\Thurly\Crm\Settings\LeadSettings::getCurrent()->enableProductRowExport(
					strtoupper($_POST['EXPORT_LEAD_PRODUCT_ROWS']) === 'Y'
				);
			}

			if(isset($_POST['CONTACT_OPENED']))
			{
				\Thurly\Crm\Settings\ContactSettings::getCurrent()->setOpenedFlag(
					strtoupper($_POST['CONTACT_OPENED']) === 'Y'
				);
			}

			if($_POST['LEAD_DEFAULT_LIST_VIEW'])
			{
				\Thurly\Crm\Settings\LeadSettings::getCurrent()->setDefaultListViewID($_POST['LEAD_DEFAULT_LIST_VIEW']);
			}

			if(isset($_POST['COMPANY_OPENED']))
			{
				\Thurly\Crm\Settings\CompanySettings::getCurrent()->setOpenedFlag(
					strtoupper($_POST['COMPANY_OPENED']) === 'Y'
				);
			}

			if(isset($_POST['DEAL_OPENED']))
			{
				\Thurly\Crm\Settings\DealSettings::getCurrent()->setOpenedFlag(
					strtoupper($_POST['DEAL_OPENED']) === 'Y'
				);
			}

			if(isset($_POST['REFRESH_DEAL_CLOSEDATE']))
			{
				\Thurly\Crm\Settings\DealSettings::getCurrent()->enableCloseDateSync(
					strtoupper($_POST['REFRESH_DEAL_CLOSEDATE']) === 'Y'
				);
			}

			if(isset($_POST['EXPORT_DEAL_PRODUCT_ROWS']))
			{
				\Thurly\Crm\Settings\DealSettings::getCurrent()->enableProductRowExport(
					strtoupper($_POST['EXPORT_DEAL_PRODUCT_ROWS']) === 'Y'
				);
			}

			if($_POST['DEAL_DEFAULT_LIST_VIEW'])
			{
				\Thurly\Crm\Settings\DealSettings::getCurrent()->setDefaultListViewID($_POST['DEAL_DEFAULT_LIST_VIEW']);
			}

			if($_POST['INVOICE_DEFAULT_LIST_VIEW'])
			{
				\Thurly\Crm\Settings\InvoiceSettings::getCurrent()->setDefaultListViewID($_POST['INVOICE_DEFAULT_LIST_VIEW']);
			}

			if($_POST['ENABLE_ENABLED_PUBLIC_B24_SIGN'])
			{
				\Thurly\Crm\Settings\InvoiceSettings::getCurrent()->setEnableSignFlag(
					strtoupper($_POST['ENABLE_ENABLED_PUBLIC_B24_SIGN']) === 'Y'
				);
			}

			if($_POST['COMPANY_DEFAULT_LIST_VIEW'])
			{
				\Thurly\Crm\Settings\CompanySettings::getCurrent()->setDefaultListViewID($_POST['COMPANY_DEFAULT_LIST_VIEW']);
			}

			if($_POST['CONTACT_DEFAULT_LIST_VIEW'])
			{
				\Thurly\Crm\Settings\ContactSettings::getCurrent()->setDefaultListViewID($_POST['CONTACT_DEFAULT_LIST_VIEW']);
			}

			if($_POST['ACTIVITY_DEFAULT_LIST_VIEW'])
			{
				\Thurly\Crm\Settings\ActivitySettings::getCurrent()->setDefaultListViewID($_POST['ACTIVITY_DEFAULT_LIST_VIEW']);
			}

			if(isset($_POST['QUOTE_OPENED']))
			{
				\Thurly\Crm\Settings\QuoteSettings::getCurrent()->setOpenedFlag(
					strtoupper($_POST['QUOTE_OPENED']) === 'Y'
				);
			}

			if(isset($_POST['CONVERSION_ENABLE_AUTOCREATION']))
			{
				\Thurly\Crm\Settings\ConversionSettings::getCurrent()->enableAutocreation(
					strtoupper($_POST['CONVERSION_ENABLE_AUTOCREATION']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_EXPORT_EVENT']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->enableExportEvent(
					strtoupper($_POST['ENABLE_EXPORT_EVENT']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_VIEW_EVENT']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->enableViewEvent(
					strtoupper($_POST['ENABLE_VIEW_EVENT']) === 'Y'
				);
			}

			if(isset($_POST['VIEW_EVENT_GROUPING_INTERVAL']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->setViewEventGroupingInterval(
					(int)$_POST['VIEW_EVENT_GROUPING_INTERVAL']
				);
			}

			if(isset($_POST['ENABLE_LEAD_DELETION_EVENT']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->enableLeadDeletionEvent(
					strtoupper($_POST['ENABLE_LEAD_DELETION_EVENT']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_DEAL_DELETION_EVENT']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->enableDealDeletionEvent(
					strtoupper($_POST['ENABLE_DEAL_DELETION_EVENT']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_QUOTE_DELETION_EVENT']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->enableQuoteDeletionEvent(
					strtoupper($_POST['ENABLE_QUOTE_DELETION_EVENT']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_DEAL_DELETION_EVENT']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->enableDealDeletionEvent(
					strtoupper($_POST['ENABLE_DEAL_DELETION_EVENT']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_CONTACT_DELETION_EVENT']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->enableContactDeletionEvent(
					strtoupper($_POST['ENABLE_CONTACT_DELETION_EVENT']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_COMPANY_DELETION_EVENT']))
			{
				\Thurly\Crm\Settings\HistorySettings::getCurrent()->enableCompanyDeletionEvent(
					strtoupper($_POST['ENABLE_COMPANY_DELETION_EVENT']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_LIVEFEED_MERGE']))
			{
				\Thurly\Crm\Settings\LiveFeedSettings::getCurrent()->enableLiveFeedMerge(
					strtoupper($_POST['ENABLE_LIVEFEED_MERGE']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_REST_REQ_USER_FIELD_CHECK']))
			{
				\Thurly\Crm\Settings\RestSettings::getCurrent()->enableRequiredUserFieldCheck(
					strtoupper($_POST['ENABLE_REST_REQ_USER_FIELD_CHECK']) === 'Y'
				);
			}

			if(isset($_POST['ENABLE_SLIDER']))
			{
				\Thurly\Crm\Settings\LayoutSettings::getCurrent()->enableSlider(
					strtoupper($_POST['ENABLE_SLIDER']) === 'Y'
				);
			}

			$activityCompetionConfig = \Thurly\Crm\Settings\LeadSettings::getCurrent()->getActivityCompletionConfig();
			foreach(\Thurly\Crm\Activity\Provider\ProviderManager::getCompletableProviderList() as $providerInfo)
			{
				$providerID = $providerInfo['ID'];
				$fieldName = "COMPLETE_ACTIVITY_ON_LEAD_CONVERT_{$providerID}";
				if(isset($_POST[$fieldName]))
				{
					$activityCompetionConfig[$providerID] = strtoupper($_POST[$fieldName]) === 'Y';
				}
			}
			\Thurly\Crm\Settings\LeadSettings::getCurrent()->setActivityCompletionConfig($activityCompetionConfig);

			LocalRedirect(
				CComponentEngine::MakePathFromTemplate(
					CCrmUrlUtil::AddUrlParams(
						$arParams['PATH_TO_SM_CONFIG'],
						array($activeTabKey => $activeTabID)
					),
					array()
				)
			);
		}
	}
}

$arResult['FIELDS'] = array();

if(\Thurly\Crm\Integration\ThurlyOSManager::isFeatureEnabled('17.5.0'))
{
	//Temporary concealment
	$arResult['FIELDS']['tab_main'][] = array(
		'id' => 'LAYOUT_CONFIG',
		'name' => GetMessage('CRM_SECTION_LAYOUT_CONFIG'),
		'type' => 'section'
	);
	$arResult['FIELDS']['tab_main'][] = array(
		'id' => 'ENABLE_SLIDER',
		'name' => GetMessage('CRM_FIELD_ENABLE_SLIDER'),
		'type' => 'checkbox',
		'value' => \Thurly\Crm\Settings\LayoutSettings::getCurrent()->isSliderEnabled(),
		'required' => false
	);
}

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'LEAD_CONFIG',
	'name' => GetMessage('CRM_SECTION_LEAD_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'LEAD_OPENED',
	'name' => GetMessage('CRM_FIELD_LEAD_OPENED'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\LeadSettings::getCurrent()->getOpenedFlag(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'LEAD_DEFAULT_LIST_VIEW',
	'name' => GetMessage('CRM_FIELD_LEAD_DEFAULT_LIST_VIEW'),
	'items' => \Thurly\Crm\Settings\LeadSettings::getViewDescriptions(),
	'type' => 'list',
	'value' => \Thurly\Crm\Settings\LeadSettings::getCurrent()->getDefaultListViewID(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'EXPORT_LEAD_PRODUCT_ROWS',
	'name' => GetMessage('CRM_FIELD_EXPORT_PRODUCT_ROWS'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\LeadSettings::getCurrent()->isProductRowExportEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'CONTACT_CONFIG',
	'name' => GetMessage('CRM_SECTION_CONTACT_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'CONTACT_OPENED',
	'name' => GetMessage('CRM_FIELD_CONTACT_OPENED'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\ContactSettings::getCurrent()->getOpenedFlag(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'CONTACT_DEFAULT_LIST_VIEW',
	'name' => GetMessage('CRM_FIELD_DEAL_DEFAULT_LIST_VIEW'),
	'items' => \Thurly\Crm\Settings\ContactSettings::getViewDescriptions(),
	'type' => 'list',
	'value' => \Thurly\Crm\Settings\ContactSettings::getCurrent()->getDefaultListViewID(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'COMPANY_CONFIG',
	'name' => GetMessage('CRM_SECTION_COMPANY_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'COMPANY_OPENED',
	'name' => GetMessage('CRM_FIELD_COMPANY_OPENED'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\CompanySettings::getCurrent()->getOpenedFlag(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'COMPANY_DEFAULT_LIST_VIEW',
	'name' => GetMessage('CRM_FIELD_DEAL_DEFAULT_LIST_VIEW'),
	'items' => \Thurly\Crm\Settings\CompanySettings::getViewDescriptions(),
	'type' => 'list',
	'value' => \Thurly\Crm\Settings\CompanySettings::getCurrent()->getDefaultListViewID(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'DEAL_CONFIG',
	'name' => GetMessage('CRM_SECTION_DEAL_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'DEAL_OPENED',
	'name' => GetMessage('CRM_FIELD_DEAL_OPENED'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\DealSettings::getCurrent()->getOpenedFlag(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'DEAL_DEFAULT_LIST_VIEW',
	'name' => GetMessage('CRM_FIELD_DEAL_DEFAULT_LIST_VIEW'),
	'items' => \Thurly\Crm\Settings\DealSettings::getViewDescriptions(),
	'type' => 'list',
	'value' => \Thurly\Crm\Settings\DealSettings::getCurrent()->getDefaultListViewID(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'REFRESH_DEAL_CLOSEDATE',
	'name' => GetMessage('CRM_FIELD_REFRESH_DEAL_CLOSEDATE'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\DealSettings::getCurrent()->isCloseDateSyncEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'EXPORT_DEAL_PRODUCT_ROWS',
	'name' => GetMessage('CRM_FIELD_EXPORT_PRODUCT_ROWS'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\DealSettings::getCurrent()->isProductRowExportEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'INVOICE_CONFIG',
	'name' => GetMessage('CRM_SECTION_INVOICE_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'INVOICE_DEFAULT_LIST_VIEW',
	'name' => GetMessage('CRM_FIELD_INVOICE_DEFAULT_LIST_VIEW'),
	'items' => \Thurly\Crm\Settings\InvoiceSettings::getViewDescriptions(),
	'type' => 'list',
	'value' => \Thurly\Crm\Settings\InvoiceSettings::getCurrent()->getDefaultListViewID(),
	'required' => false
);

if(\Thurly\Crm\Settings\InvoiceSettings::allowDisableSign())
{
	$arResult['FIELDS']['tab_main'][] = array(
		'id' => 'ENABLE_ENABLED_PUBLIC_B24_SIGN',
		'name' => GetMessage('CRM_FIELD_PUBLIC_INVOICE_B24_SIGN'),
		'type' => 'checkbox',
		'value' => \Thurly\Crm\Settings\InvoiceSettings::getCurrent()->getEnableSignFlag(),
		'required' => false
	);
}
else
{
	$arResult['FIELDS']['tab_main'][] = array(
		'id' => 'ENABLE_ENABLED_PUBLIC_B24_SIGN',
		'name' => GetMessage('CRM_FIELD_PUBLIC_INVOICE_B24_SIGN'),
		'type' => 'label',
		'value' =>  GetMessage('CRM_FIELD_PUBLIC_INVOICE_B24_SIGN_ENABLED'),
		'required' => false
	);
}

	
$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'QUOTE_CONFIG',
	'name' => GetMessage('CRM_SECTION_QUOTE_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'QUOTE_OPENED',
	'name' => GetMessage('CRM_FIELD_QUOTE_OPENED'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\QuoteSettings::getCurrent()->getOpenedFlag(),
	'required' => false
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'CONVERSION_CONFIG',
	'name' => GetMessage('CRM_SECTION_CONVERSION_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_main'][] = array(
	'id' => 'CONVERSION_ENABLE_AUTOCREATION',
	'name' => GetMessage('CRM_FIELD_CONVERSION_ENABLE_AUTOCREATION'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\ConversionSettings::getCurrent()->isAutocreationEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_rest'][] = array(
	'id' => 'ENABLE_REST_REQ_USER_FIELD_CHECK',
	'name' => GetMessage('CRM_FIELD_ENABLE_REST_REQ_USER_FIELD_CHECK'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\RestSettings::getCurrent()->isRequiredUserFieldCheckEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'ACTIVITY_GENERAL_CONFIG',
	'name' => GetMessage('CRM_SECTION_ACTIVITY_GENERAL_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'CALENDAR_DISPLAY_COMPLETED_CALLS',
	'name' => GetMessage('CRM_FIELD_DISPLAY_COMPLETED_CALLS_IN_CALENDAR'),
	'type' => 'checkbox',
	'value' => Settings\ActivitySettings::getValue(Settings\ActivitySettings::KEEP_COMPLETED_CALLS),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'CALENDAR_DISPLAY_COMPLETED_MEETINGS',
	'name' => GetMessage('CRM_FIELD_DISPLAY_COMPLETED_MEETINGS_IN_CALENDAR'),
	'type' => 'checkbox',
	'value' => Settings\ActivitySettings::getValue(Settings\ActivitySettings::KEEP_COMPLETED_MEETINGS),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'CALENDAR_KEEP_REASSIGNED_CALLS',
	'name' => GetMessage('CRM_FIELD_KEEP_REASSIGNED_CALLS'),
	'type' => 'checkbox',
	'value' => Settings\ActivitySettings::getValue(Settings\ActivitySettings::KEEP_REASSIGNED_CALLS),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'CALENDAR_KEEP_REASSIGNED_MEETINGS',
	'name' => GetMessage('CRM_FIELD_KEEP_REASSIGNED_MEETINGS'),
	'type' => 'checkbox',
	'value' => Settings\ActivitySettings::getValue(Settings\ActivitySettings::KEEP_REASSIGNED_MEETINGS),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'KEEP_UNBOUND_TASKS',
	'name' => GetMessage('CRM_FIELD_KEEP_UNBOUND_TASKS'),
	'type' => 'checkbox',
	'value' => Settings\ActivitySettings::getValue(Settings\ActivitySettings::KEEP_UNBOUND_TASKS),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'RECKON_ACTIVITYLESS_ITEMS_IN_COUNTERS',
	'name' => GetMessage('CRM_FIELD_RECKON_ACTIVITYLESS_ITEMS_IN_COUNTERS'),
	'type' => 'checkbox',
	'value' => CCrmUserCounterSettings::GetValue(CCrmUserCounterSettings::ReckonActivitylessItems, true),
	'required' => false
);


$activityCompetionConfig = \Thurly\Crm\Settings\LeadSettings::getCurrent()->getActivityCompletionConfig();
$html = '';
foreach(\Thurly\Crm\Activity\Provider\ProviderManager::getCompletableProviderList() as $providerInfo)
{
	$providerID = $providerInfo['ID'];
	$providerName = htmlspecialcharsbx($providerInfo['NAME']);
	$fieldName = "COMPLETE_ACTIVITY_ON_LEAD_CONVERT_{$providerID}";
	$enabled = !isset($activityCompetionConfig[$providerID]) || $activityCompetionConfig[$providerID];

	$html .= '<div>';
	$html .= '<input name="'.$fieldName.'" type="hidden" value="'.($enabled ? 'Y' : 'N').'"/>';
	$html .= "<input id='{$fieldName}' type='checkbox'";
	if($enabled)
	{
		$html .= " checked";
	}
	$html .= " onclick='document.getElementsByName(this.id)[0].value = this.checked ? \"Y\" : \"N\";'";
	$html .= "/>";
	$html .= "<label>{$providerName}</label>";
	$html .= '</div>';
}

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'COMPLETE_ACTIVITY_ON_LEAD_CONVERT',
	'name' => GetMessage('CRM_FIELD_COMPLETE_ACTIVITY_ON_LEAD_CONVERT'),
	'type' => 'custom',
	'value' => $html,
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'ACTIVITY_DEFAULT_LIST_VIEW',
	'name' => GetMessage('CRM_FIELD_DEAL_DEFAULT_LIST_VIEW'),
	'items' => \Thurly\Crm\Settings\ActivitySettings::getViewDescriptions(),
	'type' => 'list',
	'value' => \Thurly\Crm\Settings\ActivitySettings::getCurrent()->getDefaultListViewID(),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'ACTIVITY_INCOMING_EMAIL_CONFIG',
	'name' => GetMessage('CRM_SECTION_ACTIVITY_INCOMING_EMAIL_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'MARK_FORWARDED_EMAIL_AS_OUTGOING',
	'name' => GetMessage('CRM_FIELD_MARK_FORWARDED_EMAIL_AS_OUTGOING'),
	'type' => 'checkbox',
	'value' => Settings\ActivitySettings::getValue(Settings\ActivitySettings::MARK_FORWARDED_EMAIL_AS_OUTGOING),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'ACTIVITY_OUTGOING_EMAIL_CONFIG',
	'name' => GetMessage('CRM_SECTION_ACTIVITY_OUTGOING_EMAIL_CONFIG'),
	'type' => 'section'
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'SERVICE_CODE_ALLOCATION',
	'name' => GetMessage('CRM_FIELD_SERVICE_CODE_ALLOCATION'),
	'items' => CCrmEMailCodeAllocation::GetAllDescriptions(),
	'type' => 'list',
	'value' => CCrmEMailCodeAllocation::GetCurrent(),
	'required' => false
);

$arResult['FIELDS']['tab_activity_config'][] = array(
	'id' => 'OUTGOING_EMAIL_OWNER_TYPE',
	'name' => GetMessage('CRM_FIELD_OUTGOING_EMAIL_OWNER_TYPE'),
	'items' => array(
		\CCrmOwnerType::Lead => \CCrmOwnerType::getDescription(\CCrmOwnerType::Lead),
		\CCrmOwnerType::Contact => \CCrmOwnerType::getDescription(\CCrmOwnerType::Contact),
	),
	'type' => 'list',
	'value' => Settings\ActivitySettings::getCurrent()->getOutgoingEmailOwnerTypeId(),
	'required' => false
);

if(Thurly\Crm\Integration\ThurlyOSEmail::isEnabled())
{
	if(Thurly\Crm\Integration\ThurlyOSEmail::allowDisableSignature())
	{
		$arResult['FIELDS']['tab_activity_config'][] = array(
			'id' => 'ENABLE_B24_EMAIL_SIGNATURE',
			'name' => GetMessage('CRM_FIELD_ENABLE_B24_EMAIL_SIGNATURE'),
			'type' => 'checkbox',
			'value' => Thurly\Crm\Integration\ThurlyOSEmail::isSignatureEnabled(),
			'required' => false
		);
	}
	else
	{
		$arResult['FIELDS']['tab_activity_config'][] = array(
			'id' => 'ENABLE_B24_EMAIL_SIGNATURE',
			'name' => GetMessage('CRM_FIELD_ENABLE_B24_EMAIL_SIGNATURE'),
			'type' => 'label',
			'value' =>  Thurly\Crm\Integration\ThurlyOSEmail::getSignatureExplanation(),
			'required' => false
		);
	}
}

$arResult['FIELDS']['tab_history'][] = array(
	'id' => 'ENABLE_EXPORT_EVENT',
	'name' => GetMessage('CRM_FIELD_ENABLE_EXPORT_EVENT'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\HistorySettings::getCurrent()->isExportEventEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_history'][] = array(
	'id' => 'ENABLE_VIEW_EVENT',
	'name' => GetMessage('CRM_FIELD_ENABLE_VIEW_EVENT'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\HistorySettings::getCurrent()->isViewEventEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_history'][] = array(
	'id' => 'VIEW_EVENT_GROUPING_INTERVAL',
	'name' => GetMessage('CRM_FIELD_VIEW_EVENT_GROUPING_INTERVAL'),
	'type' => 'input',
	'value' => \Thurly\Crm\Settings\HistorySettings::getCurrent()->getViewEventGroupingInterval(),
	'required' => false
);

$arResult['FIELDS']['tab_history'][] = array(
	'id' => 'ENABLE_LEAD_DELETION_EVENT',
	'name' => GetMessage('CRM_FIELD_ENABLE_LEAD_DELETION_EVENT'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\HistorySettings::getCurrent()->isLeadDeletionEventEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_history'][] = array(
	'id' => 'ENABLE_CONTACT_DELETION_EVENT',
	'name' => GetMessage('CRM_FIELD_ENABLE_CONTACT_DELETION_EVENT'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\HistorySettings::getCurrent()->isContactDeletionEventEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_history'][] = array(
	'id' => 'ENABLE_COMPANY_DELETION_EVENT',
	'name' => GetMessage('CRM_FIELD_ENABLE_COMPANY_DELETION_EVENT'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\HistorySettings::getCurrent()->isCompanyDeletionEventEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_history'][] = array(
	'id' => 'ENABLE_DEAL_DELETION_EVENT',
	'name' => GetMessage('CRM_FIELD_ENABLE_DEAL_DELETION_EVENT'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\HistorySettings::getCurrent()->isDealDeletionEventEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_history'][] = array(
	'id' => 'ENABLE_QUOTE_DELETION_EVENT',
	'name' => GetMessage('CRM_FIELD_ENABLE_QUOTE_DELETION_EVENT'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\HistorySettings::getCurrent()->isQuoteDeletionEventEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_livefeed'][] = array(
	'id' => 'ENABLE_LIVEFEED_MERGE',
	'name' => GetMessage('CRM_FIELD_ENABLE_LIVEFEED_MERGE'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\LiveFeedSettings::getCurrent()->isLiveFeedMergeEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'PERSON_NAME_FORMAT_ID',
	'name' => GetMessage('CRM_FIELD_PERSON_NAME_FORMAT'),
	'type' => 'list',
	'items' => \Thurly\Crm\Format\PersonNameFormatter::getAllDescriptions(),
	'value' => \Thurly\Crm\Format\PersonNameFormatter::getFormatID(),
	'required' => false
);

$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'CALLTO_FORMAT',
	'name' => GetMessage('CRM_FIELD_CALLTO_FORMAT'),
	'type' => 'list',
	'items' => CCrmCallToUrl::GetAllDescriptions(),
	'value' => CCrmCallToUrl::GetFormat(CCrmCallToUrl::Thurly),
	'required' => false
);

$calltoSettings = CCrmCallToUrl::GetCustomSettings();
$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'CALLTO_URL_TEMPLATE',
	'name' => GetMessage('CRM_FIELD_CALLTO_URL_TEMPLATE'),
	'type' => 'text',
	'value' => isset($calltoSettings['URL_TEMPLATE']) ? htmlspecialcharsbx($calltoSettings['URL_TEMPLATE']) : 'callto:[phone]',
	'required' => false
);

$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'CALLTO_CLICK_HANDLER',
	'name' => GetMessage('CRM_FIELD_CALLTO_CLICK_HANDLER'),
	'type' => 'textarea',
	'value' => isset($calltoSettings['CLICK_HANDLER']) ? htmlspecialcharsbx($calltoSettings['CLICK_HANDLER']) : '',
	'required' => false
);

$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'CALLTO_NORMALIZE_NUMBER',
	'name' => GetMessage('CRM_FIELD_CALLTO_NORMALIZE_NUMBER'),
	'type' => 'checkbox',
	'value' => isset($calltoSettings['NORMALIZE_NUMBER']) ? $calltoSettings['NORMALIZE_NUMBER'] === 'Y' : true,
	'required' => false
);

$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'ENABLE_SIMPLE_TIME_FORMAT',
	'name' => GetMessage('CRM_FIELD_ENABLE_SIMPLE_TIME_FORMAT'),
	'type' => 'checkbox',
	'value' => \Thurly\Crm\Settings\LayoutSettings::getCurrent()->isSimpleTimeFormatEnabled(),
	'required' => false
);

$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'section_address_format',
	'name' => GetMessage('CRM_SECTION_ADDRESS_FORMAT'),
	'type' => 'section'
);

$curAddrFormatID = \Thurly\Crm\Format\EntityAddressFormatter::getFormatID();
$addrFormatDescrs = \Thurly\Crm\Format\EntityAddressFormatter::getAllDescriptions();
$arResult['ADDR_FORMAT_INFOS'] = \Thurly\Crm\Format\EntityAddressFormatter::getAllExamples();
$arResult['ADDR_FORMAT_CONTROL_PREFIX'] = 'addr_format_';

$addrFormatControls = array();
foreach($addrFormatDescrs as $addrFormatID => $addrFormatDescr)
{
	$isChecked = $addrFormatID === $curAddrFormatID;
	$addrFormatControlID = $arResult['ADDR_FORMAT_CONTROL_PREFIX'].$addrFormatID;
	$addrFormatControls[] = '<input type="radio" class="crm-dup-control-type-radio" id="'.$addrFormatControlID.'" name="ENTITY_ADDRESS_FORMAT_ID" value="'.$addrFormatID.'"'.($isChecked ? ' checked="checked"' : '').'/><label class="crm-dup-control-type-label" for="'.$addrFormatControlID.'">'.htmlspecialcharsbx($addrFormatDescr).'</label>';
}
$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'ENTITY_ADDRESS_FORMAT',
	'type' => 'custom',
	'value' =>
		'<div class="crm-dup-control-type-radio-title">'.GetMessage('CRM_FIELD_ENTITY_ADDRESS_FORMAT').':</div>'.
		'<div class="crm-dup-control-type-radio-wrap">'.
		implode('', $addrFormatControls).
		'</div>',
	'colspan' => true
);

$arResult['ADDR_FORMAT_DESCR_ID'] = 'addr_format_descr';
$arResult['FIELDS']['tab_format'][] = array(
	'id' => 'ENTITY_ADDRESS_FORMAT_DESCR',
	'type' => 'custom',
	'value' => '<div class="crm-dup-control-type-info" id="'.$arResult['ADDR_FORMAT_DESCR_ID'].'">'.$arResult['ADDR_FORMAT_INFOS'][$curAddrFormatID].'</div>',
	'colspan' => true
);

ob_start();

$APPLICATION->IncludeComponent(
	'thurly:crm.config.invoice.number',
	'',
	array(),
	''
);

$sVal = ob_get_contents();
ob_end_clean();

$arResult['FIELDS']['tab_inv_nums'][] = array(
	'id' => 'INVOICE_NUMBERS_FORMAT',
	'name' => GetMessage('CRM_INVOICE_NUMBERS_FORMAT'),
	'type' => 'custom',
	'colspan' => true,
	'value' => $sVal,
	'required' => false
);

ob_start();
$APPLICATION->IncludeComponent(
	'thurly:crm.config.number',
	'',
	array('ENTITY_NAME' => CCrmOwnerType::QuoteName)
);
$sVal = ob_get_contents();
ob_end_clean();

$arResult['FIELDS']['tab_quote_nums'][] = array(
	'id' => 'QUOTE_NUMBERS_FORMAT',
	'name' => GetMessage('CRM_QUOTE_NUMBERS_FORMAT'),
	'type' => 'custom',
	'colspan' => true,
	'value' => $sVal,
	'required' => false
);

$arResult['FIELDS']['tab_dup_control'][] = array(
	'id' => 'ENABLE_LEAD_DUP_CONTROL',
	'name' => GetMessage('CRM_FIELD_ENABLE_LEAD_DUP_CONTROL'),
	'type' => 'checkbox',
	'value' => $dupControl->isEnabledFor(CCrmOwnerType::Lead),
	'required' => false
);

$arResult['FIELDS']['tab_dup_control'][] = array(
	'id' => 'ENABLE_CONTACT_DUP_CONTROL',
	'name' => GetMessage('CRM_FIELD_ENABLE_CONTACT_DUP_CONTROL'),
	'type' => 'checkbox',
	'value' => $dupControl->isEnabledFor(CCrmOwnerType::Contact),
	'required' => false
);

$arResult['FIELDS']['tab_dup_control'][] = array(
	'id' => 'ENABLE_COMPANY_DUP_CONTROL',
	'name' => GetMessage('CRM_FIELD_ENABLE_COMPANY_DUP_CONTROL'),
	'type' => 'checkbox',
	'value' => $dupControl->isEnabledFor(CCrmOwnerType::Company),
	'required' => false
);

$arResult['FIELDS']['tab_status_config'][] = array(
	'id' => 'ENABLE_DEPRECATED_STATUSES',
	'name' => GetMessage('CRM_FIELD_ENABLE_DEPRECATED_STATUSES'),
	'type' => 'checkbox',
	'value' => CCrmStatus::IsDepricatedTypesEnabled(),
	'required' => false
);

$this->IncludeComponentTemplate();

$APPLICATION->AddChainItem(GetMessage('CRM_SM_LIST'), $arParams['PATH_TO_SM_CONFIG']);
?>