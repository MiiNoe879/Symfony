<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (isset($arResult['ERROR']))
{
	ShowError($arResult['ERROR']);
	return;
}

use \Thurly\Main\Localization\Loc;
use \Thurly\Crm\Kanban\Helper;
use \Thurly\Crm\Conversion\LeadConversionScheme;
use \Thurly\Crm\Category\DealCategory;
use \Thurly\Crm\Conversion\EntityConverter;

Loc::loadMessages(__FILE__);

$this->addExternalCss('/thurly/themes/.default/crm-entity-show.css');
$APPLICATION->SetPageProperty('BodyClass', 'no-all-paddings grid-mode pagetitle-toolbar-field-view crm-toolbar no-background');

$data = $arResult['ITEMS'];
$date = new \Thurly\Main\Type\Date;
$demoAccess = \CJSCore::IsExtRegistered('intranet_notify_dialog') && \Thurly\Main\Loader::includeModule('im');


// js extension reg
$assetRoot = '/thurly/js/crm/';
$langRoot = BX_ROOT . '/modules/crm/lang/' . LANGUAGE_ID . '/';
\CJSCore::RegisterExt('crm_common', array(
	'js' => array('/thurly/js/crm/crm.js', '/thurly/js/crm/common.js')
));
\CJSCore::RegisterExt('crm_activity_type', array(
	'js' => array('/thurly/js/crm/activity.js')
));
\CJSCore::RegisterExt('popup_menu', array(
	'js' => array('/thurly/js/main/popup_menu.js')
));
\CJSCore::registerExt(
	'crm_kanban',
	array(
		'js'  => array(
			$assetRoot . 'kanban/grid.js',
			$assetRoot . 'kanban/item.js',
			$assetRoot . 'kanban/column.js'
		),
		'css' => array(
			$assetRoot . 'kanban/css/kanban.css',
		),
		'rel' => array('kanban', 'ajax', 'color_picker', 'date'),
		'lang' => $langRoot . 'kanban.php',
		'bundle_js' => 'crm_kanban',
		'bundle_css' => 'crm_kanban'
	)
);
\CJSCore::Init(array(
	'crm_common',
	'crm_kanban',
	'crm_visit_tracker',
	'crm_activity_type',
	'crm_activity_planner',
	'popup_menu',
	'currency',
	'intranet_notify_dialog'
));
?>
<div id="crm_kanban"></div>

<script type="text/javascript">

	var Kanban;
	var ajaxHandlerPath = "<?= $this->getComponent()->getPath()?>/ajax.php";

	(function() {

		"use strict";

		BX.Currency.setCurrencyFormat(
			"<?= $arParams['CURRENCY']?>",
			<?= \CUtil::PhpToJSObject(\CCurrencyLang::GetFormatDescription($arParams['CURRENCY']), false, true)?>
		);


		Kanban = new BX.CRM.Kanban.Grid({
			renderTo: BX("crm_kanban"),
			itemType: "BX.CRM.Kanban.Item",
			columnType: "BX.CRM.Kanban.Column",
			canAddColumn: <?= $demoAccess ? 'true' : ($arResult['ACCESS_CONFIG_PERMS'] ? 'true' : 'false')?>,
			canEditColumn: <?= $demoAccess ? 'true' : ($arResult['ACCESS_CONFIG_PERMS'] ? 'true' : 'false')?>,
			canRemoveColumn: <?= $arResult['ACCESS_CONFIG_PERMS'] ? 'true' : 'false'?>,
			canSortColumn: <?= $arResult['ACCESS_CONFIG_PERMS'] ? 'true' : 'false'?>,
			canSortItem: true,
			bgColor: <?= (SITE_TEMPLATE_ID === 'thurlyos' ? '"transparent"' : 'null')?>,
			columns: <?= \CUtil::PhpToJSObject(array_values($data['columns']), false, false, true)?>,
			items: <?= \CUtil::PhpToJSObject($data['items'], false, false, true)?>,
			dropZones: <?= \CUtil::PhpToJSObject(array_values($data['dropzones']), false, false, true)?>,
			data: {
				ajaxHandlerPath: ajaxHandlerPath,
				entityType: "<?= \CUtil::JSEscape($arParams['ENTITY_TYPE_CHR'])?>",
				entityPath: "<?= \CUtil::JSEscape($arParams['ENTITY_PATH'])?>",
				params: <?= json_encode($arParams['EXTRA'])?>,
				gridId: "<?= Helper::getGridId($arParams['ENTITY_TYPE_CHR'])?>",
				showActivity: <?= $arParams['SHOW_ACTIVITY'] == 'Y' ? 'true' : 'false'?>,
				currency: "<?= $arParams['CURRENCY']?>",
				lastId: <?= (int)$data['last_id']?>,
				rights: {
					canAddColumn: <?= $arResult['ACCESS_CONFIG_PERMS'] ? 'true' : 'false'?>,
					canEditColumn: <?= $arResult['ACCESS_CONFIG_PERMS'] ? 'true' : 'false'?>,
					canRemoveColumn: <?= $arResult['ACCESS_CONFIG_PERMS'] ? 'true' : 'false'?>,
					canSortColumn: <?= $arResult['ACCESS_CONFIG_PERMS'] ? 'true' : 'false'?>,
					canSortItem: true

				},
				visitParams: <?= \CUtil::PhpToJSObject(\Thurly\Crm\Activity\Provider\Visit::getPopupParameters(), false, false, true)?>,
				admins: <?= \CUtil::PhpToJSObject(array_values($arResult['ADMINS']))?>
			}
		});

		Kanban.draw();

		<?if ($arParams['ENTITY_TYPE_CHR'] == 'LEAD' || $arParams['ENTITY_TYPE_CHR'] == 'INVOICE'):?>
		BX.addCustomEvent("Crm.Kanban.Grid:onItemMovedFinal", BX.delegate(BX.Crm.KanbanComponent.columnPopup, this));
		<?endif;?>

		<?if ($arParams['ENTITY_TYPE_CHR'] == 'LEAD' || $arParams['ENTITY_TYPE_CHR'] == 'INVOICE'):?>
		BX.addCustomEvent("Crm.Kanban.Grid:onBeforeItemCapturedStart", BX.delegate(BX.Crm.KanbanComponent.dropPopup, this));
		<?endif;?>

		<?if ($arParams['ENTITY_TYPE_CHR'] == 'LEAD'):?>
		BX.addCustomEvent("onPopupClose", BX.proxy(BX.Crm.KanbanComponent.onPopupClose, this));
		<?endif;?>
	})();

</script>

<script type="text/javascript">
	BX.message({
		CRM_KANBAN_POPUP_PARAMS_SAVE: "<?= CUtil::JSEscape(Loc::getMessage('CRM_KANBAN_POPUP_PARAMS_SAVE'));?>",
		CRM_KANBAN_POPUP_PARAMS_CANCEL: "<?= CUtil::JSEscape(Loc::getMessage('CRM_KANBAN_POPUP_PARAMS_CANCEL'));?>"
	});
</script>

<?include $_SERVER['DOCUMENT_ROOT'] . $this->getFolder() . '/popups.php'?>

<?if ($arParams['ENTITY_TYPE_CHR'] == 'LEAD'):
	Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/thurly/components/thurly/crm.lead.list/templates/.default/template.php');
	?>
	<script type="text/javascript">
		BX.ready(
			function()
			{
				BX.CrmEntityType.captions =
				{
					<?= CCrmOwnerType::LeadName?>: "<?= CCrmOwnerType::GetDescription(CCrmOwnerType::Lead)?>",
					<?= CCrmOwnerType::ContactName?>: "<?= CCrmOwnerType::GetDescription(CCrmOwnerType::Contact)?>",
					<?= CCrmOwnerType::CompanyName?>: "<?= CCrmOwnerType::GetDescription(CCrmOwnerType::Company)?>",
					<?= CCrmOwnerType::DealName?>: "<?= CCrmOwnerType::GetDescription(CCrmOwnerType::Deal)?>",
					<?= CCrmOwnerType::InvoiceName?>: "<?= CCrmOwnerType::GetDescription(CCrmOwnerType::Invoice)?>",
					<?= CCrmOwnerType::QuoteName?>: "<?= CCrmOwnerType::GetDescription(CCrmOwnerType::Quote)?>"
				};
				BX.CrmLeadConversionScheme.messages =
					<?= \CUtil::PhpToJSObject(LeadConversionScheme::getJavaScriptDescriptions(false))?>;
				BX.CrmLeadConverter.messages =
				{
					accessDenied: "<?= GetMessageJS('CRM_LEAD_CONV_ACCESS_DENIED')?>",
					generalError: "<?= GetMessageJS('CRM_LEAD_CONV_GENERAL_ERROR')?>",
					dialogTitle: "<?= GetMessageJS('CRM_LEAD_CONV_DIALOG_TITLE')?>",
					syncEditorLegend: "<?= GetMessageJS('CRM_LEAD_CONV_DIALOG_SYNC_LEGEND')?>",
					syncEditorFieldListTitle: "<?= GetMessageJS('CRM_LEAD_CONV_DIALOG_SYNC_FILED_LIST_TITLE')?>",
					syncEditorEntityListTitle: "<?= GetMessageJS('CRM_LEAD_CONV_DIALOG_SYNC_ENTITY_LIST_TITLE')?>",
					continueButton: "<?= GetMessageJS('CRM_LEAD_CONV_DIALOG_CONTINUE_BTN')?>",
					cancelButton: "<?= GetMessageJS('CRM_LEAD_CONV_DIALOG_CANCEL_BTN')?>",
					selectButton: "<?= GetMessageJS('CRM_LEAD_CONV_ENTITY_SEL_BTN')?>",
					openEntitySelector: "<?= GetMessageJS('CRM_LEAD_CONV_OPEN_ENTITY_SEL')?>",
					entitySelectorTitle: "<?= GetMessageJS('CRM_LEAD_CONV_ENTITY_SEL_TITLE')?>",
					contact: "<?= GetMessageJS('CRM_LEAD_CONV_ENTITY_SEL_CONTACT')?>",
					company: "<?= GetMessageJS('CRM_LEAD_CONV_ENTITY_SEL_COMPANY')?>",
					noresult: "<?= GetMessageJS('CRM_LEAD_CONV_ENTITY_SEL_SEARCH_NO_RESULT')?>",
					search : "<?= GetMessageJS('CRM_LEAD_CONV_ENTITY_SEL_SEARCH')?>",
					last : "<?= GetMessageJS('CRM_LEAD_CONV_ENTITY_SEL_LAST')?>"
				};
				BX.CrmLeadConverter.permissions =
				{
					contact: <?= CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_CONTACT'])?>,
					company: <?= CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_COMPANY'])?>,
					deal: <?= CUtil::PhpToJSObject($arResult['CAN_CONVERT_TO_DEAL'])?>
				};
				BX.CrmLeadConverter.settings =
				{
					serviceUrl: "<?='/thurly/components/thurly/crm.lead.show/ajax.php?action=convert&'.thurly_sessid_get()?>",
					enablePageRefresh: false,
					enableRedirectToShowPage: false,
					config: <?= CUtil::PhpToJSObject($arResult['CONVERSION_CONFIG']->toJavaScript())?>
				};
				BX.CrmDealCategory.infos = <?= \CUtil::PhpToJSObject(
					DealCategory::getJavaScriptInfos(EntityConverter::getPermittedDealCategoryIDs())
				)?>;
				BX.CrmDealCategorySelectDialog.messages =
				{
					title: "<?=GetMessageJS('CRM_LEAD_LIST_CONV_DEAL_CATEGORY_DLG_TITLE')?>",
					field: "<?=GetMessageJS('CRM_LEAD_LIST_CONV_DEAL_CATEGORY_DLG_FIELD')?>",
					saveButton: "<?=GetMessageJS('CRM_LEAD_LIST_BUTTON_SAVE')?>",
					cancelButton: "<?=GetMessageJS('CRM_LEAD_LIST_BUTTON_CANCEL')?>"
				};
			}
		);
	</script>
<?endif;

$prefix = strtolower('kanban_activity');
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
		'ENABLE_TASK_ADD' => false,
		'MARK_AS_COMPLETED_ON_VIEW' => false
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);
