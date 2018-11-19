<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Thurly\Main;
use Thurly\Main\Localization\Loc;
use Thurly\Crm\Format;

if(!Main\Loader::includeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

Loc::loadMessages(__FILE__);

class CCrmLeadDetailsComponent extends CThurlyComponent
{
	/** @var string */
	protected $guid = '';
	/** @var int */
	private $userID = 0;
	/** @var  CCrmPerms|null */
	private $userPermissions = null;
	/** @var CCrmUserType|null  */
	private $userType = null;
	/** @var array|null */
	private $userFields = null;
	/** @var array|null */
	private $userFieldInfos = null;
	/** @var \Thurly\Main\UserField\Dispatcher|null */
	private $userFieldDispatcher = null;
	/** @var int */
	private $entityID = 0;
	/** @var array|null */
	private $entityData = null;
	/** @var array|null */
	private $statuses = null;
	/** @var array|null */
	private $multiFieldInfos = null;
	/** @var array|null */
	private $multiFieldValueTypeInfos = null;
	/** @var bool */
	private $isEditMode = false;
	/** @var bool */
	private $isCopyMode = false;
	/** @var bool */
	private $enableDupControl = false;
	/** @var array|null */
	private $defaultFieldValues = null;

	public function __construct($component = null)
	{
		/** @global \CUserTypeManager $USER_FIELD_MANAGER */
		global $USER_FIELD_MANAGER;

		parent::__construct($component);

		$this->userID = CCrmSecurityHelper::GetCurrentUserID();
		$this->userPermissions = CCrmPerms::GetCurrentUserPermissions();
		$this->userType = new \CCrmUserType($USER_FIELD_MANAGER, \CCrmLead::GetUserFieldEntityID());
		$this->userFieldDispatcher = \Thurly\Main\UserField\Dispatcher::instance();

		$this->multiFieldInfos = CCrmFieldMulti::GetEntityTypeInfos();
		$this->multiFieldValueTypeInfos = CCrmFieldMulti::GetEntityTypes();
	}
	public function executeComponent()
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		//region Params
		$this->arResult['ENTITY_ID'] = isset($this->arParams['~ENTITY_ID']) ? (int)$this->arParams['~ENTITY_ID'] : 0;
		$extras = isset($this->arParams['~EXTRAS']) && is_array($this->arParams['~EXTRAS'])
			? $this->arParams['~EXTRAS'] : array();

		$this->arResult['PATH_TO_USER_PROFILE'] = $this->arParams['PATH_TO_USER_PROFILE'] =
			CrmCheckPath('PATH_TO_USER_PROFILE', $this->arParams['PATH_TO_USER_PROFILE'], '/company/personal/user/#user_id#/');

		$this->arResult['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE'])
			? CSite::GetNameFormat(false)
			: str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams['NAME_TEMPLATE']);

		$this->arResult['PATH_TO_LEAD_SHOW'] = CrmCheckPath(
			'PATH_TO_LEAD_SHOW',
			$this->arParams['PATH_TO_LEAD_SHOW'],
			$APPLICATION->GetCurPage().'?lead_id=#lead_id#&show'
		);
		$this->arResult['PATH_TO_LEAD_EDIT'] = CrmCheckPath(
			'PATH_TO_LEAD_EDIT',
			$this->arParams['PATH_TO_LEAD_EDIT'],
			$APPLICATION->GetCurPage().'?lead_id=#lead_id#&edit'
		);

		$this->arResult['PATH_TO_PRODUCT_EDIT'] = CrmCheckPath(
			'PATH_TO_PRODUCT_EDIT',
			$this->arParams['PATH_TO_PRODUCT_EDIT'],
			$APPLICATION->GetCurPage().'?product_id=#product_id#&edit'
		);
		$this->arResult['PATH_TO_PRODUCT_SHOW'] = CrmCheckPath(
			'PATH_TO_PRODUCT_SHOW',
			$this->arParams['PATH_TO_PRODUCT_SHOW'],
			$APPLICATION->GetCurPage().'?product_id=#product_id#&show'
		);

		$ufEntityID = \CCrmLead::GetUserFieldEntityID();
		$enableUfCreation = \CCrmAuthorizationHelper::CheckConfigurationUpdatePermission();
		$this->arResult['ENABLE_USER_FIELD_CREATION'] = $enableUfCreation;
		$this->arResult['USER_FIELD_ENTITY_ID'] = $ufEntityID;
		$this->arResult['USER_FIELD_CREATE_PAGE_URL'] = CCrmOwnerType::GetUserFieldEditUrl($ufEntityID, 0);
		$this->arResult['USER_FIELD_CREATE_SIGNATURE'] = $enableUfCreation
			? $this->userFieldDispatcher->getCreateSignature(array('ENTITY_ID' => $ufEntityID))
			: '';
		$this->arResult['ENABLE_TASK'] = IsModuleInstalled('tasks');
		$this->arResult['ACTION_URI'] = $this->arResult['POST_FORM_URI'] = POST_FORM_ACTION_URI;

		$this->arResult['PRODUCT_DATA_FIELD_NAME'] = 'LEAD_PRODUCT_DATA';
		$this->arResult['PRODUCT_EDITOR_ID'] = 'lead_product_editor';

		$this->arResult['CONTEXT_ID'] = \CCrmOwnerType::LeadName.'_'.$this->arResult['ENTITY_ID'];
		$this->arResult['CONTEXT_PARAMS'] = array(
			'PATH_TO_PRODUCT_SHOW' => $this->arResult['PATH_TO_PRODUCT_SHOW'],
			'PATH_TO_USER_PROFILE' => $this->arResult['PATH_TO_USER_PROFILE'],
			'NAME_TEMPLATE' => $this->arResult['NAME_TEMPLATE']
		);

		$this->arResult['EXTERNAL_CONTEXT_ID'] = $this->request->get('external_context_id');
		if($this->arResult['EXTERNAL_CONTEXT_ID'] === null)
		{
			$this->arResult['EXTERNAL_CONTEXT_ID'] = '';
		}

		$this->arResult['ORIGIN_ID'] = $this->request->get('origin_id');
		if($this->arResult['ORIGIN_ID'] === null)
		{
			$this->arResult['ORIGIN_ID'] = '';
		}

		$this->defaultFieldValues = array();
		$this->tryGetFieldValueFromRequest('phone', $this->defaultFieldValues);
		//endregion

		$this->setEntityID($this->arResult['ENTITY_ID']);

		//region Is Editing or Copying?
		if($this->entityID > 0)
		{
			if(!\CCrmLead::Exists($this->entityID))
			{
				ShowError(GetMessage('CRM_LEAD_NOT_FOUND'));
				return;
			}

			if($this->request->get('copy') !== null)
			{
				$this->isCopyMode = true;
				$this->arResult['CONTEXT_PARAMS']['LEAD_ID'] = $this->entityID;
			}
			else
			{
				$this->isEditMode = true;
			}
		}
		$this->arResult['IS_EDIT_MODE'] = $this->isEditMode;
		$this->arResult['IS_COPY_MODE'] = $this->isCopyMode;
		//endregion

		//region Is Control of Duplicates enabled?
		$this->arResult['DUPLICATE_CONTROL'] = array();
		$this->enableDupControl = $this->arResult['DUPLICATE_CONTROL']['enabled'] =
			!$this->isEditMode && \Thurly\Crm\Integrity\DuplicateControl::isControlEnabledFor(CCrmOwnerType::Lead);

		if($this->enableDupControl)
		{
			$this->arResult['DUPLICATE_CONTROL']['serviceUrl'] = '/thurly/components/thurly/crm.lead.edit/ajax.php?'.thurly_sessid_get();
			$this->arResult['DUPLICATE_CONTROL']['entityTypeName'] = CCrmOwnerType::LeadName;
			$this->arResult['DUPLICATE_CONTROL']['groups'] = array(
				'fullName' => array(
					'groupType' => 'fullName',
					'groupSummaryTitle' => Loc::getMessage('CRM_LEAD_DUP_CTRL_FULL_NAME_SUMMARY_TITLE')
				),
				'email' => array(
					'groupType' => 'communication',
					'communicationType' => 'EMAIL',
					'groupSummaryTitle' => Loc::getMessage('CRM_LEAD_DUP_CTRL_EMAIL_SUMMARY_TITLE')
				),
				'phone' => array(
					'groupType' => 'communication',
					'communicationType' => 'PHONE',
					'groupSummaryTitle' => Loc::getMessage('CRM_LEAD_DUP_CTRL_PHONE_SUMMARY_TITLE')
				),
				'companyTitle' => array(
					'parameterName' => 'COMPANY_TITLE',
					'groupType' => 'single',
					'groupSummaryTitle' => Loc::getMessage('CRM_LEAD_DUP_CTRL_COMPANY_TTL_SUMMARY_TITLE')
				)
			);
		}
		//endregion

		//region Permissions check
		if($this->isCopyMode)
		{
			if(!(\CCrmLead::CheckReadPermission($this->entityID, $this->userPermissions)
				&& \CCrmLead::CheckCreatePermission($this->userPermissions))
			)
			{
				ShowError(GetMessage('CRM_PERMISSION_DENIED'));
				return;
			}
		}
		elseif($this->isEditMode)
		{
			if(\CCrmLead::CheckUpdatePermission($this->entityID, $this->userPermissions))
			{
				$this->arResult['READ_ONLY'] = false;
			}
			elseif(\CCrmLead::CheckReadPermission($this->entityID, $this->userPermissions))
			{
				$this->arResult['READ_ONLY'] = true;
			}
			else
			{
				ShowError(GetMessage('CRM_PERMISSION_DENIED'));
				return;
			}
		}
		else
		{
			if(\CCrmLead::CheckCreatePermission($this->userPermissions))
			{
				$this->arResult['READ_ONLY'] = false;
			}
			else
			{
				ShowError(GetMessage('CRM_PERMISSION_DENIED'));
				return;
			}
		}
		//endregion

		$this->prepareEntityUserFields();
		$this->prepareEntityUserFieldInfos();
		$this->prepareEntityData();
		$this->prepareStatusList();

		//region GUID
		$this->guid = $this->arResult['GUID'] = isset($this->arParams['GUID'])
			? $this->arParams['GUID'] : "lead_{$this->entityID}_details";

		$this->arResult['EDITOR_CONFIG_ID'] = isset($this->arParams['EDITOR_CONFIG_ID'])
			? $this->arParams['EDITOR_CONFIG_ID'] : 'lead_details';
		//endregion

		$progressSemantics = $this->entityData['STATUS_ID']
			? \CCrmLead::GetStatusSemantics($this->entityData['STATUS_ID']) : '';
		$this->arResult['PROGRESS_SEMANTICS'] = $progressSemantics;

		//region Entity Info
		$this->arResult['ENTITY_INFO'] = array(
			'ENTITY_ID' => $this->entityID,
			'ENTITY_TYPE_ID' => CCrmOwnerType::Lead,
			'ENTITY_TYPE_NAME' => CCrmOwnerType::LeadName,
			'TITLE' => isset($this->entityData['TITLE']) ? $this->entityData['TITLE'] : '',
			'SHOW_URL' => CCrmOwnerType::GetEntityShowPath(CCrmOwnerType::Lead, $this->entityID, false),
		);
		//endregion

		//region Page title
		if($this->isCopyMode)
		{
			$APPLICATION->SetTitle(Loc::getMessage('CRM_LEAD_COPY_PAGE_TITLE'));
		}
		elseif(isset($this->entityData['TITLE']))
		{
			$APPLICATION->SetTitle($this->entityData['TITLE']);
		}
		elseif(!$this->isEditMode)
		{
			$APPLICATION->SetTitle(Loc::getMessage('CRM_LEAD_CREATION_PAGE_TITLE'));
		}
		//endregion

		//region Conversion
		$this->arResult['PERMISSION_ENTITY_TYPE'] = 'LEAD';
		CCrmLead::PrepareConversionPermissionFlags($this->entityID, $this->arResult, $this->userPermissions);
		$this->arResult['ENABLE_PROGRESS_CHANGE'] = !$this->arResult['READ_ONLY'] && $progressSemantics !== 'success';
		if($this->arResult['CAN_CONVERT'])
		{
			$config = \Thurly\Crm\Conversion\LeadConversionConfig::load();
			if($config === null)
			{
				$config = \Thurly\Crm\Conversion\LeadConversionConfig::getDefault();
			}
			$this->arResult['CONVERSION_CONFIG'] = $config;

			$schemeID = \Thurly\Crm\Conversion\LeadConversionManager::create(
				array(
					'IS_RC' => isset($this->entityData['IS_RETURN_CUSTOMER']) && $this->entityData['IS_RETURN_CUSTOMER'] == 'Y'
				)
			)->getCurrentSchemeID();

			$this->arResult['CONVERSION_SCHEME'] = array(
				'ORIGIN_URL' => $APPLICATION->GetCurPage(),
				'SCHEME_ID' => $schemeID,
				'SCHEME_NAME' => \Thurly\Crm\Conversion\LeadConversionScheme::resolveName($schemeID),
				'SCHEME_DESCRIPTION' => \Thurly\Crm\Conversion\LeadConversionScheme::getDescription($schemeID),
				'SCHEME_CAPTION' => GetMessage('CRM_LEAD_CREATE_ON_BASIS')
			);
		}
		//endregion

		//region Fields
		$this->prepareFieldInfos();
		//endregion

		//region Config
		$multiFieldConfigElements = array();
		foreach(array_keys($this->multiFieldInfos) as $fieldName)
		{
			$multiFieldConfigElements[] = array('name' => $fieldName);
		}

		$userFieldConfigElements = array();
		foreach(array_keys($this->userFieldInfos) as $fieldName)
		{
			$userFieldConfigElements[] = array('name' => $fieldName);
		}
		$this->arResult['ENTITY_CONFIG'] = array(
			array(
				'name' => 'main',
				'title' => Loc::getMessage('CRM_LEAD_SECTION_MAIN'),
				'type' => 'section',
				'elements' =>
					array_merge(
						array(
							array('name' => 'TITLE'),
							array('name' => 'STATUS_ID'),
							array('name' => 'OPPORTUNITY_WITH_CURRENCY'),
							array('name' => 'HONORIFIC'),
							array('name' => 'LAST_NAME'),
							array('name' => 'NAME'),
							array('name' => 'SECOND_NAME'),
							array('name' => 'BIRTHDATE'),
							array('name' => 'POST'),
							array('name' => 'COMPANY_TITLE')
						),
						$multiFieldConfigElements
					),
			),
			array(
				'name' => 'additional',
				'title' => Loc::getMessage('CRM_LEAD_SECTION_ADDITIONAL'),
				'type' => 'section',
				'elements' =>
					array_merge(
						array(
							array('name' => 'SOURCE_ID'),
							array('name' => 'SOURCE_DESCRIPTION'),
							array('name' => 'OPENED'),
							array('name' => 'ASSIGNED_BY_ID'),
							array('name' => 'COMMENTS'),
							array('name' => 'ADDRESS')
						),
						$userFieldConfigElements
					)
			),
			array(
				'name' => 'products',
				'title' => Loc::getMessage('CRM_LEAD_SECTION_PRODUCTS'),
				'type' => 'section',
				'elements' => array(
					array('name' => 'PRODUCT_ROW_SUMMARY')
				)
			)
		);
		//endregion

		//region Controllers
		$this->arResult['ENTITY_CONTROLLERS'] = array(
			array(
				'name' => 'PRODUCT_ROW_PROXY',
				'type' => 'product_row_proxy',
				'config' => array('editorId' => $this->arResult['PRODUCT_EDITOR_ID'])
			),
		);
		//endregion

		//region Tabs
		$this->arResult['TABS'] = array();

		$currencyID = CCrmCurrency::GetBaseCurrencyID();
		if(isset($this->entityData['CURRENCY_ID']) && $this->entityData['CURRENCY_ID'] !== '')
		{
			$currencyID = $this->entityData['CURRENCY_ID'];
		}

		$bTaxMode = \CCrmTax::isTaxMode();

		$companyID = isset($this->entityData['COMPANY_ID']) ? (int)$this->entityData['COMPANY_ID'] : 0;
		// Determine person type
		$personTypes = CCrmPaySystem::getPersonTypeIDs();
		$personTypeID = 0;
		if (isset($arPersonTypes['COMPANY']) && isset($arPersonTypes['CONTACT']))
		{
			$personTypeID = $companyID > 0 ? $personTypes['COMPANY'] : $personTypes['CONTACT'];
		}

		ob_start();
		$APPLICATION->IncludeComponent('thurly:crm.product_row.list',
			'',
			array(
				'ID' => $this->arResult['PRODUCT_EDITOR_ID'],
				'PREFIX' => $this->arResult['PRODUCT_EDITOR_ID'],
				'FORM_ID' => '',
				'OWNER_ID' => $this->entityID,
				'OWNER_TYPE' => 'L',
				'PERMISSION_TYPE' => $this->arResult['READ_ONLY'] ? 'READ' : 'WRITE',
				'PERMISSION_ENTITY_TYPE' => $this->arResult['PERMISSION_ENTITY_TYPE'],
				'PERSON_TYPE_ID' => $personTypeID,
				'CURRENCY_ID' => $currencyID,
				'LOCATION_ID' => $bTaxMode && isset($this->entityData['LOCATION_ID']) ? $this->entityData['LOCATION_ID'] : '',
				'CLIENT_SELECTOR_ID' => '', //TODO: Add Client Selector
				//'PRODUCT_ROWS' =>  null,
				'HIDE_MODE_BUTTON' => !$this->isEditMode ? 'Y' : 'N',
				'TOTAL_SUM' => isset($this->entityData['OPPORTUNITY']) ? $this->entityData['OPPORTUNITY'] : null,
				'TOTAL_TAX' => isset($this->entityData['TAX_VALUE']) ? $this->entityData['TAX_VALUE'] : null,
				'PRODUCT_DATA_FIELD_NAME' => $this->arResult['PRODUCT_DATA_FIELD_NAME'],
				'PATH_TO_PRODUCT_EDIT' => $this->arResult['PATH_TO_PRODUCT_EDIT'],
				'PATH_TO_PRODUCT_SHOW' => $this->arResult['PATH_TO_PRODUCT_SHOW'],
				'INIT_LAYOUT' => 'N',
				'INIT_EDITABLE' => $this->arResult['READ_ONLY'] ? 'N' : 'Y',
				'ENABLE_MODE_CHANGE' => 'N',
				'ENABLE_SUBMIT_WITHOUT_LAYOUT' => 'N'
			),
			false,
			array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT'=>'Y')
		);
		$html = ob_get_contents();
		ob_end_clean();

		$this->arResult['TABS'][] = array(
			'id' => 'tab_products',
			'name' => Loc::getMessage('CRM_LEAD_TAB_PRODUCTS'),
			'html' => $html
		);

		if($this->entityID > 0)
		{
			$this->arResult['TABS'][] = array(
				'id' => 'tab_quote',
				'name' => Loc::getMessage('CRM_LEAD_TAB_QUOTE'),
				'loader' => array(
					'serviceUrl' => '/thurly/components/thurly/crm.quote.list/lazyload.ajax.php?&site='.SITE_ID.'&'.thurly_sessid_get(),
					'componentData' => array(
						'template' => '',
						'params' => array(
							'QUOTE_COUNT' => '20',
							'PATH_TO_QUOTE_SHOW' => $this->arResult['PATH_TO_QUOTE_SHOW'],
							'PATH_TO_QUOTE_EDIT' => $this->arResult['PATH_TO_QUOTE_EDIT'],
							'INTERNAL_FILTER' => array('LEAD_ID' => $this->entityID),
							'INTERNAL_CONTEXT' => array('LEAD_ID' => $this->entityID),
							'GRID_ID_SUFFIX' => 'LEAD_DETAILS',
							'TAB_ID' => 'tab_quote',
							'NAME_TEMPLATE' => $this->arResult['NAME_TEMPLATE'],
							'ENABLE_TOOLBAR' => true,
							'PRESERVE_HISTORY' => true,
							'ADD_EVENT_NAME' => 'CrmCreateQuoteFromLead'
						)
					)
				)
			);
			if (\Thurly\Crm\Automation\Factory::isAutomationAvailable(CCrmOwnerType::Lead))
			{
				Thurly\Main\Page\Asset::getInstance()->addCss('/thurly/components/thurly/crm.automation/templates/.default/style.css');
				$this->arResult['TABS'][] = array(
					'id' => 'tab_automation',
					'name' => Loc::getMessage('CRM_LEAD_TAB_AUTOMATION'),
					'loader' => array(
						'serviceUrl' => '/thurly/components/thurly/crm.automation/lazyload.ajax.php?&site='.SITE_ID.'&'.thurly_sessid_get(),
						'componentData' => array(
							'template' => '',
							'params' => array(
								'ENTITY_TYPE_ID' => \CCrmOwnerType::Lead,
								'ENTITY_ID' => $this->entityID,
								'back_url' => \CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::Lead, $this->entityID)
							)
						)
					)
				);

			}
			if (CModule::IncludeModule('bizproc') && CBPRuntime::isFeatureEnabled())
			{
				$this->arResult['TABS'][] = array(
					'id' => 'tab_bizproc',
					'name' => Loc::getMessage('CRM_LEAD_TAB_BIZPROC'),
					'loader' => array(
						'serviceUrl' => '/thurly/components/thurly/bizproc.document/lazyload.ajax.php?&site='.SITE_ID.'&'.thurly_sessid_get(),
						'componentData' => array(
							'template' => 'frame',
							'params' => array(
								'MODULE_ID' => 'crm',
								'ENTITY' => 'CCrmDocumentLead',
								'DOCUMENT_TYPE' => 'LEAD',
								'DOCUMENT_ID' => 'LEAD_'.$this->entityID
							)
						)
					)
				);
				$this->arResult['BIZPROC_STARTER_DATA'] = array(
					'templates' => CBPDocument::getTemplatesForStart(
						$this->userID,
						array('crm', 'CCrmDocumentLead', 'LEAD'),
						array('crm', 'CCrmDocumentLead', 'LEAD_'.$this->entityID)
					),
					'moduleId' => 'crm',
					'entity' => 'CCrmDocumentLead',
					'documentType' => 'LEAD',
					'documentId' => 'LEAD_'.$this->entityID
				);
			}
			$this->arResult['TABS'][] = array(
				'id' => 'tab_tree',
				'name' => Loc::getMessage('CRM_LEAD_TAB_TREE'),
				'loader' => array(
					'serviceUrl' => '/thurly/components/thurly/crm.entity.tree/lazyload.ajax.php?&site='.SITE_ID.'&'.thurly_sessid_get(),
					'componentData' => array(
						'template' => '.default',
						'params' => array(
							'ENTITY_ID' => $this->entityID,
							'ENTITY_TYPE_NAME' => CCrmOwnerType::LeadName,
						)
					)
				)
			);
			$this->arResult['TABS'][] = array(
				'id' => 'tab_event',
				'name' => Loc::getMessage('CRM_LEAD_TAB_EVENT'),
				'loader' => array(
					'serviceUrl' => '/thurly/components/thurly/crm.event.view/lazyload.ajax.php?&site'.SITE_ID.'&'.thurly_sessid_get(),
					'componentData' => array(
						'template' => '',
						'contextId' => "LEAD_{$this->entityID}_EVENT",
						'params' => array(
							'AJAX_OPTION_ADDITIONAL' => "LEAD_{$this->entityID}_EVENT",
							'ENTITY_TYPE' => 'LEAD',
							'ENTITY_ID' => $this->entityID,
							'PATH_TO_USER_PROFILE' => $this->arResult['PATH_TO_USER_PROFILE'],
							'TAB_ID' => 'tab_event',
							'INTERNAL' => 'Y',
							'SHOW_INTERNAL_FILTER' => 'Y',
							'PRESERVE_HISTORY' => true,
							'NAME_TEMPLATE' => $this->arResult['NAME_TEMPLATE']
						)
					)
				)
			);
			if (CModule::IncludeModule('lists'))
			{
				$listIblock = CLists::getIblockAttachedCrm(CCrmOwnerType::LeadName);
				foreach($listIblock as $iblockId => $iblockName)
				{
					$this->arResult['TABS'][] = array(
						'id' => 'tab_lists_'.$iblockId,
						'name' => $iblockName,
						'loader' => array(
							'serviceUrl' => '/thurly/components/thurly/lists.element.attached.crm/lazyload.ajax.php?&site='.SITE_ID.'&'.thurly_sessid_get().'',
							'componentData' => array(
								'template' => '',
								'params' => array(
									'ENTITY_ID' => $this->entityID,
									'ENTITY_TYPE' => CCrmOwnerType::Lead,
									'TAB_ID' => 'tab_lists_'.$iblockId,
									'IBLOCK_ID' => $iblockId
								)
							)
						)
					);
				}
			}
		}
		else
		{
			$this->arResult['TABS'][] = array(
				'id' => 'tab_quote',
				'name' => Loc::getMessage('CRM_LEAD_TAB_QUOTE'),
				'enabled' => false
			);
			if (\Thurly\Crm\Automation\Factory::isAutomationAvailable(CCrmOwnerType::Lead))
			{
				$this->arResult['TABS'][] = array(
					'id' => 'tab_automation',
					'name' => Loc::getMessage('CRM_LEAD_TAB_AUTOMATION'),
					'enabled' => false
				);
			}
			if (CModule::IncludeModule('bizproc') && CBPRuntime::isFeatureEnabled())
			{
				$this->arResult['TABS'][] = array(
					'id' => 'tab_bizproc',
					'name' => Loc::getMessage('CRM_LEAD_TAB_BIZPROC'),
					'enabled' => false
				);
			}
			$this->arResult['TABS'][] = array(
				'id' => 'tab_event',
				'name' => Loc::getMessage('CRM_LEAD_TAB_EVENT'),
				'enabled' => false
			);
			if (CModule::IncludeModule('lists'))
			{
				$listIblock = CLists::getIblockAttachedCrm(CCrmOwnerType::LeadName);
				foreach($listIblock as $iblockId => $iblockName)
				{
					$this->arResult['TABS'][] = array(
						'id' => 'tab_lists_'.$iblockId,
						'name' => $iblockName,
						'enabled' => false
					);
				}
			}
		}
		//endregion

		//region Wait Target Dates
		$this->arResult['WAIT_TARGET_DATES'] = array();
		$userFields = $this->userType->GetFields();
		foreach($userFields as $userField)
		{
			if($userField['USER_TYPE_ID'] === 'date')
			{
				$this->arResult['WAIT_TARGET_DATES'][] = array(
					'name' => $userField['FIELD_NAME'],
					'caption' => isset($userField['EDIT_FORM_LABEL'])
						? $userField['EDIT_FORM_LABEL'] : $userField['FIELD_NAME']
				);
			}
		}
		//endregion

		//region VIEW EVENT
		if($this->entityID > 0 && \Thurly\Crm\Settings\HistorySettings::getCurrent()->isViewEventEnabled())
		{
			CCrmEvent::RegisterViewEvent(CCrmOwnerType::Lead, $this->entityID, $this->userID);
		}
		//endregion

		$this->includeComponentTemplate();
	}
	public function initializeParams(array $params)
	{
		foreach($params as $k => $v)
		{
			if(!is_string($v))
			{
				continue;
			}

			if($k === 'PATH_TO_PRODUCT_SHOW')
			{
				$this->arResult['PATH_TO_PRODUCT_SHOW'] = $this->arParams['PATH_TO_PRODUCT_SHOW'] = $v;
			}
			elseif($k === 'PATH_TO_USER_PROFILE')
			{
				$this->arResult['PATH_TO_USER_PROFILE'] = $this->arParams['PATH_TO_USER_PROFILE'] = $v;
			}
			elseif($k === 'NAME_TEMPLATE')
			{
				$this->arResult['NAME_TEMPLATE'] = $this->arParams['NAME_TEMPLATE'] = $v;
			}
		}
	}
	public function getEntityID()
	{
		return $this->entityID;
	}
	public function setEntityID($entityID)
	{
		$this->entityID = $entityID;

		$this->userFields = null;
		$this->prepareEntityUserFields();

		$this->userFieldInfos = null;
		$this->prepareEntityUserFieldInfos();
	}
	public function prepareFieldInfos()
	{
		if(isset($this->arResult['ENTITY_FIELDS']))
		{
			return $this->arResult['ENTITY_FIELDS'];
		}

		$prohibitedStatusIDs = array();
		$allStatuses = CCrmStatus::GetStatusList('STATUS');
		foreach(array_keys($allStatuses) as $statusID)
		{
			if($this->arResult['READ_ONLY'])
			{
				$prohibitedStatusIDs[] = $statusID;
			}
			else
			{
				$permissionType = $this->isEditMode
					? \CCrmLead::GetStatusUpdatePermissionType($statusID, $this->userPermissions)
					: \CCrmLead::GetStatusCreatePermissionType($statusID, $this->userPermissions);

				if($permissionType == BX_CRM_PERM_NONE)
				{
					$prohibitedStatusIDs[] = $statusID;
				}
			}
		}

		$this->arResult['ENTITY_FIELDS'] = array(
			array(
				'name' => 'ID',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_ID'),
				'type' => 'text',
				'editable' => false
			),
			array(
				'name' => 'TITLE',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_TITLE'),
				'type' => 'text',
				'isHeading' => true,
				'visibilityPolicy' => 'edit',
				'editable' => true
			),
			array(
				'name' => 'STATUS_ID',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_STATUS_ID'),
				'type' => 'list',
				'editable' => !(isset($this->entityData['STATUS_ID']) && $this->entityData['STATUS_ID'] === 'CONVERTED'),
				'data' => array(
					'items'=> \CCrmInstantEditorHelper::PrepareListOptions(
						$allStatuses,
						array('EXCLUDE_FROM_EDIT' => array_merge($prohibitedStatusIDs, array('CONVERTED')))
					)
				)
			),
			array(
				'name' => 'STATUS_DESCRIPTION',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_STATUS_DESCRIPTION'),
				'type' => 'text',
				'data' => array('lineCount' => 6),
				'editable' => true
			),
			array(
				'name' => 'OPPORTUNITY_WITH_CURRENCY',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_OPPORTUNITY_WITH_CURRENCY'),
				'type' => 'money',
				'editable' => true,
				'data' => array(
					'affectedFields' => array('CURRENCY_ID', 'OPPORTUNITY'),
					'currency' => array(
						'name' => 'CURRENCY_ID',
						'items'=> \CCrmInstantEditorHelper::PrepareListOptions(CCrmCurrencyHelper::PrepareListItems())
					),
					'amount' => 'OPPORTUNITY',
					'formatted' => 'FORMATTED_OPPORTUNITY',
					'formattedWithCurrency' => 'FORMATTED_OPPORTUNITY_WITH_CURRENCY'
				)
			),
			array(
				'name' => 'SOURCE_ID',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_SOURCE_ID'),
				'type' => 'list',
				'editable' => true,
				'data' => array('items'=> \CCrmInstantEditorHelper::PrepareListOptions(CCrmStatus::GetStatusList('SOURCE')))
			),
			array(
				'name' => 'SOURCE_DESCRIPTION',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_SOURCE_DESCRIPTION'),
				'type' => 'text',
				'data' => array('lineCount' => 6),
				'editable' => true
			),
			array(
				'name' => 'ASSIGNED_BY_ID',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_ASSIGNED_BY_ID'),
				'type' => 'user',
				'editable' => true,
				'data' => array(
					'enableEditInView' => true,
					'formated' => 'ASSIGNED_BY_FORMATTED_NAME',
					'position' => 'ASSIGNED_BY_WORK_POSITION',
					'photoUrl' => 'ASSIGNED_BY_PHOTO_URL',
					'showUrl' => 'PATH_TO_ASSIGNED_BY_USER',
					'pathToProfile' => $this->arResult['PATH_TO_USER_PROFILE']

				)
			),
			array(
				'name' => 'OPENED',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_OPENED'),
				'type' => 'boolean',
				'editable' => true
			),
			array(
				'name' => 'HONORIFIC',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_HONORIFIC'),
				'type' => 'list',
				'editable' => true,
				'data' => array(
					'items'=> \CCrmInstantEditorHelper::PrepareListOptions(
						CCrmStatus::GetStatusList('HONORIFIC'),
						array('NOT_SELECTED' => Loc::getMessage('CRM_LEAD_HONORIFIC_NOT_SELECTED'))
					)
				)
			),
			array(
				'name' => 'LAST_NAME',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_LAST_NAME'),
				'type' => 'text',
				'editable' => true,
				'data' => array('duplicateControl' => array('groupId' => 'fullName', 'field' => array('id' => 'LAST_NAME')))
			),
			array(
				'name' => 'NAME',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_NAME'),
				'type' => 'text',
				'editable' => true,
				'data' => array('duplicateControl' => array('groupId' => 'fullName', 'field' => array('id' => 'NAME')))
			),
			array(
				'name' => 'SECOND_NAME',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_SECOND_NAME'),
				'type' => 'text',
				'editable' => true,
				'data' => array('duplicateControl' => array('groupId' => 'fullName', 'field' => array('id' => 'SECOND_NAME')))
			),
			array(
				'name' => 'BIRTHDATE',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_BIRTHDATE'),
				'type' => 'datetime',
				'editable' => true,
				'data' =>  array('enableTime' => false)
			),
			array(
				'name' => 'POST',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_POST'),
				'type' => 'text',
				'editable' => true
			),
			array(
				'name' => 'COMPANY_TITLE',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_COMPANY_TITLE'),
				'type' => 'text',
				'editable' => true,
				'data' => array('duplicateControl' => array('groupId' => 'companyTitle', 'field' => array('id' => 'COMPANY_TITLE')))
			),
			array(
				'name' => 'ADDRESS',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_ADDRESS'),
				'type' => 'address',
				'editable' => true,
				'data' => array(
					'fields' => array(
						'ADDRESS' => array('NAME' => 'ADDRESS', 'IS_MULTILINE' => true),
						'ADDRESS_2' => array('NAME' => 'ADDRESS_2'),
						'CITY' => array('NAME' => 'ADDRESS_CITY'),
						'REGION' => array('NAME' => 'ADDRESS_REGION'),
						'PROVINCE' => array('NAME' => 'ADDRESS_PROVINCE'),
						'POSTAL_CODE' => array('NAME' => 'ADDRESS_POSTAL_CODE'),
						'COUNTRY' => array('NAME' => 'ADDRESS_COUNTRY')
					),
					'labels' => \Thurly\Crm\EntityAddress::getLabels(),
					'view' => 'ADDRESS_HTML'
				)
			),
			array(
				'name' => 'COMMENTS',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_COMMENTS'),
				'type' => 'html',
				'editable' => true
			),
			array(
				'name' => 'PRODUCT_ROW_SUMMARY',
				'title' => Loc::getMessage('CRM_LEAD_FIELD_PRODUCTS'),
				'type' => 'product_row_summary',
				'editable' => false,
				'transferable' => false
			)
		);

		foreach($this->multiFieldInfos as $typeName => $typeInfo)
		{
			$valueTypes = isset($this->multiFieldValueTypeInfos[$typeName])
				? $this->multiFieldValueTypeInfos[$typeName] : array();

			$valueTypeItems = array();
			foreach($valueTypes as $valueTypeId => $valueTypeInfo)
			{
				$valueTypeItems[] = array(
					'NAME' => isset($valueTypeInfo['SHORT']) ? $valueTypeInfo['SHORT'] : $valueTypeInfo['FULL'],
					'VALUE' => $valueTypeId
				);
			}

			$data = array('type' => $typeName, 'items'=> $valueTypeItems);
			if($typeName === 'PHONE')
			{
				$data['duplicateControl'] = array('groupId' => 'phone');
			}
			else if($typeName === 'EMAIL')
			{
				$data['duplicateControl'] = array('groupId' => 'email');
			}

			$this->arResult['ENTITY_FIELDS'][] = array(
				'name' => $typeName,
				'title' => $typeInfo['NAME'],
				'type' => 'multifield',
				'editable' => true,
				'data' => $data
			);
		}
		$this->arResult['ENTITY_FIELDS'] = array_merge(
			$this->arResult['ENTITY_FIELDS'],
			array_values($this->userFieldInfos)
		);

		return $this->arResult['ENTITY_FIELDS'];
	}
	public function prepareEntityUserFields()
	{
		if($this->userFields === null)
		{
			$this->userFields = $this->userType->GetEntityFields($this->entityID);
		}
		return $this->userFields;
	}
	public function prepareEntityUserFieldInfos()
	{
		if($this->userFieldInfos !== null)
		{
			return $this->userFieldInfos;
		}

		$this->userFieldInfos = array();
		$userFields = $this->prepareEntityUserFields();
		$enumerationFields = array();
		foreach($userFields as $userField)
		{
			$fieldName = $userField['FIELD_NAME'];
			$fieldInfo = array(
				'USER_TYPE_ID' => $userField['USER_TYPE_ID'],
				'ENTITY_ID' => \CCrmLead::GetUserFieldEntityID(),
				'ENTITY_VALUE_ID' => $this->entityID,
				'FIELD' => $fieldName,
				'MULTIPLE' => $userField['MULTIPLE'],
				'MANDATORY' => $userField['MANDATORY'],
				'SETTINGS' => isset($userField['SETTINGS']) ? $userField['SETTINGS'] : null
				//'CONTEXT' => $this->guid
			);

			if($userField['USER_TYPE_ID'] === 'enumeration')
			{
				$enumerationFields[$fieldName] = $userField;
			}
			elseif($userField['USER_TYPE_ID'] === 'file')
			{
				$fieldInfo['ADDITIONAL'] = array(
					'URL_TEMPLATE' => \CComponentEngine::MakePathFromTemplate(
						'/thurly/components/thurly/crm.lead.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#',
						array(
							'owner_id' => $this->entityID,
							'field_name' => $fieldName
						)
					)
				);
			}

			$this->userFieldInfos[$fieldName] = array(
				'name' => $fieldName,
				'title' => isset($userField['EDIT_FORM_LABEL']) ? $userField['EDIT_FORM_LABEL'] : $fieldName,
				'type' => 'userField',
				'data' => array('fieldInfo' => $fieldInfo)
			);

			if(isset($userField['MANDATORY']) && $userField['MANDATORY'] === 'Y')
			{
				$this->userFieldInfos[$fieldName]['required'] = true;
			}
		}

		if(!empty($enumerationFields))
		{
			$enumInfos = \CCrmUserType::PrepareEnumerationInfos($enumerationFields);
			foreach($enumInfos as $fieldName => $enums)
			{
				if(isset($this->userFieldInfos[$fieldName])
					&& isset($this->userFieldInfos[$fieldName]['data'])
					&& isset($this->userFieldInfos[$fieldName]['data']['fieldInfo'])
				)
				{
					$this->userFieldInfos[$fieldName]['data']['fieldInfo']['ENUM'] = $enums;
				}
			}
		}

		return $this->userFieldInfos;
	}
	public function prepareEntityData()
	{
		if($this->entityData)
		{
			return $this->entityData;
		}

		if($this->entityID <= 0)
		{
			$this->entityData = array();
			//leave OPPORTUNITY unassigned
			//$this->entityData['OPPORTUNITY'] = 0.0;
			$this->entityData['CURRENCY_ID'] = \CCrmCurrency::GetBaseCurrencyID();
			$this->entityData['OPENED'] = \Thurly\Crm\Settings\LeadSettings::getCurrent()->getOpenedFlag() ? 'Y' : 'N';
			//$this->entityData['CLOSED'] = 'N';

			//region Default Responsible
			if($this->userID > 0)
			{
				$this->entityData['ASSIGNED_BY_ID'] = $this->userID;
			}
			//endregion

			//region Default Status ID
			$statusList = $this->prepareStatusList();
			if(!empty($statusList))
			{
				$requestStatusId = $this->request->get('status_id');
				if (isset($statusList[$requestStatusId]))
				{
					$this->entityData['STATUS_ID'] = $requestStatusId;
				}
				else
				{
					$this->entityData['STATUS_ID'] = current(array_keys($statusList));
				}
			}
			//endregion

			if(isset($this->defaultFieldValues['phone']))
			{
				$phone = trim($this->defaultFieldValues['phone']);
				if($phone !== '')
				{
					$this->entityData['FM']['PHONE'] = array(
						'n0' => array('VALUE' => $phone, 'VALUE_TYPE' => 'WORK'));
				}
			}
		}
		else
		{
			$dbResult = \CCrmLead::GetListEx(
				array(),
				array('=ID' => $this->entityID, 'CHECK_PERMISSIONS' => 'N')
			);

			if(is_object($dbResult))
			{
				$this->entityData = $dbResult->Fetch();
			}

			if(!is_array($this->entityData))
			{
				$this->entityData = array();
			}

			$this->entityData['FORMATTED_NAME'] =
				\CUser::FormatName(
					$this->arResult['NAME_TEMPLATE'],
					array(
						'NAME' => isset($this->entityData['NAME']) ? $this->entityData['NAME'] : '',
						'LAST_NAME' => isset($this->entityData['LAST_NAME']) ? $this->entityData['LAST_NAME'] : '',
						'SECOND_NAME' => $this->entityData['SECOND_NAME'] ? $this->entityData['SECOND_NAME'] : ''
					),
					false,
					false
				);

			if(!isset($this->entityData['OPPORTUNITY']))
			{
				$this->entityData['OPPORTUNITY'] = 0.0;
			}

			if(!isset($this->entityData['CURRENCY_ID']) || $this->entityData['CURRENCY_ID'] === '')
			{
				$this->entityData['CURRENCY_ID'] = \CCrmCurrency::GetBaseCurrencyID();
			}

			//region Default Responsible and Status ID for copy mode
			if($this->isCopyMode)
			{
				if($this->userID > 0)
				{
					$this->entityData['ASSIGNED_BY_ID'] = $this->userID;
				}

				$statusList = $this->prepareStatusList();
				if(!empty($statusList))
				{
					$this->entityData['STATUS_ID'] = current(array_keys($statusList));
				}
			}
			//endregion
		}

		//region Responsible
		if(isset($this->entityData['ASSIGNED_BY_ID']) && $this->entityData['ASSIGNED_BY_ID'] > 0)
		{
			$dbUsers = \CUser::GetList(
				$by = 'ID',
				$order = 'ASC',
				array('ID' => $this->entityData['ASSIGNED_BY_ID']),
				array(
					'FIELDS' => array(
						'ID',  'LOGIN', 'PERSONAL_PHOTO',
						'NAME', 'SECOND_NAME', 'LAST_NAME'
					)
				)
			);
			$user = is_object($dbUsers) ? $dbUsers->Fetch() : null;
			if(is_array($user))
			{
				$this->entityData['ASSIGNED_BY_LOGIN'] = $user['LOGIN'];
				$this->entityData['ASSIGNED_BY_NAME'] = isset($user['NAME']) ? $user['NAME'] : '';
				$this->entityData['ASSIGNED_BY_SECOND_NAME'] = isset($user['SECOND_NAME']) ? $user['SECOND_NAME'] : '';
				$this->entityData['ASSIGNED_BY_LAST_NAME'] = isset($user['LAST_NAME']) ? $user['LAST_NAME'] : '';
				$this->entityData['ASSIGNED_BY_PERSONAL_PHOTO'] = isset($user['PERSONAL_PHOTO']) ? $user['PERSONAL_PHOTO'] : '';
			}
		}
		//endregion

		//region User Fields
		foreach($this->userFields as $userField)
		{
			$fieldName = $userField['FIELD_NAME'];
			$fieldValue = isset($userField['VALUE']) ? $userField['VALUE'] : '';

			$fieldData = isset($this->userFieldInfos[$fieldName])
				? $this->userFieldInfos[$fieldName] : null;
			if(!is_array($fieldData))
			{
				continue;
			}

			$isEmptyField = true;
			$fieldParams = $fieldData['data']['fieldInfo'];
			if((is_string($fieldValue) && $fieldValue !== '')
				|| (is_array($fieldValue) && !empty($fieldValue))
			)
			{
				$fieldParams['VALUE'] = $fieldValue;
				$isEmptyField = false;
			}

			$fieldSignature = $this->userFieldDispatcher->getSignature($fieldParams);
			if($isEmptyField)
			{
				$this->entityData[$fieldName] = array(
					'SIGNATURE' => $fieldSignature,
					'IS_EMPTY' => true
				);
			}
			else
			{
				$this->entityData[$fieldName] = array(
					'VALUE' => $fieldValue,
					'SIGNATURE' => $fieldSignature,
					'IS_EMPTY' => false
				);
			}
		}
		//endregion
		//region Opportunity & Currency
		$this->entityData['FORMATTED_OPPORTUNITY_WITH_CURRENCY'] = \CCrmCurrency::MoneyToString(
			$this->entityData['OPPORTUNITY'],
			$this->entityData['CURRENCY_ID'],
			''
		);
		$this->entityData['FORMATTED_OPPORTUNITY'] = \CCrmCurrency::MoneyToString(
			$this->entityData['OPPORTUNITY'],
			$this->entityData['CURRENCY_ID'],
			'#'
		);
		//endregion
		//region Responsible
		$assignedByID = isset($this->entityData['ASSIGNED_BY_ID']) ? (int)$this->entityData['ASSIGNED_BY_ID'] : 0;
		if($assignedByID > 0)
		{
			$this->entityData['ASSIGNED_BY_FORMATTED_NAME'] =
				\CUser::FormatName(
					$this->arResult['NAME_TEMPLATE'],
					array(
						'LOGIN' => $this->entityData['ASSIGNED_BY_LOGIN'],
						'NAME' => $this->entityData['ASSIGNED_BY_NAME'],
						'LAST_NAME' => $this->entityData['ASSIGNED_BY_LAST_NAME'],
						'SECOND_NAME' => $this->entityData['ASSIGNED_BY_SECOND_NAME']
					),
					true,
					false
				);

			$assignedByPhotoID = isset($this->entityData['ASSIGNED_BY_PERSONAL_PHOTO'])
				? (int)$this->entityData['ASSIGNED_BY_PERSONAL_PHOTO'] : 0;

			if($assignedByPhotoID > 0)
			{
				$file = new CFile();
				$fileInfo = $file->ResizeImageGet(
					$assignedByPhotoID,
					array('width' => 60, 'height'=> 60),
					BX_RESIZE_IMAGE_EXACT
				);
				if(is_array($fileInfo) && isset($fileInfo['src']))
				{
					$this->entityData['ASSIGNED_BY_PHOTO_URL'] = $fileInfo['src'];
				}
			}

			$this->entityData['PATH_TO_ASSIGNED_BY_USER'] = CComponentEngine::MakePathFromTemplate(
				$this->arResult['PATH_TO_USER_PROFILE'],
				array('user_id' => $assignedByID)
			);
		}
		//endregion
		//region Client Data & Multifield Data
		$multiFildData = array();
		if($this->entityID > 0)
		{
			$multiFieldDbResult = \CCrmFieldMulti::GetList(
				array('ID' => 'asc'),
				array(
					'ENTITY_ID' => CCrmOwnerType::LeadName,
					'ELEMENT_ID' => $this->entityID
				)
			);

			$entityKey = CCrmOwnerType::Lead.'_'.$this->entityID;
			$multiFieldEntityTypes = \CCrmFieldMulti::GetEntityTypes();
			$multiFieldViewClassNames = array(
				'PHONE' => 'crm-entity-phone-number',
				'EMAIL' => 'crm-entity-email',
				'IM' => 'crm-entity-phone-number'

			);

			while($multiField = $multiFieldDbResult->Fetch())
			{
				$typeID = $multiField['TYPE_ID'];
				if(!isset($this->entityData[$typeID]))
				{
					$this->entityData[$typeID] = array();
				}

				$multiFieldID = $multiField['ID'];
				if($this->isCopyMode)
				{
					$multiFieldID = "n0{$multiFieldID}";
				}

				$multiFieldComplexID = $multiField['COMPLEX_ID'];
				$value = $multiField['VALUE'];
				$valueType = $multiField['VALUE_TYPE'];
				$multiFieldEntityType = $multiFieldEntityTypes[$typeID];

				$this->entityData[$typeID][] = array(
					'ID' => $multiFieldID,
					'VALUE' => $value,
					'VALUE_TYPE' => $valueType,
					'VIEW_DATA' => \CCrmViewHelper::PrepareMultiFieldValueItemData(
						$typeID,
						array(
							'VALUE' => $value,
							'VALUE_TYPE_ID' => $valueType,
							'VALUE_TYPE' => isset($multiFieldEntityType[$valueType]) ? $multiFieldEntityType[$valueType] : null,
							'CLASS_NAME' => isset($multiFieldViewClassNames[$typeID]) ? $multiFieldViewClassNames[$typeID] : ''
						),
						array(
							'ENABLE_SIP' => false,
							'SIP_PARAMS' => array(
								'ENTITY_TYPE_NAME' => CCrmOwnerType::LeadName,
								'ENTITY_ID' => $this->entityID,
								'AUTO_FOLD' => true
							)
						)
					)
				);

				//Is required for phone & email & messenger menu
				if($typeID === 'PHONE' || $typeID === 'EMAIL'
					|| ($typeID === 'IM' && preg_match('/^imol|/', $value) === 1)
				)
				{
					if(!isset($multiFildData[$typeID]))
					{
						$multiFildData[$typeID] = array();
					}

					if(!isset($multiFildData[$typeID][$entityKey]))
					{
						$multiFildData[$typeID][$entityKey] = array();
					}

					$formattedValue = $typeID === 'PHONE'
						? Main\PhoneNumber\Parser::getInstance()->parse($value)->format()
						: $value;

					$multiFildData[$typeID][$entityKey][] = array(
						'ID' => $multiFieldID,
						'VALUE' => $value,
						'VALUE_TYPE' => $valueType,
						'VALUE_FORMATTED' => $formattedValue,
						'COMPLEX_ID' => $multiFieldComplexID,
						'COMPLEX_NAME' => \CCrmFieldMulti::GetEntityNameByComplex($multiFieldComplexID, false)
					);
				}
			}
		}
		else
		{
			if(isset($this->defaultFieldValues['phone']))
			{
				$phone = trim($this->defaultFieldValues['phone']);
				if($phone !== '')
				{
					$this->entityData['PHONE'] = array(
						array('ID' => 'n0', 'VALUE' => $phone, 'VALUE_TYPE' => 'WORK')
					);
				}
			}
		}
		$this->entityData['MULTIFIELD_DATA'] = $multiFildData;
		//endregion

		$this->entityData['ADDRESS_HTML'] = Format\LeadAddressFormatter::format(
			$this->entityData,
			array('SEPARATOR' => Format\AddressSeparator::HtmlLineBreak, 'NL2BR' => true, 'HTML_ENCODE' => true)
		);

		//region Product row
		$productRowCount = 0;
		$productRowTotalSum = 0.0;
		$productRowInfos = array();
		if($this->entityID > 0)
		{
			$dbResult = \CCrmProductRow::GetList(
				array('SORT' => 'ASC', 'ID'=>'ASC'),
				array(
					'OWNER_ID' => $this->entityID, 'OWNER_TYPE' => 'L'
			   	),
				false,
				false,
				array(
					'PRODUCT_ID',
					'PRODUCT_NAME',
					'ORIGINAL_PRODUCT_NAME',
					'PRICE',
					'PRICE_EXCLUSIVE',
					'QUANTITY',
					'TAX_INCLUDED',
					'TAX_RATE'
			   )
			);

			while($fields = $dbResult->Fetch())
			{
				$productName = isset($fields['PRODUCT_NAME']) ? $fields['PRODUCT_NAME'] : '';
				if($productName === '' && isset($fields['ORIGINAL_PRODUCT_NAME']))
				{
					$productName = $fields['ORIGINAL_PRODUCT_NAME'];
				}

				$productID = isset($fields['PRODUCT_ID']) ? (int)$fields['PRODUCT_ID'] : 0;
				$url = '';
				if($productID > 0)
				{
					$url = CComponentEngine::MakePathFromTemplate(
						$this->arResult['PATH_TO_PRODUCT_SHOW'],
						array('product_id' => $fields['PRODUCT_ID'])
					);
				}

				if($fields['TAX_INCLUDED'] === 'Y')
				{
					$sum = $fields['PRICE'] * $fields['QUANTITY'];
				}
				else
				{
					$sum = $fields['PRICE_EXCLUSIVE'] * $fields['QUANTITY'] * (1 + $fields['TAX_RATE'] / 100);
				}

				$productRowTotalSum += $sum;
				$productRowCount++;

				if($productRowCount <= 10)
				{
					$productRowInfos[] = array(
						'PRODUCT_NAME' => $productName,
						'SUM' => CCrmCurrency::MoneyToString($sum, $this->entityData['CURRENCY_ID']),
						'URL' => $url
					);
				}
			}
			$this->entityData['PRODUCT_ROW_SUMMARY'] = array(
				'count' => $productRowCount,
				'total' => CCrmCurrency::MoneyToString($productRowTotalSum, $this->entityData['CURRENCY_ID']),
				'items' => $productRowInfos
			);
		}
		//endregion
		return ($this->arResult['ENTITY_DATA'] = $this->entityData);
	}
	protected function prepareMultifieldData($entityTypeID, $entityID, $typeID, array &$data)
	{
		$dbResult = CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array(
				'ENTITY_ID' => CCrmOwnerType::ResolveName($entityTypeID),
				'ELEMENT_ID' => $entityID,
				'TYPE_ID' => $typeID
			)
		);

		$entityKey = "{$entityTypeID}_{$entityID}";
		while($fields = $dbResult->Fetch())
		{
			$value = isset($fields['VALUE']) ? $fields['VALUE'] : '';
			if($value === '')
			{
				continue;
			}

			if(!isset($data[$typeID]))
			{
				$data[$typeID] = array();
			}

			if(!isset($data[$typeID][$entityKey]))
			{
				$data[$typeID][$entityKey] = array();
			}

			$data[$typeID][$entityKey][] = $value;
		}
	}
	protected function prepareStatusList()
	{
		if($this->statuses === null)
		{
			$this->statuses = array();
			$allStatuses = CCrmStatus::GetStatusList('STATUS');
			foreach ($allStatuses as $statusID => $statusTitle)
			{
				$permissionType = $this->isEditMode
					? \CCrmLead::GetStatusUpdatePermissionType($statusID, $this->userPermissions)
					: \CCrmLead::GetStatusCreatePermissionType($statusID, $this->userPermissions);

				if ($permissionType > BX_CRM_PERM_NONE)
				{
					$this->statuses[$statusID] = $statusTitle;
				}
			}
		}
		return $this->statuses;
	}
	protected function tryGetFieldValueFromRequest($name, array &$params)
	{
		$value = $this->request->get($name);
		if($value === null)
		{
			return false;
		}

		$params[$name] = $value;
		return true;
	}
}