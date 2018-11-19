<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @var CThurlyComponentTemplate $this */
/** @var CCrmEntityProgressBarComponent $component */

use \Thurly\Crm\Conversion\LeadConversionScheme;
use \Thurly\Crm\Category\DealCategory;
use \Thurly\Crm\Conversion\EntityConverter;

$guid = $arResult['GUID'];
$prefix = strtolower($guid);
$activityEditorID = "{$prefix}_editor";

$APPLICATION->IncludeComponent(
	'thurly:crm.activity.editor',
	'',
	array(
		'CONTAINER_ID' => '',
		'EDITOR_ID' => $activityEditorID,
		'PREFIX' => $prefix,
		'ENABLE_UI' => false,
		'ENABLE_TOOLBAR' => false,
		'ENABLE_EMAIL_ADD' => true,
		'ENABLE_TASK_ADD' => $arResult['ENABLE_TASK'],
		'MARK_AS_COMPLETED_ON_VIEW' => false,
		'SKIP_VISUAL_COMPONENTS' => 'Y'
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);

$APPLICATION->IncludeComponent(
	'thurly:crm.lead.menu',
	'',
	array(
		'PATH_TO_LEAD_LIST' => $arResult['PATH_TO_LEAD_LIST'],
		'PATH_TO_LEAD_SHOW' => $arResult['PATH_TO_LEAD_SHOW'],
		'PATH_TO_LEAD_EDIT' => $arResult['PATH_TO_LEAD_EDIT'],
		'PATH_TO_LEAD_IMPORT' => $arResult['PATH_TO_LEAD_IMPORT'],
		'ELEMENT_ID' => $arResult['ENTITY_ID'],
		'MULTIFIELD_DATA' => isset($arResult['ENTITY_DATA']['MULTIFIELD_DATA'])
			? $arResult['ENTITY_DATA']['MULTIFIELD_DATA'] : array(),
		'OWNER_INFO' => $arResult['ENTITY_INFO'],
		'CONVERSION_PERMITTED' => $arResult['CONVERSION_PERMITTED'],
		'BIZPROC_STARTER_DATA' => $arResult['BIZPROC_STARTER_DATA'],
		'TYPE' => 'details',
		'SCRIPTS' => array(
			'DELETE' => 'BX.Crm.EntityDetailManager.items["'.CUtil::JSEscape($guid).'"].processRemoval();'
		)
	),
	$component
);

?><script type="text/javascript">
		BX.ready(
			function()
			{
				BX.message({ "CRM_TIMELINE_HISTORY_STUB": "<?=GetMessageJS('CRM_LEAD_DETAIL_HISTORY_STUB')?>" });
			}
		);
</script><?

//$arResult['READ_ONLY'] = true;
$editorContext = array('PARAMS' => $arResult['CONTEXT_PARAMS']);
if(isset($arResult['ORIGIN_ID']) && $arResult['ORIGIN_ID'] !== '')
{
	$editorContext['ORIGIN_ID'] = $arResult['ORIGIN_ID'];
}
$APPLICATION->IncludeComponent(
	'thurly:crm.entity.details',
	'',
	array(
		'GUID' => $guid,
		'ENTITY_TYPE_ID' => \CCrmOwnerType::Lead,
		'ENTITY_ID' => $arResult['IS_EDIT_MODE'] ? $arResult['ENTITY_ID'] : 0,
		'ENTITY_INFO' => $arResult['ENTITY_INFO'],
		'READ_ONLY' => $arResult['READ_ONLY'],
		'TABS' => $arResult['TABS'],
		'SERVICE_URL' => '/thurly/components/thurly/crm.lead.details/ajax.php?action=convert&'.thurly_sessid_get(),
		'EDITOR' => array(
			'GUID' => "{$guid}_editor",
			'CONFIG_ID' => $arResult['EDITOR_CONFIG_ID'],
			'ENTITY_CONFIG' => $arResult['ENTITY_CONFIG'],
			'DUPLICATE_CONTROL' => $arResult['DUPLICATE_CONTROL'],
			'ENTITY_CONTROLLERS' => $arResult['ENTITY_CONTROLLERS'],
			'ENTITY_FIELDS' => $arResult['ENTITY_FIELDS'],
			'ENTITY_DATA' => $arResult['ENTITY_DATA'],
			'ENABLE_SECTION_EDIT' => true,
			'ENABLE_SECTION_CREATION' => true,
			'ENABLE_USER_FIELD_CREATION' => $arResult['ENABLE_USER_FIELD_CREATION'],
			'USER_FIELD_ENTITY_ID' => $arResult['USER_FIELD_ENTITY_ID'],
			'USER_FIELD_CREATE_PAGE_URL' => $arResult['USER_FIELD_CREATE_PAGE_URL'],
			'USER_FIELD_CREATE_SIGNATURE' => $arResult['USER_FIELD_CREATE_SIGNATURE'],
			'SERVICE_URL' => '/thurly/components/thurly/crm.lead.details/ajax.php?'.thurly_sessid_get(),
			'EXTERNAL_CONTEXT_ID' => $arResult['EXTERNAL_CONTEXT_ID'],
			'CONTEXT_ID' => $arResult['CONTEXT_ID'],
			'CONTEXT' => $editorContext
		),
		'TIMELINE' => array(
			'GUID' => "{$guid}_timeline",
			'ENABLE_WAIT' => true,
			'PROGRESS_SEMANTICS' => $arResult['PROGRESS_SEMANTICS'],
			'WAIT_TARGET_DATES' => $arResult['WAIT_TARGET_DATES']
		),
		'ENABLE_PROGRESS_BAR' => true,
		'ENABLE_PROGRESS_CHANGE' => $arResult['ENABLE_PROGRESS_CHANGE'],
		'ACTIVITY_EDITOR_ID' => $activityEditorID,
		'PATH_TO_USER_PROFILE' => $arResult['PATH_TO_USER_PROFILE'],
		'CAN_CONVERT' => isset($arResult['CAN_CONVERT']) ? $arResult['CAN_CONVERT'] : false,
		'CONVERSION_SCHEME' => isset($arResult['CONVERSION_SCHEME']) ? $arResult['CONVERSION_SCHEME'] : null
	)
);

if($arResult['CONVERSION_PERMITTED'] && $arResult['CAN_CONVERT'] && isset($arResult['CONVERSION_CONFIG'])):
?><script type="text/javascript">
		BX.ready(
			function()
			{
				BX.CrmLeadConversionScheme.messages =
					<?=CUtil::PhpToJSObject(LeadConversionScheme::getJavaScriptDescriptions(false))?>;

				BX.CrmLeadConverter.messages =
				{
					accessDenied: "<?=GetMessageJS("CRM_LEAD_CONV_ACCESS_DENIED")?>",
					generalError: "<?=GetMessageJS("CRM_LEAD_CONV_GENERAL_ERROR")?>",
					dialogTitle: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_TITLE")?>",
					syncEditorLegend: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_SYNC_LEGEND")?>",
					syncEditorFieldListTitle: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_SYNC_FILED_LIST_TITLE")?>",
					syncEditorEntityListTitle: "<?=GetMessageJS("CRM_LEAD_CONV_DIALOG_SYNC_ENTITY_LIST_TITLE")?>",
					continueButton: "<?=GetMessageJS("CRM_LEAD_DETAIL_CONTINUE_BTN")?>",
					cancelButton: "<?=GetMessageJS("CRM_LEAD_DETAIL_CANCEL_BTN")?>",
					selectButton: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_BTN")?>",
					openEntitySelector: "<?=GetMessageJS("CRM_LEAD_CONV_OPEN_ENTITY_SEL")?>",
					entitySelectorTitle: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_TITLE")?>",
					contact: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_CONTACT")?>",
					company: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_COMPANY")?>",
					noresult: "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_SEARCH_NO_RESULT")?>",
					search : "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_SEARCH")?>",
					last : "<?=GetMessageJS("CRM_LEAD_CONV_ENTITY_SEL_LAST")?>"
				};
				BX.CrmLeadConverter.permissions =
				{
					contact: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_CONTACT'])?>,
					company: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_COMPANY'])?>,
					deal: <?=CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_DEAL'])?>
				};
				BX.CrmLeadConverter.settings =
				{
					serviceUrl: "<?='/thurly/components/thurly/crm.lead.show/ajax.php?action=convert&'.thurly_sessid_get()?>",
					config: <?=CUtil::PhpToJSObject($arResult['CONVERSION_CONFIG']->toJavaScript())?>,
					enableSlider: true
				};
				BX.CrmDealCategory.infos = <?=CUtil::PhpToJSObject(
					DealCategory::getJavaScriptInfos(EntityConverter::getPermittedDealCategoryIDs())
				)?>;
				BX.CrmDealCategorySelectDialog.messages =
				{
					title: "<?=GetMessageJS('CRM_LEAD_CONV_DEAL_CATEGORY_DLG_TITLE')?>",
					field: "<?=GetMessageJS('CRM_LEAD_CONV_DEAL_CATEGORY_DLG_FIELD')?>",
					saveButton: "<?=GetMessageJS('CRM_LEAD_DETAIL_BUTTON_SAVE')?>",
					cancelButton: "<?=GetMessageJS('CRM_LEAD_DETAIL_BUTTON_CANCEL')?>"
				};
				BX.CrmEntityType.setCaptions(<?=CUtil::PhpToJSObject(CCrmOwnerType::GetJavascriptDescriptions())?>);
				BX.onCustomEvent(window, "BX.CrmEntityConverter:applyPermissions", [BX.CrmEntityType.names.lead]);
			}
		);
	</script><?
endif;
