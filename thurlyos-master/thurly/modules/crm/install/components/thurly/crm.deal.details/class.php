<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Thurly\Main;
use Thurly\Main\Localization\Loc;
use  Thurly\Crm\Category\DealCategory;
use Thurly\Crm\Recurring;

if(!Main\Loader::includeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

Loc::loadMessages(__FILE__);

class CCrmDealDetailsComponent extends CThurlyComponent
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
	/** @var int */
	private $categoryID = 0;
	/** @var array|null */
	private $stages = null;
	/** @var bool */
	private $isEditMode = false;
	/** @var bool */
	private $isCopyMode = false;
	/** @var bool */
	private $isExposeMode = false;
	/** @var bool */
	private $isEnableRecurring = true;
	/** @var \Thurly\Crm\Conversion\EntityConversionWizard|null  */
	private $conversionWizard = null;
	/** @var int */
	private $leadID = 0;
	/** @var int */
	private $quoteID = 0;
	/** @var array|null */
	private $defaultFieldValues = null;
	/** @var array|null */
	private $types = null;


	public function __construct($component = null)
	{
		/** @global \CUserTypeManager $USER_FIELD_MANAGER */
		global $USER_FIELD_MANAGER;

		parent::__construct($component);

		$this->userID = CCrmSecurityHelper::GetCurrentUserID();
		$this->userPermissions = CCrmPerms::GetCurrentUserPermissions();
		$this->userType = new \CCrmUserType($USER_FIELD_MANAGER, \CCrmDeal::GetUserFieldEntityID());
		$this->userFieldDispatcher = \Thurly\Main\UserField\Dispatcher::instance();
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
			elseif($k === 'LEAD_ID' || $k === 'QUOTE_ID')
			{
				$this->arResult[$k] = $this->arParams[$k] = (int)$v;
			}
		}
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

		$this->arResult['PATH_TO_DEAL_SHOW'] = CrmCheckPath(
			'PATH_TO_DEAL_SHOW',
			$this->arParams['PATH_TO_DEAL_SHOW'],
			$APPLICATION->GetCurPage().'?deal_id=#deal_id#&show'
		);
		$this->arResult['PATH_TO_DEAL_EDIT'] = CrmCheckPath(
			'PATH_TO_DEAL_EDIT',
			$this->arParams['PATH_TO_DEAL_EDIT'],
			$APPLICATION->GetCurPage().'?deal_id=#deal_id#&edit'
		);

		$this->arResult['PATH_TO_QUOTE_SHOW'] = CrmCheckPath(
			'PATH_TO_QUOTE_SHOW',
			$this->arParams['PATH_TO_QUOTE_SHOW'],
			$APPLICATION->GetCurPage().'?quote_id=#quote_id#&show'
		);
		$this->arResult['PATH_TO_QUOTE_EDIT'] = CrmCheckPath(
			'PATH_TO_QUOTE_EDIT',
			$this->arParams['PATH_TO_QUOTE_EDIT'],
			$APPLICATION->GetCurPage().'?quote_id=#quote_id#&edit'
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

		$ufEntityID = \CCrmDeal::GetUserFieldEntityID();
		$enableUfCreation = \CCrmAuthorizationHelper::CheckConfigurationUpdatePermission();
		$this->arResult['ENABLE_USER_FIELD_CREATION'] = $enableUfCreation;
		$this->arResult['USER_FIELD_ENTITY_ID'] = $ufEntityID;
		$this->arResult['USER_FIELD_CREATE_PAGE_URL'] = CCrmOwnerType::GetUserFieldEditUrl($ufEntityID, 0);
		$this->arResult['USER_FIELD_CREATE_SIGNATURE'] = $enableUfCreation
			? $this->userFieldDispatcher->getCreateSignature(array('ENTITY_ID' => $ufEntityID))
			: '';
		$this->arResult['ENABLE_TASK'] = IsModuleInstalled('tasks');
		$this->arResult['ACTION_URI'] = $this->arResult['POST_FORM_URI'] = POST_FORM_ACTION_URI;

		$this->arResult['PRODUCT_DATA_FIELD_NAME'] = 'DEAL_PRODUCT_DATA';
		$this->arResult['PRODUCT_EDITOR_ID'] = 'deal_product_editor';

		$this->arResult['CONTEXT_ID'] = \CCrmOwnerType::DealName.'_'.$this->arResult['ENTITY_ID'];
		$this->arResult['CONTEXT_PARAMS'] = array(
			'PATH_TO_PRODUCT_SHOW' => $this->arResult['PATH_TO_PRODUCT_SHOW'],
			'PATH_TO_USER_PROFILE' => $this->arResult['PATH_TO_USER_PROFILE'],
			'NAME_TEMPLATE' => $this->arResult['NAME_TEMPLATE']
		);

		$this->arResult['EXTERNAL_CONTEXT_ID'] = $this->request->get('external_context_id');
		if($this->arResult['EXTERNAL_CONTEXT_ID'] === null)
		{
			$this->arResult['EXTERNAL_CONTEXT_ID'] = $this->request->get('external_context');
			if($this->arResult['EXTERNAL_CONTEXT_ID'] === null)
			{
				$this->arResult['EXTERNAL_CONTEXT_ID'] = '';
			}
		}
		$this->isEnableRecurring = \Thurly\Crm\Recurring\Manager::isAllowedExpose(\Thurly\Crm\Recurring\Manager::DEAL);

		$this->arResult['ORIGIN_ID'] = $this->request->get('origin_id');
		if($this->arResult['ORIGIN_ID'] === null)
		{
			$this->arResult['ORIGIN_ID'] = '';
		}

		$this->defaultFieldValues = array();
		//endregion

		$this->setEntityID($this->arResult['ENTITY_ID']);

		//region Is Editing or Copying?
		if($this->entityID > 0)
		{
			if(!\CCrmDeal::Exists($this->entityID))
			{
				ShowError(GetMessage('CRM_DEAL_NOT_FOUND'));
				return;
			}

			if($this->request->get('copy') !== null)
			{
				$this->isCopyMode = true;
				$this->arResult['CONTEXT_PARAMS']['DEAL_ID'] = $this->entityID;
			}
			elseif ($this->request->get('expose') !== null)
			{
				$this->isExposeMode = true;
				$this->arResult['CONTEXT_PARAMS']['DEAL_ID'] = $this->entityID;
			}
			else
			{
				$this->isEditMode = true;
			}
		}
		$this->arResult['IS_EDIT_MODE'] = $this->isEditMode;
		$this->arResult['IS_COPY_MODE'] = $this->isCopyMode;
		//endregion

		//region Category && Category List
		$categoryReadMap = array_fill_keys(\CCrmDeal::GetPermittedToReadCategoryIDs($this->userPermissions), true);
		$categoryCreateMap = array_fill_keys(\CCrmDeal::GetPermittedToCreateCategoryIDs($this->userPermissions), true);
		$this->arResult['READ_CATEGORY_LIST'] = $this->arResult['CREATE_CATEGORY_LIST'] = array();
		foreach(DealCategory::getAll(true) as $item)
		{
			if (isset($categoryReadMap[$item['ID']]))
			{
				$this->arResult['READ_CATEGORY_LIST'][$item['ID']] = array(
					'NAME' => isset($item['NAME']) ? $item['NAME'] : "[{$item['ID']}]",
					'VALUE' => $item['ID']
				);
			}
			if (isset($categoryCreateMap[$item['ID']]))
			{
				$this->arResult['CREATE_CATEGORY_LIST'][$item['ID']] = array(
					'NAME' => isset($item['NAME']) ? $item['NAME'] : "[{$item['ID']}]",
					'VALUE' => $item['ID']
				);
			}
		}

		$categoryID = -1;
		if(isset($extras['DEAL_CATEGORY_ID']) && $extras['DEAL_CATEGORY_ID'] >= 0)
		{
			$categoryID = (int)$extras['DEAL_CATEGORY_ID'];
		}
		if($categoryID < 0 && $this->entityID > 0)
		{
			$categoryID = \CCrmDeal::GetCategoryID($this->entityID);
		}
		if($categoryID < 0 && isset($this->request['category_id']) && $this->request['category_id'] >= 0)
		{
			$categoryID = (int)$this->request['category_id'];
			if($categoryID > 0 && !Thurly\Crm\Category\DealCategory::isEnabled($categoryID))
			{
				$categoryID = -1;
			}
		}
		if($this->entityID <= 0)
		{
			//We are in CREATE or COPY mode
			//Check if specified category is permitted
			if($categoryID >= 0 && !isset($categoryCreateMap[$categoryID]))
			{
				$categoryID = -1;
			}

			//Get default category if category is not specified
			if($categoryID < 0 && !empty($categoryCreateMap))
			{
				$categoryID = current(array_keys($categoryCreateMap));
			}
		}

		$this->arResult['CATEGORY_ID'] = $this->categoryID = max($categoryID, 0);
		//endregion

		//region Conversion Scheme
		if(isset($this->arResult['LEAD_ID']) && $this->arResult['LEAD_ID'] > 0)
		{
			$this->leadID = $this->arResult['LEAD_ID'];
		}
		elseif(isset($this->request['lead_id']) && $this->request['lead_id'] > 0)
		{
			$this->leadID = $this->arResult['LEAD_ID'] = (int)$this->request['lead_id'];
		}

		if($this->leadID > 0)
		{
			$this->conversionWizard = \Thurly\Crm\Conversion\LeadConversionWizard::load($this->leadID);
			if($this->conversionWizard !== null)
			{
				$this->arResult['CONTEXT_PARAMS']['LEAD_ID'] = $this->leadID;

				//TODO: Move code in to wizard
				$config = $this->conversionWizard->getEntityConfig(CCrmOwnerType::Deal);
				if($config)
				{
					$initData = $config->getInitData();
					if(is_array($initData) && isset($initData['categoryId']))
					{
						$this->arResult['CATEGORY_ID'] = $this->categoryID = (int)$initData['categoryId'];
					}
				}
			}
		}

		if(isset($this->arResult['QUOTE_ID']) && $this->arResult['QUOTE_ID'] > 0)
		{
			$this->quoteID = $this->arResult['QUOTE_ID'];
		}
		elseif(isset($this->request['conv_quote_id']) && $this->request['conv_quote_id'] > 0)
		{
			$this->quoteID = $this->arResult['QUOTE_ID'] = (int)$this->request['conv_quote_id'];
		}

		if($this->quoteID > 0)
		{
			$this->conversionWizard = \Thurly\Crm\Conversion\QuoteConversionWizard::load($this->quoteID);
			if($this->conversionWizard !== null)
			{
				$this->arResult['CONTEXT_PARAMS']['QUOTE_ID'] = $this->quoteID;
			}
		}
		//endregion

		//region Permissions check
		if($this->isCopyMode)
		{
			if(!(\CCrmDeal::CheckReadPermission($this->entityID, $this->userPermissions, $this->categoryID)
				&& \CCrmDeal::CheckCreatePermission($this->userPermissions, $this->categoryID))
			)
			{
				ShowError(GetMessage('CRM_PERMISSION_DENIED'));
				return;
			}
		}
		elseif ($this->isExposeMode)
		{
			$dealRecurringData = \Thurly\Crm\Recurring\Manager::getList(
				array(
					'filter' => array('DEAL_ID' => (int)$this->arResult['ENTITY_ID']),
					'limit' => 1
				),
				\Thurly\Crm\Recurring\Manager::DEAL
			);
			$recurring = $dealRecurringData->fetch();
			if (!($recurring
				&&\CCrmDeal::CheckReadPermission($this->entityID, $this->userPermissions, $this->categoryID)
				&& \CCrmDeal::CheckCreatePermission($this->userPermissions, $recurring['CATEGORY_ID'])))
			{
				ShowError(GetMessage('CRM_PERMISSION_DENIED'));
				return;
			}

			$this->arResult['CATEGORY_ID'] = $this->categoryID = (int)$recurring['CATEGORY_ID'];
		}
		elseif($this->isEditMode)
		{
			if(\CCrmDeal::CheckUpdatePermission($this->entityID, $this->userPermissions, $this->categoryID))
			{
				$this->arResult['READ_ONLY'] = false;
			}
			elseif(\CCrmDeal::CheckReadPermission($this->entityID, $this->userPermissions, $this->categoryID))
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
			if(\CCrmDeal::CheckCreatePermission($this->userPermissions, $this->categoryID))
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

		//expose recurring region
		if ($this->isExposeMode)
		{
			$resultExposing = \Thurly\Crm\Recurring\Manager::expose(
				array('DEAL_ID' => (int)$this->arResult['ENTITY_ID']),1,\Thurly\Crm\Recurring\Manager::DEAL
			);

			if ($resultExposing->isSuccess())
			{
				$exposedData = $resultExposing->getData();
				$this->isEditMode = true;
				$this->arResult['IS_EDIT_MODE'] = true;
				$newId = $exposedData['ID'][0];
				$this->setEntityID($newId);
				$this->arResult['ENTITY_ID'] = $newId;
				$this->arResult['CONTEXT_ID'] = \CCrmOwnerType::DealName.'_'.$newId;
			}
		}
		//endregion

		$this->prepareEntityUserFields();
		$this->prepareEntityUserFieldInfos();
		$this->prepareEntityData();
		$this->prepareStageList();

		//region GUID
		$this->guid = $this->arResult['GUID'] = isset($this->arParams['GUID'])
			? $this->arParams['GUID'] : "deal_{$this->entityID}_details";

		$this->arResult['EDITOR_CONFIG_ID'] = Thurly\Crm\Category\DealCategory::prepareFormID(
			$this->categoryID,
			isset($this->arParams['EDITOR_CONFIG_ID']) ? $this->arParams['EDITOR_CONFIG_ID'] : 'deal_details',
			false
		);
		//endregion

		//region Entity Info
		$this->arResult['ENTITY_INFO'] = array(
			'ENTITY_ID' => $this->entityID,
			'ENTITY_TYPE_ID' => CCrmOwnerType::Deal,
			'ENTITY_TYPE_NAME' => CCrmOwnerType::DealName,
			'TITLE' => isset($this->entityData['TITLE']) ? $this->entityData['TITLE'] : '',
			'SHOW_URL' => CCrmOwnerType::GetEntityShowPath(CCrmOwnerType::Deal, $this->entityID, false),
		);
		//endregion

		$progressSemantics = $this->entityData['STAGE_ID']
			? \CCrmDeal::GetStageSemantics($this->entityData['STAGE_ID']) : '';
		$this->arResult['PROGRESS_SEMANTICS'] = $progressSemantics;

		//region Page title
		if($this->isCopyMode)
		{
			$APPLICATION->SetTitle(Loc::getMessage('CRM_DEAL_COPY_PAGE_TITLE'));
		}
		elseif(isset($this->entityData['TITLE']))
		{
			if ($this->entityData['IS_RECURRING'] === "Y")
			{
				$APPLICATION->SetTitle(
					Loc::getMessage(
						"CRM_DEAL_FIELD_RECURRING_TITLE",
						array(
							"#TITLE#" => $this->entityData['TITLE']
						)
					)
				);
			}
			else
			{
				$APPLICATION->SetTitle($this->entityData['TITLE']);
			}
		}
		elseif(!$this->isEditMode)
		{
			$APPLICATION->SetTitle(Loc::getMessage('CRM_DEAL_CREATION_PAGE_TITLE'));
		}
		//endregion

		//region Conversion
		$this->arResult['PERMISSION_ENTITY_TYPE'] = DealCategory::convertToPermissionEntityType($this->categoryID);
		CCrmDeal::PrepareConversionPermissionFlags($this->entityID, $this->arResult, $this->userPermissions);
		if($this->arResult['CAN_CONVERT'])
		{
			$config = \Thurly\Crm\Conversion\DealConversionConfig::load();
			if($config === null)
			{
				$config = \Thurly\Crm\Conversion\DealConversionConfig::getDefault();
			}

			$this->arResult['CONVERSION_CONFIG'] = $config;
		}
		//endregion
		//region Recurring Deals
		if ($this->entityData['IS_RECURRING'] === 'Y')
		{
			$dbResult = Recurring\Manager::getList(
				array('filter' => array('=DEAL_ID' => $this->entityID)),
				Recurring\Manager::DEAL
			);
			$recurringData = $dbResult->fetch();
			if (strlen($recurringData['NEXT_EXECUTION']) > 0 && $recurringData['ACTIVE'] === 'Y' && $this->isEnableRecurring)
			{
				$recurringViewText =  Loc::getMessage(
					'CRM_DEAL_FIELD_RECURRING_DATE_NEXT_EXECUTION',
					array(
						'#NEXT_DATE#' => $recurringData['NEXT_EXECUTION']
					)
				);
			}
			else
			{
				$recurringViewText = Loc::getMessage('CRM_DEAL_FIELD_RECURRING_NOTHING_SELECTED');
			}
		}
		elseif ($this->entityID > 0)
		{
			$dbResult = Recurring\Manager::getList(
				array(
					'filter' => array('=BASED_ID' => $this->entityID),
					'select' => array('DEAL_ID')
				),
				Recurring\Manager::DEAL
			);

			$recurringLine = "";
			$recurringList = $dbResult->fetchAll();
			$recurringCount = count($recurringList);
			if ($recurringCount === 1)
			{
				$recurringViewText =  Loc::getMessage(
				'CRM_DEAL_FIELD_RECURRING_CREATED_FROM_CURRENT',
					array(
						'#RECURRING_ID#' => $recurringList[0]['DEAL_ID']
					)
				);
			}
			elseif ($recurringCount > 1)
			{
				foreach ($recurringList as $item)
				{
					$recurringLine .= Loc::getMessage('CRM_DEAL_FIELD_NUM_SIGN', array("#DEAL_ID#" => $item['DEAL_ID'])).", ";
				}

				if (strlen($recurringLine) > 0)
				{
					$recurringLine = substr($recurringLine, 0, -2);
					$recurringViewText =  Loc::getMessage(
						'CRM_DEAL_FIELD_RECURRING_CREATED_MANY_FROM_CURRENT',
						array(
							'#RECURRING_LIST#' => $recurringLine
						)
					);
				}
			}
		}

		if (empty($recurringViewText) && empty($this->arResult['CREATE_CATEGORY_LIST']) )
		{
			$recurringViewText  =  Loc::getMessage("CRM_DEAL_FIELD_RECURRING_RESTRICTED");
		}
		if (empty($recurringViewText))
		{
			$recurringViewText  =  Loc::getMessage("CRM_DEAL_FIELD_RECURRING_NOTHING_SELECTED");
		}
		if (!$this->isEnableRecurring)
		{
			switch (LANGUAGE_ID)
			{
				case "ru":
				case "kz":
				case "by":
					$promoLink = 'https://www.thurlyos.ru/pro/crm.php ';
					break;
				case "de":
					$promoLink = 'https://www.thurlyos.de/pro/crm.php';
					break;
				case "ua":
					$promoLink = 'https://www.thurlyos.ua/pro/crm.php';
					break;
				default:
					$promoLink = 'https://www.thurlyos.com/pro/crm.php';
			}
		}
		else
		{
			$promoLink = "";
		}
		//endregion

		//region FIELDS
		$companyID = isset($this->entityData['COMPANY_ID']) ? (int)$this->entityData['COMPANY_ID'] : 0;
		$primaryEntityTypeName = CCrmOwnerType::CompanyName;
		if($companyID <= 0 || !empty($contactIDs))
		{
			$primaryEntityTypeName = CCrmOwnerType::ContactName;
		}

		$allStages = Thurly\Crm\Category\DealCategory::getStageList($this->categoryID);
		$prohibitedStageIDS = array();
		foreach(array_keys($allStages) as $stageID)
		{
			if($this->arResult['READ_ONLY'])
			{
				$prohibitedStageIDS[] = $stageID;
			}
			else
			{
				$permissionType = $this->isEditMode
					? \CCrmDeal::GetStageUpdatePermissionType($stageID, $this->userPermissions, $this->categoryID)
					: \CCrmDeal::GetStageCreatePermissionType($stageID, $this->userPermissions, $this->categoryID);

				if($permissionType == BX_CRM_PERM_NONE)
				{
					$prohibitedStageIDS[] = $stageID;
				}
			}
		}

		$this->arResult['ENTITY_FIELDS'] = array(
			array(
				'name' => 'ID',
				'title' => Loc::getMessage('CRM_DEAL_FIELD_ID'),
				'type' => 'text',
				'editable' => false
			),
			array(
				'name' => 'TITLE',
				'title' => Loc::getMessage('CRM_DEAL_FIELD_TITLE'),
				'type' => 'text',
				'isHeading' => true,
				'visibilityPolicy' => 'edit',
				'required' => true,
				'editable' => true
			),
			array(
				'name' => 'TYPE_ID',
				'title' => Loc::getMessage('CRM_DEAL_FIELD_TYPE_ID'),
				'type' => 'list',
				'editable' => true,
				'data' => array('items' => \CCrmInstantEditorHelper::PrepareListOptions($this->prepareTypeList()))
			),
			array(
				'name' => 'STAGE_ID',
				'title' => Loc::getMessage('CRM_DEAL_FIELD_STAGE_ID'),
				'type' => 'list',
				'editable' => true,
				'data' => array(
					'items' => \CCrmInstantEditorHelper::PrepareListOptions(
						$allStages,
						array('EXCLUDE_FROM_EDIT' => $prohibitedStageIDS)
					)
				)
			),
			array(
				'name' => 'OPPORTUNITY_WITH_CURRENCY',
				'title' => Loc::getMessage('CRM_DEAL_FIELD_OPPORTUNITY_WITH_CURRENCY'),
				'type' => 'money',
				'editable' => true,
				'data' => array(
					'affectedFields' => array('CURRENCY_ID', 'OPPORTUNITY'),
					'currency' => array(
						'name' => 'CURRENCY_ID',
						'items' => \CCrmInstantEditorHelper::PrepareListOptions(CCrmCurrencyHelper::PrepareListItems())
					),
					'amount' => 'OPPORTUNITY',
					'formatted' => 'FORMATTED_OPPORTUNITY',
					'formattedWithCurrency' => 'FORMATTED_OPPORTUNITY_WITH_CURRENCY'
				)
			),
			array(
				'name' => 'CLOSEDATE',
				'title' => Loc::getMessage('CRM_DEAL_FIELD_CLOSEDATE'),
				'type' => 'datetime',
				'editable' => true,
				'data' =>  array('enableTime' => false)
			),
			array(
				'name' => 'BEGINDATE',
				'title' => Loc::getMessage('CRM_DEAL_FIELD_BEGINDATE'),
				'type' => 'datetime',
				'editable' => true,
				'data' => array('enableTime' => false)
			),
			array(
				"name" => "PROBABILITY",
				"title" => Loc::getMessage("CRM_DEAL_FIELD_PROBABILITY"),
				"type" => "number",
				"editable" => true
			),
			array(
				"name" => "OPENED",
				"title" => Loc::getMessage("CRM_DEAL_FIELD_OPENED"),
				"type" => "boolean",
				"editable" => true
			),
			array(
				"name" => "COMMENTS",
				"title" => Loc::getMessage("CRM_DEAL_FIELD_COMMENTS"),
				"type" => "html",
				"editable" => true
			),
			array(
				"name" => "CLIENT",
				"title" => Loc::getMessage("CRM_DEAL_FIELD_CLIENT"),
				"type" => "client",
				"editable" => true,
				"data" => array(
					"map" => array(
						"primaryEntityType" => "CLIENT_PRIMARY_ENTITY_TYPE",
						"primaryEntityId" => "CLIENT_PRIMARY_ENTITY_ID",
						"secondaryEntityType" => "CLIENT_SECONDARY_ENTITY_TYPE",
						"secondaryEntityIds" => "CLIENT_SECONDARY_ENTITY_IDS",
						"unboundSecondaryEntityIds" => "CLIENT_UBOUND_SECONDARY_ENTITY_IDS",
						"boundSecondaryEntityIds" => "CLIENT_BOUND_SECONDARY_ENTITY_IDS",
					),
					'info' => 'CLIENT_INFO',
					'primaryEntityTypeName' => $primaryEntityTypeName,
					'secondaryEntityTypeName' => CCrmOwnerType::ContactName,
					'secondaryEntityLegend' => Loc::getMessage('CRM_DEAL_FIELD_CONTACT_LEGEND'),
					'loaders' => array(
						'primary' => array(
							CCrmOwnerType::CompanyName => array(
								'action' => 'GET_CLIENT_INFO',
								'url' => '/thurly/components/thurly/crm.company.show/ajax.php?'.thurly_sessid_get()
							),
							CCrmOwnerType::ContactName => array(
								'action' => 'GET_CLIENT_INFO',
								'url' => '/thurly/components/thurly/crm.contact.show/ajax.php?'.thurly_sessid_get()
							)
						),
						'secondary' => array(
							CCrmOwnerType::CompanyName => array(
								'action' => 'GET_SECONDARY_ENTITY_INFOS',
								'url' => '/thurly/components/thurly/crm.deal.edit/ajax.php?'.thurly_sessid_get()
							)
						)
					)
				)
			),
			array(
				'name' => 'ASSIGNED_BY_ID',
				'title' => Loc::getMessage('CRM_DEAL_FIELD_ASSIGNED_BY_ID'),
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
				"name" => "PRODUCT_ROW_SUMMARY",
				"title" => Loc::getMessage("CRM_DEAL_FIELD_PRODUCTS"),
				"type" => "product_row_summary",
				"editable" => false,
				"transferable" => false
			),
			array(
				"name" => "RECURRING",
				"title" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING"),
				"type" => "recurring",
				"editable" => count($this->arResult['CREATE_CATEGORY_LIST']) > 0,
				"transferable" => false,
				"enableRecurring" => $this->isEnableRecurring,
				"data" => array(
					'loaders' => array(
						'action' => 'GET_DEAL_HINT',
						'url' => '/thurly/components/thurly/crm.interface.form.recurring/ajax.php?'.thurly_sessid_get()
					),
					"view" => array(
						'text' => $recurringViewText
					),
					"data" => array(
						"MULTIPLY_EXECUTION" => Recurring\Manager::MULTIPLY_EXECUTION,
						"SINGLE_EXECUTION" => Recurring\Manager::SINGLE_EXECUTION,
						"NO_LIMIT" => Recurring\Entity\Deal::NO_LIMITED,
						"NON_ACTIVE" => Recurring\Calculator::SALE_TYPE_NON_ACTIVE_DATE,
						"LIMITED_BY_DATE" => Recurring\Entity\Deal::LIMITED_BY_DATE,
						"LIMITED_BY_TIMES" => Recurring\Entity\Deal::LIMITED_BY_TIMES,
						"PERIOD_DEAL" => array(
							'options' => array(
								Recurring\Calculator::SALE_TYPE_NON_ACTIVE_DATE => array(
									"VALUE" => Recurring\Calculator::SALE_TYPE_NON_ACTIVE_DATE,
									"NAME" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING_NOT_REPEAT")
								),
								Recurring\Calculator::SALE_TYPE_DAY_OFFSET => array(
									"VALUE" => Recurring\Calculator::SALE_TYPE_DAY_OFFSET,
									"NAME" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING_EVERYDAY")
								),
								Recurring\Calculator::SALE_TYPE_WEEK_OFFSET => array(
									"VALUE" => Recurring\Calculator::SALE_TYPE_WEEK_OFFSET,
									"NAME" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING_EVERY_WEEK")
								),
								Recurring\Calculator::SALE_TYPE_MONTH_OFFSET => array(
									"VALUE" => Recurring\Calculator::SALE_TYPE_MONTH_OFFSET,
									"NAME" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING_EVERY_MONTH")
								),
								Recurring\Calculator::SALE_TYPE_YEAR_OFFSET => array(
									"VALUE" => Recurring\Calculator::SALE_TYPE_YEAR_OFFSET,
									"NAME" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING_EVERY_YEAR")
								)
							)
						),
						"DEAL_TYPE_BEFORE" => array(
							'options' => array(
								Recurring\Calculator::SALE_TYPE_DAY_OFFSET => array(
									"VALUE" => Recurring\Calculator::SALE_TYPE_DAY_OFFSET,
									"NAME" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING_DAY")
								),
								Recurring\Calculator::SALE_TYPE_WEEK_OFFSET => array(
									"VALUE" => Recurring\Calculator::SALE_TYPE_WEEK_OFFSET,
									"NAME" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING_WEEK")
								),
								Recurring\Calculator::SALE_TYPE_MONTH_OFFSET => array(
									"VALUE" => Recurring\Calculator::SALE_TYPE_MONTH_OFFSET,
									"NAME" => Loc::getMessage("CRM_DEAL_FIELD_RECURRING_MONTH")
								)
							)
						),
						"CATEGORY_LIST" => array(
							'options' => $this->arResult['CREATE_CATEGORY_LIST']
						)
					),
					"restrictMessage" => array(
						"title" => !$this->isEnableRecurring ? Loc::getMessage("CRM_RECURRING_DEAL_B24_BLOCK_TITLE") : "",
						"text" => !$this->isEnableRecurring ? Loc::getMessage("CRM_RECURRING_DEAL_B24_BLOCK_TEXT", array("#LINK#" => $promoLink)) : "",
					)
				)
			),
		);
		$this->arResult['ENTITY_FIELDS'] = array_merge(
			$this->arResult['ENTITY_FIELDS'],
			array_values($this->userFieldInfos)
		);
		//endregion
		//region CONFIG
		$userFieldConfigElements = array();
		foreach(array_keys($this->userFieldInfos) as $fieldName)
		{
			$userFieldConfigElements[] = array('name' => $fieldName);
		}
		$this->arResult['ENTITY_CONFIG'] = array(
			array(
				'name' => 'main',
				'title' => Loc::getMessage('CRM_DEAL_SECTION_MAIN'),
				'type' => 'section',
				'elements' => array(
					array('name' => 'TITLE'),
					array('name' => 'OPPORTUNITY_WITH_CURRENCY'),
					array('name' => 'STAGE_ID'),
					array('name' => 'CLOSEDATE'),
					array('name' => 'CLIENT'),
				)
			),
			array(
				'name' => 'additional',
				'title' => Loc::getMessage('CRM_DEAL_SECTION_ADDITIONAL'),
				'type' => 'section',
				'elements' =>
					array_merge(
						array(
							array('name' => 'TYPE_ID'),
							array('name' => 'BEGINDATE'),
							//array('name' => 'STAGE_ID'),
							array('name' => 'OPENED'),
							array('name' => 'ASSIGNED_BY_ID'),
							array('name' => 'COMMENTS'),
						),
						$userFieldConfigElements
					)
			),
			array(
				'name' => 'products',
				'title' => Loc::getMessage('CRM_DEAL_SECTION_PRODUCTS'),
				'type' => 'section',
				'elements' => array(
					array('name' => 'PRODUCT_ROW_SUMMARY')
				)
			),
			array(
				'name' => 'recurring',
				'title' => Loc::getMessage('CRM_DEAL_SECTION_RECURRING'),
				'type' => 'section',
				'elements' => array(
					array('name' => 'RECURRING')
				)
			)
		);
		//endregion

		//region CONTROLLERS
		$this->arResult['ENTITY_CONTROLLERS'] = array(
			array(
				"name" => "PRODUCT_ROW_PROXY",
				"type" => "product_row_proxy",
				"config" => array("editorId" => $this->arResult['PRODUCT_EDITOR_ID'])
			),
		);
		//endregion

		//region TABS
		$this->arResult['TABS'] = array();

		$currencyID = CCrmCurrency::GetBaseCurrencyID();
		if(isset($this->entityData['CURRENCY_ID']) && $this->entityData['CURRENCY_ID'] !== '')
		{
			$currencyID = $this->entityData['CURRENCY_ID'];
		}

		$bTaxMode = \CCrmTax::isTaxMode();

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
				'OWNER_TYPE' => 'D',
				'PERMISSION_TYPE' => $this->arResult['READ_ONLY'] ? 'READ' : 'WRITE',
				'PERMISSION_ENTITY_TYPE' => $this->arResult['PERMISSION_ENTITY_TYPE'],
				'PERSON_TYPE_ID' => $personTypeID,
				'CURRENCY_ID' => $currencyID,
				'LOCATION_ID' => $bTaxMode && isset($this->entityData['LOCATION_ID']) ? $this->entityData['LOCATION_ID'] : '',
				'CLIENT_SELECTOR_ID' => '', //TODO: Add Client Selector
				'PRODUCT_ROWS' =>  isset($this->entityData['PRODUCT_ROWS']) ? $this->entityData['PRODUCT_ROWS'] : null,
				'HIDE_MODE_BUTTON' => !$this->isEditMode ? 'Y' : 'N',
				'TOTAL_SUM' => isset($this->entityData['OPPORTUNITY']) ? $this->entityData['OPPORTUNITY'] : null,
				'TOTAL_TAX' => isset($this->entityData['TAX_VALUE']) ? $this->entityData['TAX_VALUE'] : null,
				'PRODUCT_DATA_FIELD_NAME' => $this->arResult['PRODUCT_DATA_FIELD_NAME'],
				'PATH_TO_PRODUCT_EDIT' => $this->arResult['PATH_TO_PRODUCT_EDIT'],
				'PATH_TO_PRODUCT_SHOW' => $this->arResult['PATH_TO_PRODUCT_SHOW'],
				'INIT_LAYOUT' => 'N',
				'INIT_EDITABLE' => $this->arResult['READ_ONLY'] ? 'N' : 'Y',
				'ENABLE_MODE_CHANGE' => 'N',
				'ENABLE_SUBMIT_WITHOUT_LAYOUT' => ($this->isCopyMode || $this->conversionWizard !== null) ? 'Y' : 'N'
			),
			false,
			array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT'=>'Y')
		);
		$html = ob_get_contents();
		ob_end_clean();

		$this->arResult['TABS'][] = array(
			'id' => 'tab_products',
			'name' => Loc::getMessage('CRM_DEAL_TAB_PRODUCTS'),
			'html' => $html
		);

		if ($this->entityData['IS_RECURRING'] !== "Y")
		{
			if($this->entityID > 0)
			{
				$quoteID = isset($this->entityData['QUOTE_ID']) ? (int)$this->entityData['QUOTE_ID'] : 0;
				if($quoteID > 0)
				{
					$quoteDbResult = \CCrmQuote::GetList(
						array(),
						array('=ID' => $quoteID, 'CHECK_PERMISSIONS' => 'N'),
						false,
						false,
						array('TITLE')
					);
					$quoteFields = is_object($quoteDbResult) ? $quoteDbResult->Fetch() : null;
					if (is_array($quoteFields))
					{
						$this->arResult['TABS'][] = array(
							'id' => 'tab_quote',
							'name' => GetMessage('CRM_DEAL_TAB_QUOTE'),
							'html' => '<div class="crm-conv-info">'
								.Loc::getMessage(
									'CRM_DEAL_QUOTE_LINK',
									array(
										'#TITLE#' => htmlspecialcharsbx($quoteFields['TITLE']),
										'#URL#' => CCrmOwnerType::GetEntityShowPath(CCrmOwnerType::Quote, $quoteID, false)
									)
								)
								.'</div>'
						);
					}
				}
				else
				{
					$this->arResult['TABS'][] = array(
						'id' => 'tab_quote',
						'name' => Loc::getMessage('CRM_DEAL_TAB_QUOTE'),
						'loader' => array(
							'serviceUrl' => '/thurly/components/thurly/crm.quote.list/lazyload.ajax.php?&site'.SITE_ID.'&'.thurly_sessid_get(),
							'componentData' => array(
								'template' => '',
								'params' => array(
									'QUOTE_COUNT' => '20',
									'PATH_TO_QUOTE_SHOW' => $this->arResult['PATH_TO_QUOTE_SHOW'],
									'PATH_TO_QUOTE_EDIT' => $this->arResult['PATH_TO_QUOTE_EDIT'],
									'INTERNAL_FILTER' => array('DEAL_ID' => $this->entityID),
									'INTERNAL_CONTEXT' => array('DEAL_ID' => $this->entityID),
									'GRID_ID_SUFFIX' => 'DEAL_DETAILS',
									'TAB_ID' => 'tab_quote',
									'NAME_TEMPLATE' => $this->arResult['NAME_TEMPLATE'],
									'ENABLE_TOOLBAR' => true,
									'PRESERVE_HISTORY' => true,
									'ADD_EVENT_NAME' => 'CrmCreateQuoteFromDeal'
								)
							)
						)
					);
				}
				$this->arResult['TABS'][] = array(
					'id' => 'tab_invoice',
					'name' => Loc::getMessage('CRM_DEAL_TAB_INVOICES'),
					'loader' => array(
						'serviceUrl' => '/thurly/components/thurly/crm.invoice.list/lazyload.ajax.php?&site'.SITE_ID.'&'.thurly_sessid_get(),
						'componentData' => array(
							'template' => '',
							'params' => array(
								'INVOICE_COUNT' => '20',
								'PATH_TO_COMPANY_SHOW' => $this->arResult['PATH_TO_COMPANY_SHOW'],
								'PATH_TO_COMPANY_EDIT' => $this->arResult['PATH_TO_COMPANY_EDIT'],
								'PATH_TO_CONTACT_EDIT' => $this->arResult['PATH_TO_CONTACT_EDIT'],
								'PATH_TO_DEAL_EDIT' => $this->arResult['PATH_TO_DEAL_EDIT'],
								'PATH_TO_INVOICE_EDIT' => $this->arResult['PATH_TO_INVOICE_EDIT'],
								'PATH_TO_INVOICE_PAYMENT' => $this->arResult['PATH_TO_INVOICE_PAYMENT'],
								'INTERNAL_FILTER' => array('UF_DEAL_ID' => $this->entityID),
								'SUM_PAID_CURRENCY' => $currencyID,
								'GRID_ID_SUFFIX' => 'DEAL_DETAILS',
								'TAB_ID' => 'tab_invoice',
								'NAME_TEMPLATE' => $this->arResult['NAME_TEMPLATE'],
								'ENABLE_TOOLBAR' => 'Y',
								'PRESERVE_HISTORY' => true,
								'ADD_EVENT_NAME' => 'CrmCreateInvoiceFromDeal'
							)
						)
					)
				);
				if (\Thurly\Crm\Automation\Factory::isAutomationAvailable(CCrmOwnerType::Deal))
				{
					Thurly\Main\Page\Asset::getInstance()->addCss('/thurly/components/thurly/crm.automation/templates/.default/style.css');
					$this->arResult['TABS'][] = array(
						'id' => 'tab_automation',
						'name' => Loc::getMessage('CRM_DEAL_TAB_AUTOMATION'),
						'loader' => array(
							'serviceUrl' => '/thurly/components/thurly/crm.automation/lazyload.ajax.php?&site='.SITE_ID.'&'.thurly_sessid_get(),
							'componentData' => array(
								'template' => '',
								'params' => array(
									'ENTITY_TYPE_ID' => \CCrmOwnerType::Deal,
									'ENTITY_ID' => $this->entityID,
									'ENTITY_CATEGORY_ID' => $this->categoryID,
									'back_url' => \CCrmOwnerType::GetEntityShowPath(\CCrmOwnerType::Deal, $this->entityID)
								)
							)
						)
					);
				}
				if (CModule::IncludeModule('bizproc') && CBPRuntime::isFeatureEnabled())
				{
					$this->arResult['TABS'][] = array(
						'id' => 'tab_bizproc',
						'name' => Loc::getMessage('CRM_DEAL_TAB_BIZPROC'),
						'loader' => array(
							'serviceUrl' => '/thurly/components/thurly/bizproc.document/lazyload.ajax.php?&site='.SITE_ID.'&'.thurly_sessid_get(),
							'componentData' => array(
								'template' => 'frame',
								'params' => array(
									'MODULE_ID' => 'crm',
									'ENTITY' => 'CCrmDocumentDeal',
									'DOCUMENT_TYPE' => 'DEAL',
									'DOCUMENT_ID' => 'DEAL_'.$this->entityID
								)
							)
						)
					);
					$this->arResult['BIZPROC_STARTER_DATA'] = array(
						'templates' => CBPDocument::getTemplatesForStart(
							$this->userID,
							array('crm', 'CCrmDocumentDeal', 'DEAL'),
							array('crm', 'CCrmDocumentDeal', 'DEAL_'.$this->entityID)
						),
						'moduleId' => 'crm',
						'entity' => 'CCrmDocumentDeal',
						'documentType' => 'DEAL',
						'documentId' => 'DEAL_'.$this->entityID
					);
				}
				$this->arResult['TABS'][] = array(
					'id' => 'tab_tree',
					'name' => Loc::getMessage('CRM_DEAL_TAB_TREE'),
					'loader' => array(
						'serviceUrl' => '/thurly/components/thurly/crm.entity.tree/lazyload.ajax.php?&site='.SITE_ID.'&'.thurly_sessid_get(),
						'componentData' => array(
							'template' => '.default',
							'params' => array(
								'ENTITY_ID' => $this->entityID,
								'ENTITY_TYPE_NAME' => CCrmOwnerType::DealName,
							)
						)
					)
				);
				$this->arResult['TABS'][] = array(
					'id' => 'tab_event',
					'name' => Loc::getMessage('CRM_DEAL_TAB_EVENT'),
					'loader' => array(
						'serviceUrl' => '/thurly/components/thurly/crm.event.view/lazyload.ajax.php?&site'.SITE_ID.'&'.thurly_sessid_get(),
						'componentData' => array(
							'template' => '',
							'contextId' => "DEAL_{$this->entityID}_EVENT",
							'params' => array(
								'AJAX_OPTION_ADDITIONAL' => "DEAL_{$this->entityID}_EVENT",
								'ENTITY_TYPE' => 'DEAL',
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
					$listIblock = CLists::getIblockAttachedCrm(CCrmOwnerType::DealName);
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
										'ENTITY_TYPE' => CCrmOwnerType::Deal,
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
					'name' => Loc::getMessage('CRM_DEAL_TAB_QUOTE'),
					'enabled' => false
				);
				$this->arResult['TABS'][] = array(
					'id' => 'tab_invoice',
					'name' => Loc::getMessage('CRM_DEAL_TAB_INVOICES'),
					'enabled' => false
				);
				if (\Thurly\Crm\Automation\Factory::isAutomationAvailable(CCrmOwnerType::Deal))
				{
					$this->arResult['TABS'][] = array(
						'id' => 'tab_automation',
						'name' => Loc::getMessage('CRM_DEAL_TAB_AUTOMATION'),
						'enabled' => false
					);
				}
				if (CModule::IncludeModule('bizproc') && CBPRuntime::isFeatureEnabled())
				{
					$this->arResult['TABS'][] = array(
						'id' => 'tab_bizproc',
						'name' => Loc::getMessage('CRM_DEAL_TAB_BIZPROC'),
						'enabled' => false
					);
				}
				$this->arResult['TABS'][] = array(
					'id' => 'tab_event',
					'name' => Loc::getMessage('CRM_DEAL_TAB_EVENT'),
					'enabled' => false
				);
				if (CModule::IncludeModule('lists'))
				{
					$listIblock = CLists::getIblockAttachedCrm(CCrmOwnerType::DealName);
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
		}
		//endregion

		//region WAIT TARGET DATES
		$this->arResult['WAIT_TARGET_DATES'] = array(
			array('name' => 'BEGINDATE', 'caption' => \CAllCrmDeal::GetFieldCaption('BEGINDATE')),
			array('name' => 'CLOSEDATE', 'caption' => \CAllCrmDeal::GetFieldCaption('CLOSEDATE')),
		);

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
			CCrmEvent::RegisterViewEvent(CCrmOwnerType::Deal, $this->entityID, $this->userID);
		}
		//endregion

		if (!$this->isEnableRecurring && CModule::IncludeModule('thurlyos'))
		{
			CThurlyOS::initLicenseInfoPopupJS();
		}

		$this->includeComponentTemplate();
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
				'ENTITY_ID' => \CCrmDeal::GetUserFieldEntityID(),
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
						'/thurly/components/thurly/crm.deal.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#',
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

		if($this->conversionWizard !== null)
		{
			$this->entityData = array();
			$mappedUserFields = array();
			\Thurly\Crm\Entity\EntityEditor::prepareConvesionMap(
				$this->conversionWizard,
				CCrmOwnerType::Deal,
				$this->entityData,
				$mappedUserFields
			);

			foreach($mappedUserFields as $k => $v)
			{
				if(isset($this->userFields[$k]))
				{
					$this->userFields[$k]['VALUE'] = $v;
				}
			}

			if(!isset($this->entityData['CURRENCY_ID']) || $this->entityData['CURRENCY_ID'] === '')
			{
				$this->entityData['CURRENCY_ID'] = \CCrmCurrency::GetBaseCurrencyID();
			}

			$this->entityData['OPENED'] = \Thurly\Crm\Settings\CompanySettings::getCurrent()->getOpenedFlag() ? 'Y' : 'N';
		}
		else if($this->entityID <= 0)
		{
			$this->entityData = array();
			//region Default Dates
			$beginDate = time() + \CTimeZone::GetOffset();
			$time = localtime($beginDate, true);
			$beginDate -= $time['tm_sec'] + 60 * $time['tm_min'] + 3600 * $time['tm_hour'];

			$this->entityData['BEGINDATE'] = ConvertTimeStamp($beginDate, 'SHORT', SITE_ID);
			$this->entityData['CLOSEDATE'] = ConvertTimeStamp($beginDate + 7 * 86400, 'SHORT', SITE_ID);
			//endregion
			//leave OPPORTUNITY unassigned
			//$this->entityData['OPPORTUNITY'] = 0.0;
			$this->entityData['CURRENCY_ID'] = \CCrmCurrency::GetBaseCurrencyID();
			$this->entityData['OPENED'] = \Thurly\Crm\Settings\DealSettings::getCurrent()->getOpenedFlag() ? 'Y' : 'N';
			//$this->entityData['CLOSED'] = 'N';

			//region Default Responsible
			if($this->userID > 0)
			{
				$this->entityData['ASSIGNED_BY_ID'] = $this->userID;
			}
			//endregion

			//region Default Stage ID
			$stageList = $this->prepareStageList();
			if(!empty($stageList))
			{
				$requestStageId = $this->request->get('stage_id');
				if (isset($stageList[$requestStageId]))
				{
					$this->entityData['STAGE_ID'] = $requestStageId;
				}
				else
				{
					$this->entityData['STAGE_ID'] = current(array_keys($stageList));
				}
			}
			//endregion

			$typeList = $this->prepareTypeList();
			if(!empty($typeList))
			{
				$this->entityData['TYPE_ID'] = current(array_keys($typeList));
			}

			$externalCompanyID = $this->request->get('company_id');
			if($externalCompanyID > 0)
			{
				$this->entityData['COMPANY_ID'] = $externalCompanyID;
			}

			$externalContactID = $this->request->get('contact_id');
			if($externalContactID > 0)
			{
				$this->entityData['CONTACT_ID'] = $externalContactID;
			}
		}
		else
		{
			$dbResult = \CCrmDeal::GetListEx(
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

			if(isset($this->entityData['CATEGORY_ID']))
			{
				$this->arResult['CATEGORY_ID'] = $this->categoryID = (int)$this->entityData['CATEGORY_ID'];
			}

			if(!isset($this->entityData['CURRENCY_ID']) || $this->entityData['CURRENCY_ID'] === '')
			{
				$this->entityData['CURRENCY_ID'] = \CCrmCurrency::GetBaseCurrencyID();
			}

			//region Default Responsible and Stage ID for copy mode
			if($this->isCopyMode)
			{
				if($this->userID > 0)
				{
					$this->entityData['ASSIGNED_BY_ID'] = $this->userID;
				}

				$stageList = $this->prepareStageList();
				if(!empty($stageList))
				{
					$this->entityData['STAGE_ID'] = current(array_keys($stageList));
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

		if(isset($this->entityData['CATEGORY_ID']))
		{
			$this->entityData['CATEGORY_NAME'] = Thurly\Crm\Category\DealCategory::getName($this->entityData['CATEGORY_ID']);
		}

		//region User Fields
		foreach($this->userFields as $fieldName => $userField)
		{
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
		$clientInfo = array();
		$multiFildData = array();

		$companyID = isset($this->entityData['COMPANY_ID']) ? (int)$this->entityData['COMPANY_ID'] : 0;
		if($companyID > 0)
		{
			$this->prepareMultifieldData(\CCrmOwnerType::Company, $companyID, 'PHONE', $multiFildData);
			$this->prepareMultifieldData(\CCrmOwnerType::Company, $companyID, 'EMAIL', $multiFildData);

			$isEntityReadPermitted = \CCrmCompany::CheckReadPermission($companyID, $this->userPermissions);
			$companyInfo = \CCrmEntitySelectorHelper::PrepareEntityInfo(
				CCrmOwnerType::CompanyName,
				$companyID,
				array(
					'ENTITY_EDITOR_FORMAT' => true,
					'IS_HIDDEN' => !$isEntityReadPermitted,
					'REQUIRE_REQUISITE_DATA' => true,
					'REQUIRE_MULTIFIELDS' => true,
					'NAME_TEMPLATE' => \Thurly\Crm\Format\PersonNameFormatter::getFormat()
				)
			);

			$clientInfo['PRIMARY_ENTITY_DATA'] = $companyInfo;
		}

		$contactBindings = array();
		if($this->entityID > 0)
		{
			$contactBindings = \Thurly\Crm\Binding\DealContactTable::getDealBindings($this->entityID);
		}
		elseif(isset($this->entityData['CONTACT_BINDINGS']) && is_array($this->entityData['CONTACT_BINDINGS']))
		{
			$contactBindings = $this->entityData['CONTACT_BINDINGS'];
		}
		elseif(isset($this->entityData['CONTACT_ID']) && $this->entityData['CONTACT_ID'] > 0)
		{
			$contactBindings = \Thurly\Crm\Binding\EntityBinding::prepareEntityBindings(
				CCrmOwnerType::Contact,
				array($this->entityData['CONTACT_ID'])
			);
		}

		$contactIDs = \Thurly\Crm\Binding\EntityBinding::prepareEntityIDs(\CCrmOwnerType::Contact, $contactBindings);
		$clientInfo['SECONDARY_ENTITY_DATA'] = array();
		foreach($contactIDs as $contactID)
		{
			$this->prepareMultifieldData(CCrmOwnerType::Contact, $contactID, 'PHONE', $multiFildData);
			$this->prepareMultifieldData(\CCrmOwnerType::Contact, $contactID, 'EMAIL', $multiFildData);

			$isEntityReadPermitted = CCrmContact::CheckReadPermission($contactID, $this->userPermissions);
			$clientInfo['SECONDARY_ENTITY_DATA'][] = CCrmEntitySelectorHelper::PrepareEntityInfo(
				CCrmOwnerType::ContactName,
				$contactID,
				array(
					'ENTITY_EDITOR_FORMAT' => true,
					'IS_HIDDEN' => !$isEntityReadPermitted,
					'REQUIRE_REQUISITE_DATA' => true,
					'REQUIRE_MULTIFIELDS' => true,
					'REQUIRE_BINDINGS' => true,
					'NAME_TEMPLATE' => \Thurly\Crm\Format\PersonNameFormatter::getFormat()
				)
			);
		}
		if(!isset($clientInfo['PRIMARY_ENTITY_DATA']) && !empty($clientInfo['SECONDARY_ENTITY_DATA']))
		{
			$clientInfo['PRIMARY_ENTITY_DATA'] = array_shift($clientInfo['SECONDARY_ENTITY_DATA']);
		}
		$this->entityData['CLIENT_INFO'] = $clientInfo;

		//region Requisites
		$this->entityData['REQUISITE_BINDING'] = array();

		$requisiteEntityList = array();
		$requisite = new \Thurly\Crm\EntityRequisite();
		$requisiteEntityList[] = array('ENTITY_TYPE_ID' => CCrmOwnerType::Deal, 'ENTITY_ID' => $this->entityID);
		if(isset($this->entityData['COMPANY_ID']) && $this->entityData['COMPANY_ID'] > 0)
		{
			$requisiteEntityList[] = array(
				'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
				'ENTITY_ID' => $this->entityData['COMPANY_ID']
			);
		}
		if(!empty($contactBindings))
		{
			$primaryBoundEntityID = \Thurly\Crm\Binding\EntityBinding::getPrimaryEntityID(
				CCrmOwnerType::Contact,
				$contactBindings
			);
			if($primaryBoundEntityID > 0)
			{
				$requisiteEntityList[] = array(
					'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
					'ENTITY_ID' => $primaryBoundEntityID
				);
			}
		}

		$requisiteLinkInfo = $requisite->getDefaultRequisiteInfoLinked($requisiteEntityList);
		if (is_array($requisiteLinkInfo))
		{
			/* requisiteLinkInfo contains following fields: REQUISITE_ID, BANK_DETAIL_ID */
			$this->entityData['REQUISITE_BINDING'] = $requisiteLinkInfo;
		}
		//endregion

		$this->entityData['MULTIFIELD_DATA'] = $multiFildData;
		//endregion

		//region Product row
		$productRowCount = 0;
		$productRowTotalSum = 0.0;
		$productRowInfos = array();
		if($this->entityID > 0)
		{
			$dbResult = \CAllCrmProductRow::GetList(
				array('SORT' => 'ASC', 'ID'=>'ASC'),
				array(
					'OWNER_ID' => $this->entityID, 'OWNER_TYPE' => 'D'
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
		//region Recurring Deals
		if($this->entityID > 0 && $this->entityData['IS_RECURRING'] === 'Y' && $this->isEnableRecurring)
		{
			$dbResult = Recurring\Manager::getList(
				array('filter' => array('=DEAL_ID' => $this->entityID)),
				Recurring\Manager::DEAL
			);
			$recurringData = $dbResult->fetch();
			if(is_array($recurringData))
			{
				$this->entityData['RECURRING'] = $recurringData['PARAMS'];
				if (isset($recurringData['CATEGORY_ID']) || (int)$recurringData['CATEGORY_ID'] > 0)
				{
					$this->entityData['RECURRING']['CATEGORY_ID'] = $recurringData['CATEGORY_ID'];
				}
				else
				{
					$this->entityData['RECURRING']['CATEGORY_ID'] = $this->arResult['CATEGORY_ID'];
				}
			}
		}
		else
		{
			$today = new \Thurly\Main\Type\Date();
			$this->entityData['RECURRING'] = array(
				'EXECUTION_TYPE' => Recurring\Manager::MULTIPLY_EXECUTION,
				'PERIOD_DEAL' => Recurring\Calculator::SALE_TYPE_NON_ACTIVE_DATE,
				'DEAL_COUNT_BEFORE' => 0,
				'DEAL_TYPE_BEFORE' => Recurring\Calculator::SALE_TYPE_DAY_OFFSET,
				'DEAL_DATEPICKER_BEFORE' => $today->toString(),
				'REPEAT_TILL' => Recurring\Entity\Deal::NO_LIMITED,
				'LIMIT_REPEAT' => 1,
				'CATEGORY_ID' => $this->arResult['CATEGORY_ID'],
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
			$valueType = $fields['VALUE_TYPE'];
			$multiFieldComplexID = $fields['COMPLEX_ID'];

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

			//Is required for phone & email & messenger menu
			if($typeID === 'PHONE' || $typeID === 'EMAIL'
				|| ($typeID === 'IM' && preg_match('/^imol|/', $value) === 1)
			)
			{
				$formattedValue = $typeID === 'PHONE'
					? Main\PhoneNumber\Parser::getInstance()->parse($value)->format()
					: $value;

				$data[$typeID][$entityKey][] = array(
					'ID' => $fields['ID'],
					'VALUE' => $value,
					'VALUE_TYPE' => $valueType,
					'VALUE_FORMATTED' => $formattedValue,
					'COMPLEX_ID' => $multiFieldComplexID,
					'COMPLEX_NAME' => \CCrmFieldMulti::GetEntityNameByComplex($multiFieldComplexID, false)
				);
			}
			else
			{
				$data[$typeID][$entityKey][] = $value;
			}
		}
	}

	protected function prepareTypeList()
	{
		if($this->types === null)
		{
			$this->types = \CCrmStatus::GetStatusList('DEAL_TYPE');
		}
		return $this->types;
	}
	protected function prepareStageList()
	{
		if($this->stages === null)
		{
			$this->stages = array();
			$allStages = Thurly\Crm\Category\DealCategory::getStageList($this->categoryID);
			foreach ($allStages as $stageID => $stageTitle)
			{
				$permissionType = $this->isEditMode
					? \CCrmDeal::GetStageUpdatePermissionType($stageID, $this->userPermissions, $this->categoryID)
					: \CCrmDeal::GetStageCreatePermissionType($stageID, $this->userPermissions, $this->categoryID);

				if ($permissionType > BX_CRM_PERM_NONE)
				{
					$this->stages[$stageID] = $stageTitle;
				}
			}
		}
		return $this->stages;
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