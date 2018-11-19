<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * Thurly vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $component
 */

CJSCore::Init(array('ajax', 'uf', 'uploader', 'avatar_editor', 'core_money_editor', 'tooltip', 'phone_number'));
\Thurly\Main\UI\Extension::load("ui.buttons");

$guid = $arResult['GUID'];
$prefix = strtolower($guid);
$containerID = "{$prefix}_container";
$buttonContainerID = "{$prefix}_buttons";
$createSectionButtonID = "{$prefix}_create_section";

if($arResult['REST_USE'])
{
	$restSectionButtonID = "{$prefix}_rest_section";
	$arResult['REST_PLACEMENT_TAB_CONFIG']['bottom_button_id'] = $restSectionButtonID;
}

$htmlEditorConfig = array(
	'id' => "{$prefix}_html_editor",
	'containerId' => "{$prefix}_html_editor_container"
);

//\Thurly\Main\Page\Asset::getInstance()->addJs('/thurly/js/main/core/core_dragdrop.js');
\Thurly\Main\Page\Asset::getInstance()->addJs('/thurly/js/crm/interface_form.js');
\Thurly\Main\Page\Asset::getInstance()->addJs('/thurly/js/crm/common.js');
\Thurly\Main\Page\Asset::getInstance()->addJs('/thurly/js/main/dd.js');
\Thurly\Main\Page\Asset::getInstance()->addJs('/thurly/js/crm/dialog.js');

if(Thurly\Main\Loader::includeModule('socialnetwork'))
{
	\CJSCore::init(array('socnetlogdest'));

	$destSort = CSocNetLogDestination::GetDestinationSort(
			array('DEST_CONTEXT' => \Thurly\Crm\Entity\EntityEditor::getUserSelectorContext())
	);
	$last = array();
	CSocNetLogDestination::fillLastDestination($destSort, $last);

	$destUserIDs = array();
	if(isset($last['USERS']))
	{
		foreach($last['USERS'] as $code)
		{
			$destUserIDs[] = str_replace('U', '', $code);
		}
	}

	$dstUsers = CSocNetLogDestination::GetUsers(array('id' => $destUserIDs));
	$structure = CSocNetLogDestination::GetStucture(array('LAZY_LOAD' => true));

	$department = $structure['department'];
	$departmentRelation = $structure['department_relation'];
	$departmentRelationHead = $structure['department_relation_head'];
	?><script type="text/javascript">
		BX.ready(
			function()
			{
				BX.Crm.EntityEditorUserSelector.users =  <?=CUtil::PhpToJSObject($dstUsers)?>;
				BX.Crm.EntityEditorUserSelector.department = <?=CUtil::PhpToJSObject($department)?>;
				BX.Crm.EntityEditorUserSelector.departmentRelation = <?=CUtil::PhpToJSObject($departmentRelation)?>;
				BX.Crm.EntityEditorUserSelector.last = <?=CUtil::PhpToJSObject(array_change_key_case($last, CASE_LOWER))?>;

				BX.Crm.EntityEditorCrmSelector.contacts = {};
				BX.Crm.EntityEditorCrmSelector.contactsLast = {};
				BX.Crm.EntityEditorCrmSelector.companies = {};
				BX.Crm.EntityEditorCrmSelector.companiesLast = {};
			}
		);
	</script><?
}

?><div class="crm-entity-card-container">
	<div class="crm-entity-card-container-content" id="<?=htmlspecialcharsbx($containerID)?>">
	</div>
	<div class="crm-entity-card-widget-add-btn-container" id="<?=htmlspecialcharsbx($buttonContainerID)?>">
		<span id="<?=htmlspecialcharsbx($createSectionButtonID)?>" class="crm-entity-add-widget-btn">
			<?=GetMessage('CRM_ENTITY_ED_CREATE_SECTION')?>
		</span>
<?
if($arResult['REST_USE'])
{
?>
		<span id="<?=htmlspecialcharsbx($restSectionButtonID)?>" class="crm-entity-add-widget-btn">
			<?=GetMessage('CRM_ENTITY_ED_REST_SECTION')?>
		</span>
<?
}
?>

	</div>
</div>
<div id="<?=htmlspecialcharsbx($htmlEditorConfig['containerId'])?>" style="display:none;"><?
	CModule::IncludeModule('fileman');
	$editor = new CHTMLEditor();
	$editor->Show(
		array(
			'name' => $htmlEditorConfig['id'],
			'id' => $htmlEditorConfig['id'],
			'siteId' => SITE_ID,
			'width' => '100%',
			'minBodyWidth' => '100%',
			'normalBodyWidth' => '100%',
			'height' => 200,
			'minBodyHeight' => 200,
			'showTaskbars' => false,
			'showNodeNavi' => false,
			'autoResize' => true,
			'autoResizeOffset' => 50,
			'bbCode' => false,
			'saveOnBlur' => false,
			'bAllowPhp' => false,
			'lazyLoad' => true,
			'limitPhpAccess' => false,
			'setFocusAfterShow' => false,
			'askBeforeUnloadPage' => false,
			'useFileDialogs' => false,
			'controlsMap' => array(
				array('id' => 'Bold',  'compact' => true, 'sort' => 10),
				array('id' => 'Italic',  'compact' => true, 'sort' => 20),
				array('id' => 'Underline',  'compact' => true, 'sort' => 30),
				array('id' => 'Strikeout',  'compact' => true, 'sort' => 40),
				array('id' => 'RemoveFormat',  'compact' => true, 'sort' => 50),
				array('id' => 'Color',  'compact' => true, 'sort' => 60),
				array('id' => 'FontSelector',  'compact' => false, 'sort' => 70),
				array('id' => 'FontSize',  'compact' => false, 'sort' => 80),
				array('separator' => true, 'compact' => false, 'sort' => 90),
				array('id' => 'OrderedList',  'compact' => true, 'sort' => 100),
				array('id' => 'UnorderedList',  'compact' => true, 'sort' => 110),
				array('id' => 'AlignList', 'compact' => false, 'sort' => 120),
				array('separator' => true, 'compact' => false, 'sort' => 130),
				array('id' => 'InsertLink',  'compact' => true, 'sort' => 140),
				array('id' => 'Code',  'compact' => true, 'sort' => 180),
				array('id' => 'Quote',  'compact' => true, 'sort' => 190),
				array('separator' => true, 'compact' => false, 'sort' => 200),
				array('id' => 'Fullscreen',  'compact' => false, 'sort' => 210)
			)
		)
	);
?></div>
<?if (!empty($arResult['BIZPROC_MANAGER_CONFIG'])):
	$arResult['BIZPROC_MANAGER_CONFIG']['containerId'] = "{$prefix}_bizproc_manager_container";
?><div id="<?=htmlspecialcharsbx($arResult['BIZPROC_MANAGER_CONFIG']['containerId'])?>" style="display:none;"><?
	\CJSCore::init(array('bp_starter'));
	$APPLICATION->IncludeComponent("thurly:bizproc.workflow.start",
		'modern',
		array(
			"MODULE_ID" => $arResult['BIZPROC_MANAGER_CONFIG']['moduleId'],
			"ENTITY" => $arResult['BIZPROC_MANAGER_CONFIG']['entity'],
			"DOCUMENT_TYPE" => $arResult['BIZPROC_MANAGER_CONFIG']['documentType'],
			"AUTO_EXECUTE_TYPE" => $arResult['BIZPROC_MANAGER_CONFIG']['autoExecuteType'],
		)
	);
?></div>
<?endif?>
<script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmEntityType.captions =
			{
				<?=CCrmOwnerType::LeadName?>: "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Lead)?>",
				<?=CCrmOwnerType::ContactName?>: "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Contact)?>",
				<?=CCrmOwnerType::CompanyName?>: "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Company)?>",
				<?=CCrmOwnerType::DealName?>: "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Deal)?>",
				<?=CCrmOwnerType::InvoiceName?>: "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Invoice)?>",
				<?=CCrmOwnerType::QuoteName?>: "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::Quote)?>",
				<?=CCrmOwnerType::DealRecurringName?>: "<?=CCrmOwnerType::GetDescription(CCrmOwnerType::DealRecurring)?>"
			};

			var config = BX.Crm.EntityConfig.create(
				"<?=CUtil::JSEscape($arResult['CONFIG_ID'])?>",
				{
					data: <?=CUtil::PhpToJSObject($arResult['ENTITY_CONFIG'])?>,
					serviceUrl: "<?='/thurly/components/thurly/crm.entity.editor/settings.php?'.thurly_sessid_get()?>"
				}
			);

			var scheme = BX.Crm.EntityScheme.create(
				"<?=CUtil::JSEscape($guid)?>",
				{
					current: <?=CUtil::PhpToJSObject($arResult['ENTITY_SCHEME'])?>,
					available: <?=CUtil::PhpToJSObject($arResult['ENTITY_AVAILABLE_FIELDS'])?>
				}
			);

			var model = BX.Crm.EntityEditorModelFactory.create(
				<?=$arResult['ENTITY_TYPE_ID']?>,
				"",
				{ data: <?=CUtil::PhpToJSObject($arResult['ENTITY_DATA'])?> }
			);

			var userFieldManager = BX.Crm.EntityUserFieldManager.create(
				"<?=CUtil::JSEscape($guid)?>",
				{
					entityId: <?=$arResult['ENTITY_ID']?>,
					enableCreation: <?=$arResult['ENABLE_USER_FIELD_CREATION'] ? 'true' : 'false'?>,
					fieldEntityId: "<?=CUtil::JSEscape($arResult['USER_FIELD_ENTITY_ID'])?>",
					creationSignature: "<?=CUtil::JSEscape($arResult['USER_FIELD_CREATE_SIGNATURE'])?>",
					creationPageUrl: "<?=CUtil::JSEscape($arResult['USER_FIELD_CREATE_PAGE_URL'])?>",
					languages: <?=CUtil::PhpToJSObject($arResult['LANGUAGES'])?>
				}
			);

			BX.CrmDuplicateSummaryPopup.messages =
			{
				title: "<?=GetMessageJS("CRM_ENTITY_ED_DUP_CTRL_SHORT_SUMMARY_TITLE")?>"
			};

			BX.CrmDuplicateWarningDialog.messages =
			{
				title: "<?=GetMessageJS("CRM_ENTITY_ED_DUP_CTRL_WARNING_DLG_TITLE")?>",
				acceptButtonTitle: "<?=GetMessageJS("CRM_ENTITY_ED_DUP_CTRL_WARNING_ACCEPT_BTN_TITLE")?>",
				cancelButtonTitle: "<?=GetMessageJS("CRM_ENTITY_ED_DUP_CTRL_WARNING_CANCEL_BTN_TITLE")?>"
			};

			BX.CrmEntityType.categoryCaptions = <?=CUtil::PhpToJSObject(\CCrmOwnerType::GetAllCategoryCaptions(true))?>;

			BX.Crm.EntityEditor.messages =
			{
				newSectionTitle: "<?=GetMessageJS('CRM_ENTITY_ED_NEW_SECTION_TITLE')?>",
				resetConfig: "<?=GetMessageJS('CRM_ENTITY_ED_RESET_CONFIG')?>",
				resetConfigForAllUsers: "<?=GetMessageJS('CRM_ENTITY_ED_RESET_CONFIG_FOR_ALL')?>",
				saveConfigForAllUsers: "<?=GetMessageJS('CRM_ENTITY_ED_SAVE_CONFIG_FOR_ALL')?>",
				couldNotFindEntityIdError: "<?=GetMessageJS('CRM_ENTITY_ED_COULD_NOT_FIND_ENTITY_ID')?>"
			};

			BX.Crm.EntityUserFieldManager.messages =
			{
				stringLabel: "<?=GetMessageJS('CRM_ENTITY_ED_UF_STRING_LABEL')?>",
				doubleLabel: "<?=GetMessageJS('CRM_ENTITY_ED_UF_DOUBLE_LABEL')?>",
				moneyLabel: "<?=GetMessageJS('CRM_ENTITY_ED_UF_MONEY_LABEL')?>",
				datetimeLabel: "<?=GetMessageJS('CRM_ENTITY_ED_UF_DATETIME_LABEL')?>",
				enumerationLabel: "<?=GetMessageJS('CRM_ENTITY_ED_UF_ENUMERATION_LABEL')?>",
				fileLabel: "<?=GetMessageJS('CRM_ENTITY_ED_UF_FILE_LABEL')?>",
				label: "<?=GetMessageJS('CRM_ENTITY_ED_UF_LABEL')?>",
				stringTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_STRING_TITLE')?>",
				stringLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_STRING_LEGEND')?>",
				doubleTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_DOUBLE_TITLE')?>",
				doubleLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_DOUBLE_LEGEND')?>",
				moneyTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_MONEY_TITLE')?>",
				moneyLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_MONEY_LEGEND')?>",
				booleanTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_BOOLEAN_TITLE')?>",
				booleanLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_BOOLEAN_LEGEND')?>",
				datetimeTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_DATETIME_TITLE')?>",
				datetimeLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_DATETIME_LEGEND')?>",
				enumerationTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_ENUM_TITLE')?>",
				enumerationLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_ENUM_LEGEND')?>",
				urlTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_URL_TITLE')?>",
				urlLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_URL_LEGEND')?>",
				addressTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_ADDRESS_TITLE')?>",
				addressLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_ADDRESS_LEGEND')?>",
				fileTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_FILE_TITLE')?>",
				fileLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_FILE_LEGEND')?>",
				customTitle: "<?=GetMessageJS('CRM_ENTITY_ED_UF_CUSTOM_TITLE')?>",
				customLegend: "<?=GetMessageJS('CRM_ENTITY_ED_UF_CUSTOM_LEGEND')?>"
			};

			BX.Crm.EntityUserFieldManager.additionalTypeList = <?=\CUtil::PhpToJSObject($arResult['USERFIELD_TYPE_ADDITIONAL'])?>;

			BX.Crm.EntityEditorFieldConfigurator.messages =
			{
				labelField: "<?=GetMessageJS('CRM_ENTITY_ED_FIELD_TITLE')?>"
			};

			BX.Crm.EntityEditorUserFieldConfigurator.messages =
			{
				labelField: "<?=GetMessageJS('CRM_ENTITY_ED_FIELD_TITLE')?>",
				isRequiredField: "<?=GetMessageJS('CRM_ENTITY_ED_UF_REQUIRED_FIELD')?>",
				isMultipleField: "<?=GetMessageJS('CRM_ENTITY_ED_UF_MULTIPLE_FIELD')?>",
				enableTime: "<?=GetMessageJS('CRM_ENTITY_ED_UF_ENABLE_TIME')?>",
				enumItems: "<?=GetMessageJS('CRM_ENTITY_ED_UF_ENUM_ITEMS')?>",
				add: "<?=GetMessageJS('CRM_ENTITY_ED_ADD')?>"
			};

			BX.Crm.EntityEditorField.messages =
			{
				hideButtonHint: "<?=GetMessageJS('CRM_ENTITY_ED_HIDE_BUTTON_HINT')?>",
				hideButtonDisabledHint: "<?=GetMessageJS('CRM_ENTITY_ED_HIDE_BUTTON_DISABLED_HINT')?>",
				requiredFieldError: "<?=GetMessageJS('CRM_ENTITY_ED_REQUIRED_FIELD_ERROR')?>",
				add: "<?=GetMessageJS('CRM_ENTITY_ED_ADD')?>",
				hide: "<?=GetMessageJS('CRM_ENTITY_ED_HIDE')?>",
				configure: "<?=GetMessageJS('CRM_ENTITY_ED_CONFIGURE')?>"
			};

			BX.Crm.EntityEditorSection.messages =
			{
				change: "<?=GetMessageJS('CRM_ENTITY_ED_CHANGE')?>",
				cancel: "<?=GetMessageJS('CRM_ENTITY_ED_CANCEL')?>",
				createField: "<?=GetMessageJS('CRM_ENTITY_ED_CREATE_FIELD')?>",
				selectField: "<?=GetMessageJS('CRM_ENTITY_ED_SELECT_FIELD')?>",
				deleteSection: "<?=GetMessageJS('CRM_ENTITY_ED_DELETE_SECTION')?>",
				deleteSectionConfirm: "<?=GetMessageJS('CRM_ENTITY_ED_DELETE_SECTION_CONFIRM')?>",
				selectFieldFromOtherSection: "<?=GetMessageJS('CRM_ENTITY_ED_SELECT_FIELD_FROM_OTHER_SECTION')?>",
				transferDialogTitle: "<?=GetMessageJS('CRM_ENTITY_ED_FIELD_TRANSFER_DIALOG_TITLE')?>"
			};

			BX.Crm.EntityEditorBoolean.messages =
			{
				yes: "<?=GetMessageJS('MAIN_YES')?>",
				no: "<?=GetMessageJS('MAIN_NO')?>"
			};

			BX.Crm.EntityEditorUser.messages =
			{
				change: "<?=GetMessageJS('CRM_ENTITY_ED_CHANGE_USER')?>"
			};

			BX.Crm.EntityEditorHtml.messages =
			{
				expand: "<?=GetMessageJS('CRM_ENTITY_ED_EXPAND_COMMENT')?>",
				collapse: "<?=GetMessageJS('CRM_ENTITY_ED_COLLAPSE_COMMENT')?>"
			};

			BX.Crm.PrimaryClientEditor.messages =
			{
				select: "<?=GetMessageJS('CRM_ENTITY_ED_SELECT')?>",
				bind: "<?=GetMessageJS('CRM_ENTITY_ED_BIND')?>",
				create: "<?=GetMessageJS('CRM_ENTITY_ED_CREATE')?>"
			};

			BX.Crm.SecondaryClientEditor.messages =
			{
				select: "<?=GetMessageJS('CRM_ENTITY_ED_SELECT')?>",
				create: "<?=GetMessageJS('CRM_ENTITY_ED_CREATE')?>"
			};

			BX.Crm.ClientEditorCommunicationButton.messages =
			{
				telephonyNotSupported: "<?=GetMessageJS('CRM_ENTITY_ED_TELEPHONY_NOT_SUPPORTED')?>"
			};

			BX.Crm.EntityEditorProductRowSummary.messages =
			{
				notShown: "<?=GetMessageJS('CRM_ENTITY_ED_PRODUCT_NOT_SHOWN')?>",
				total: "<?=GetMessageJS('CRM_ENTITY_ED_TOTAL')?>"
			};

			BX.Crm.EntityEditorFieldSelector.messages =
			{
				select: "<?=GetMessageJS('CRM_ENTITY_ED_SELECT')?>",
				cancel: "<?=GetMessageJS('CRM_ENTITY_ED_CANCEL')?>"
			};

			BX.Crm.ClientEditorEntityRequisitePanel.messages =
			{
				toggle: "<?=GetMessageJS('CRM_ENTITY_ED_TOGGLE_REQUISITES')?>"
			};


			BX.Crm.EntityEditorRequisiteSelector.messages =
			{
				bankDetails: "<?=GetMessageJS('CRM_ENTITY_ED_BANK_DETAILS')?>"
			};

			BX.Crm.EntityEditorRequisiteListItem.messages =
			{
				deleteTitle: "<?=GetMessageJS("CRM_ENTITY_ED_REQUISITE_DELETE_DLG_TITLE")?>",
				deleteConfirm: "<?=GetMessageJS("CRM_ENTITY_ED_REQUISITE_DELETE_DLG_CONTENT")?>"
			};

			BX.Crm.EntityEditorRequisiteList.messages =
			{
				deleteTitle: "<?=GetMessageJS("CRM_ENTITY_ED_REQUISITE_DELETE_DLG_TITLE")?>",
				deleteConfirm: "<?=GetMessageJS("CRM_ENTITY_ED_REQUISITE_DELETE_DLG_CONTENT")?>"
			};

			BX.Crm.EntityEditorRecurring.messages =
				{
					createBeforeDate: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_CREATE_BEFORE_DATE')?>",
					directionSelectorTitle: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_DIRECTION_TITLE')?>",
					until: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_UNTIL')?>",
					repeatUntil: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_REPEAT_UNTIL')?>",
					noLimitDate: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_NO_LIMIT_DATE')?>",
					dateLimit: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_DATE_LIMIT')?>",
					finishAfter: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_FINISH_AFTER')?>",
					repeats: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_REPEATS')?>",
					notRepeat: "<?=GetMessageJS('CRM_ENTITY_ED_RECURRING_NOT_REPEAT')?>"
				};

			BX.message(
				{
					"CRM_EDITOR_SAVE": "<?=GetMessageJS('CRM_ENTITY_ED_SAVE')?>",
					"CRM_EDITOR_CANCEL": "<?=GetMessageJS('CRM_ENTITY_ED_CANCEL')?>",
					"CRM_EDITOR_DELETE": "<?=GetMessageJS('CRM_ENTITY_ED_DELETE')?>",
					"CRM_EDITOR_ADD": "<?=GetMessageJS('CRM_ENTITY_ED_ADD')?>"
				}
			);

			var bizprocManager = BX.Crm.EntityBizprocManager.create(
				"<?=CUtil::JSEscape($guid)?>",
				<?=\Thurly\Main\Web\Json::encode($arResult['BIZPROC_MANAGER_CONFIG'])?>
			);
			var restPlacementTabManager = BX.Crm.EntityRestPlacementManager.create(
				"<?=CUtil::JSEscape($guid)?>",
				<?=\CUtil::PhpToJSObject($arResult['REST_PLACEMENT_TAB_CONFIG'])?>
			);

			BX.Crm.EntityEditor.setDefault(
				BX.Crm.EntityEditor.create(
					"<?=CUtil::JSEscape($guid)?>",
					{
						entityTypeId: <?=$arResult['ENTITY_TYPE_ID']?>,
						entityId: <?=$arResult['ENTITY_ID']?>,
						model: model,
						config: config,
						scheme: scheme,
						validators: <?=CUtil::PhpToJSObject($arResult['ENTITY_VALIDATORS'])?>,
						controllers: <?=CUtil::PhpToJSObject($arResult['ENTITY_CONTROLLERS'])?>,
						userFieldManager: userFieldManager,
						bizprocManager: bizprocManager,
						restPlacementTabManager: restPlacementTabManager,
						duplicateControl: <?=CUtil::PhpToJSObject($arResult['DUPLICATE_CONTROL'])?>,
						initialMode: "<?=CUtil::JSEscape($arResult['INITIAL_MODE'])?>",
						enableModeToggle: <?=$arResult['ENABLE_MODE_TOGGLE'] ? 'true' : 'false'?>,
						readOnly: <?=$arResult['READ_ONLY'] ? 'true' : 'false'?>,
						enableAjaxForm: <?=$arResult['ENABLE_AJAX_FORM'] ? 'true' : 'false'?>,
						enableSectionEdit: <?=$arResult['ENABLE_SECTION_EDIT'] ? 'true' : 'false'?>,
						enableSectionCreation: <?=$arResult['ENABLE_SECTION_CREATION'] ? 'true' : 'false'?>,
						enableSettingsForAll: <?=$arResult['ENABLE_SETTINGS_FOR_ALL'] ? 'true' : 'false'?>,
						containerId: "<?=CUtil::JSEscape($containerID)?>",
						buttonContainerId: "<?=CUtil::JSEscape($buttonContainerID)?>",
						createSectionButtonId: "<?=CUtil::JSEscape($createSectionButtonID)?>",
						htmlEditorConfig: <?=CUtil::PhpToJSObject($htmlEditorConfig)?>,
						serviceUrl: "<?=CUtil::JSEscape($arResult['SERVICE_URL'])?>",
						externalContextId: "<?=CUtil::JSEscape($arResult['EXTERNAL_CONTEXT_ID'])?>",
						contextId: "<?=CUtil::JSEscape($arResult['CONTEXT_ID'])?>",
						context: <?=CUtil::PhpToJSObject($arResult['CONTEXT'])?>,
						contactCreateUrl: "<?=CUtil::JSEscape($arResult['PATH_TO_CONTACT_CREATE'])?>",
						contactRequisiteSelectUrl: "<?=CUtil::JSEscape($arResult['PATH_TO_CONTACT_REQUISITE_SELECT'])?>",
						companyCreateUrl: "<?=CUtil::JSEscape($arResult['PATH_TO_COMPANY_CREATE'])?>",
						companyRequisiteSelectUrl: "<?=CUtil::JSEscape($arResult['PATH_TO_COMPANY_REQUISITE_SELECT'])?>",
						requisiteEditUrl: "<?=CUtil::JSEscape($arResult['PATH_TO_REQUISITE_EDIT'])?>"
					}
				)
			);
		}
	);
</script>
