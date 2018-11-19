<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

use Thurly\Crm\Synchronization\UserFieldSynchronizer;
use Thurly\Crm\Conversion\DealConversionConfig;
use Thurly\Crm\Conversion\DealConversionWizard;
use Thurly\Crm\Recurring;

if (!CModule::IncludeModule('crm'))
{
	return;
}
/*
 * ONLY 'POST' METHOD SUPPORTED
 * SUPPORTED ACTIONS:
 * 'GET_DEFAULT_SECONDARY_ENTITIES'
 */
global $DB, $APPLICATION, $USER_FIELD_MANAGER;
\Thurly\Main\Localization\Loc::loadMessages(__FILE__);
if(!function_exists('__CrmDealDetailsEndJsonResonse'))
{
	function __CrmDealDetailsEndJsonResonse($result)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		if(!empty($result))
		{
			echo CUtil::PhpToJSObject($result);
		}
		if(!defined('PUBLIC_AJAX_MODE'))
		{
			define('PUBLIC_AJAX_MODE', true);
		}
		require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');
		die();
	}
}

if (!CCrmSecurityHelper::IsAuthorized() || !check_thurly_sessid() || $_SERVER['REQUEST_METHOD'] != 'POST')
{
	return;
}

CUtil::JSPostUnescape();
$APPLICATION->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

$currentUserID = CCrmSecurityHelper::GetCurrentUserID();
$currentUserPermissions =  CCrmPerms::GetCurrentUserPermissions();

$action = isset($_POST['ACTION']) ? $_POST['ACTION'] : '';
if($action === '' && isset($_POST['MODE']))
{
	$action = $_POST['MODE'];
}
if($action === '')
{
	__CrmDealDetailsEndJsonResonse(array('ERROR'=>'ACTION IS NOT DEFINED!'));
}
if($action === 'GET_FORMATTED_SUM')
{
	$sum = isset($_POST['SUM']) ? $_POST['SUM'] : 0.0;
	$currencyID = isset($_POST['CURRENCY_ID']) ? $_POST['CURRENCY_ID'] : '';
	if($currencyID === '')
	{
		$currencyID = CCrmCurrency::GetBaseCurrencyID();
	}

	__CrmDealDetailsEndJsonResonse(
		array(
			'FORMATTED_SUM' => CCrmCurrency::MoneyToString($sum, $currencyID, '#'),
			'FORMATTED_SUM_WITH_CURRENCY' => CCrmCurrency::MoneyToString($sum, $currencyID, '')
		)
	);
}
elseif($action === 'MOVE_TO_CATEGORY')
{
	$ID = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;
	if($ID <= 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR'=>'ENTITY ID IS NOT FOUND!'));
	}

	if(!\CCrmDeal::CheckUpdatePermission($ID, $currentUserPermissions))
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR'=>'PERMISSION DENIED!'));
	}

	$newCategoryID =  isset($_POST['CATEGORY_ID']) ? max((int)$_POST['CATEGORY_ID'], 0) : 0;
	if($newCategoryID < 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR'=>'CATEGORY ID IS NOT FOUND!'));
	}

	if(!\CCrmDeal::CheckCreatePermission($currentUserPermissions, $newCategoryID))
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR'=>'PERMISSION DENIED!'));
	}

	$DB->StartTransaction();
	try
	{
		$error = \CCrmDeal::MoveToCategory($ID, $newCategoryID);
		if($error !== \Thurly\Crm\Category\DealCategoryChangeError::NONE)
		{
			__CrmDealDetailsEndJsonResonse(
				array('ERROR' => GetMessage('CRM_DEAL_MOVE_TO_CATEGORY_ERROR'))
			);
		}
		Thurly\Crm\Automation\Factory::runOnStatusChanged(CCrmOwnerType::Deal, $ID);
		$DB->Commit();
	}
	catch(Exception $e)
	{
		$DB->Rollback();
	}

	__CrmDealDetailsEndJsonResonse(array('CATEGORY_ID' => $newCategoryID));
}
elseif($action === 'SAVE')
{
	$ID = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;
	$categoryID =  isset($_POST['CATEGORY_ID']) ? max((int)$_POST['CATEGORY_ID'], 0) : 0;
	if(($ID > 0 && !\CCrmDeal::CheckUpdatePermission($ID, $currentUserPermissions))
		|| ($ID === 0 && !\CCrmDeal::CheckCreatePermission($currentUserPermissions, $categoryID))
	)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR'=>'PERMISSION DENIED!'));
	}

	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	$sourceEntityID =  isset($params['DEAL_ID']) ? (int)$params['DEAL_ID'] : 0;

	$isNew = $ID === 0;
	$isCopyMode = $isNew && $sourceEntityID > 0;
	//TODO: Implement external mode
	$isExternal = false;

	$previousFields = !$isNew ? \CCrmDeal::GetByID($ID, false) : null;

	$fields = array();
	$fieldsInfo = \CCrmDeal::GetFieldsInfo();
	$userType = new \CCrmUserType($GLOBALS['USER_FIELD_MANAGER'], \CCrmDeal::GetUserFieldEntityID());
	$userType->PrepareFieldsInfo($fieldsInfo);

	$sourceFields = array();
	if($sourceEntityID > 0)
	{
		$dbResult = \CCrmDeal::GetListEx(
			array(),
			array('=ID' => $sourceEntityID, 'CHECK_PERMISSIONS' => 'N'),
			false,
			false,
			array('*', 'UF_*')
		);
		$sourceFields = $dbResult->Fetch();
		if(!is_array($sourceFields))
		{
			$sourceFields = array();
		}
	}

	foreach(array_keys($fieldsInfo) as $fieldName)
	{
		if(isset($_POST[$fieldName]))
		{
			$fields[$fieldName] = $_POST[$fieldName];
		}
	}

	if($isNew)
	{
		$fields['CATEGORY_ID'] = $categoryID;
	}
	else
	{
		unset($fields['CATEGORY_ID']);
	}

	//region CLIENT
	$primaryClientTypeName = isset($_POST['CLIENT_PRIMARY_ENTITY_TYPE']) ? $_POST['CLIENT_PRIMARY_ENTITY_TYPE'] : '';
	$primaryClientTypeID = \CCrmOwnerType::ResolveID($primaryClientTypeName);

	$secondaryClientTypeName = isset($_POST['CLIENT_SECONDARY_ENTITY_TYPE']) ? $_POST['CLIENT_SECONDARY_ENTITY_TYPE'] : '';
	$secondaryClientTypeID = \CCrmOwnerType::ResolveID($secondaryClientTypeName);

	$primaryClientID = 0;

	$boundSecondaryClientIDs = array();
	$unboundSecondaryClientIDs = array();
	if($primaryClientTypeID !== \CCrmOwnerType::Undefined && $secondaryClientTypeID !== \CCrmOwnerType::Undefined)
	{
		$companyID = 0;
		$contactIDs = array();

		$primaryClientID = isset($_POST['CLIENT_PRIMARY_ENTITY_ID']) ? (int)$_POST['CLIENT_PRIMARY_ENTITY_ID'] : 0;
		if($primaryClientID < 0)
		{
			$primaryClientID = 0;
		}

		if($primaryClientID > 0)
		{
			if($primaryClientTypeID === \CCrmOwnerType::Company)
			{
				$companyID = $primaryClientID;
			}
			elseif($primaryClientTypeID === \CCrmOwnerType::Contact)
			{
				$contactIDs[$primaryClientID] = true;
			}
		}

		$secondaryClientIDs = isset($_POST['CLIENT_SECONDARY_ENTITY_IDS']) ? $_POST['CLIENT_SECONDARY_ENTITY_IDS'] : '';
		$secondaryClientIDs = $secondaryClientIDs !== '' ? explode(',', $secondaryClientIDs) : array();

		foreach($secondaryClientIDs as $clientID)
		{
			$contactIDs[$clientID] = true;
		}
		$contactIDs = array_keys($contactIDs);

		$fields['COMPANY_ID'] = $companyID;
		$fields['CONTACT_IDS'] = $contactIDs;

		$s = isset($_POST['CLIENT_UBOUND_SECONDARY_ENTITY_IDS']) ? $_POST['CLIENT_UBOUND_SECONDARY_ENTITY_IDS'] : '';
		$unboundSecondaryClientIDs = $s !== '' ? explode(',', $s) : array();

		$s = isset($_POST['CLIENT_BOUND_SECONDARY_ENTITY_IDS']) ? $_POST['CLIENT_BOUND_SECONDARY_ENTITY_IDS'] : '';
		$boundSecondaryClientIDs = $s !== '' ? explode(',', $s) : array();
	}
	//endregion

	//region REQUISITE_ID & BANK_DETAIL_ID
	$requisiteID = isset($_POST['REQUISITE_ID']) ? max((int)$_POST['REQUISITE_ID'], 0) : 0;
	$bankDetailID = isset($_POST['BANK_DETAIL_ID']) ? max((int)$_POST['BANK_DETAIL_ID'], 0) : 0;
	//endregion

	//region PRODUCT ROWS
	$enableProductRows = !array_key_exists('DEAL_PRODUCT_DATA_INVALIDATE', $_POST) && array_key_exists('DEAL_PRODUCT_DATA', $_POST);
	$productRows = array();
	$productRowSettings = array();
	if($enableProductRows)
	{
		if(isset($_POST['DEAL_PRODUCT_DATA']) && $_POST['DEAL_PRODUCT_DATA'] !== '')
		{
			$productRows = \CUtil::JsObjectToPhp($_POST['DEAL_PRODUCT_DATA']);
		}

		if(!is_array($productRows))
		{
			$productRows = array();
		}

		if(!empty($productRows))
		{
			if($isCopyMode)
			{
				for($index = 0, $qty = count($productRows); $index < $qty; $index++)
				{
					unset($productRows[$index]['ID']);
				}
			}

			$calculationParams = $fields;
			if(!isset($calculationParams['CURRENCY_ID']))
			{
				if(is_array($previousFields) && isset($previousFields['CURRENCY_ID']))
				{
					$calculationParams['CURRENCY_ID'] = $previousFields['CURRENCY_ID'];
				}
				elseif(isset($sourceFields['CURRENCY_ID']))
				{
					$calculationParams['CURRENCY_ID'] = $sourceFields['CURRENCY_ID'];
				}
				else
				{
					$calculationParams['CURRENCY_ID'] = CCrmCurrency::GetBaseCurrencyID();
				}
			}

			$totals = \CCrmProductRow::CalculateTotalInfo('D', 0, false, $calculationParams, $productRows);
			$fields['OPPORTUNITY'] = isset($totals['OPPORTUNITY']) ? $totals['OPPORTUNITY'] : 0.0;
			$fields['TAX_VALUE'] = isset($totals['TAX_VALUE']) ? $totals['TAX_VALUE'] : 0.0;
		}
		else
		{
			$fields['TAX_VALUE'] = 0.0;
			if(!isset($fields['OPPORTUNITY']) && $isNew)
			{
				$fields['OPPORTUNITY'] = 0.0;
			}
		}

		if(isset($_POST['DEAL_PRODUCT_DATA_SETTINGS']) && $_POST['DEAL_PRODUCT_DATA_SETTINGS'] !== '')
		{
			$settings = \CUtil::JsObjectToPhp($_POST['DEAL_PRODUCT_DATA_SETTINGS']);
			if(is_array($settings))
			{
				$productRowSettings['ENABLE_DISCOUNT'] = isset($settings['ENABLE_DISCOUNT'])
					? $settings['ENABLE_DISCOUNT'] === 'Y' : false;
				$productRowSettings['ENABLE_TAX'] = isset($settings['ENABLE_TAX'])
					? $settings['ENABLE_TAX'] === 'Y' : false;
			}
		}
	}

	//endregion

	//region RECURRING
	if (
		(
			$_POST['IS_RECURRING'] === 'N'
			&&
			(
				($_POST['RECURRING']['PERIOD_DEAL'] != Recurring\Calculator::SALE_TYPE_NON_ACTIVE_DATE
				&& (int)$_POST['RECURRING']['EXECUTION_TYPE'] === Recurring\Manager::MULTIPLY_EXECUTION)
				|| (int)$_POST['RECURRING']['EXECUTION_TYPE'] !== Recurring\Manager::MULTIPLY_EXECUTION
			)
		)
		|| ($_POST['IS_RECURRING'] === 'Y')
	)
	{
		if (!Recurring\Manager::isAllowedExpose(Recurring\Manager::DEAL))
			__CrmDealDetailsEndJsonResonse(array('ERROR' => "RECURRING DEALS IS RESTRICTED"));

		$fields['RECURRING'] = $_POST['RECURRING'];

		if (isset($fields['COMMENTS']))
		{
			$fields['COMMENTS'] = \Thurly\Crm\Format\TextHelper::sanitizeHtml($fields['COMMENTS']);
		}

		$limit = Recurring\Entity\Deal::NO_LIMITED;
		$limitDate = null;

		$recurringRow = Recurring\Manager::getList(
			array(
				'filter' => array("=DEAL_ID" => $ID),
				'select' => array('ID')
			),
			Recurring\Manager::DEAL
		);
		$recurring = $recurringRow->fetch();

		if ($_POST['RECURRING']['REPEAT_TILL'] === Recurring\Entity\Deal::LIMITED_BY_TIMES && (int)$fields['RECURRING']['LIMIT_REPEAT'] > 0)
		{
			$limit = Recurring\Entity\Deal::LIMITED_BY_TIMES;
		}
		elseif(
			$_POST['RECURRING']['REPEAT_TILL'] === Recurring\Entity\Deal::LIMITED_BY_DATE
			&& strlen($_POST['RECURRING']['END_DATE']) > 0
		)
		{
			$limitDate = new \Thurly\Main\Type\Date($_POST['RECURRING']['END_DATE']);
			$limit = Recurring\Entity\Deal::LIMITED_BY_DATE;
		}

		if (
			strlen($_POST['RECURRING']['DEAL_DATEPICKER_BEFORE']) > 0
			&& (int)$_POST['RECURRING']['EXECUTION_TYPE'] === Recurring\Manager::SINGLE_EXECUTION
		)
		{
			$startDate = new \Thurly\Main\Type\Date($_POST['RECURRING']['DEAL_DATEPICKER_BEFORE']);
			$limit = Recurring\Entity\Deal::LIMITED_BY_DATE;
			$limitDateBefore = Recurring\Calculator::getNextDate($_POST['RECURRING'] ,clone($startDate));

			if (
				($limitDate instanceof \Thurly\Main\Type\Date && $limitDateBefore->getTimestamp() < $limitDate->getTimestamp())
				|| empty($limitDate)
			)
			{
				$limitDate = $limitDateBefore;
			}
		}
		else
		{
			$startDate = new \Thurly\Main\Type\Date();
		}

		$categoryId = 0;
		if (isset($fields['RECURRING']['CATEGORY_ID']))
		{
			$categoryId = (int)$fields['RECURRING']['CATEGORY_ID'];
		}
		elseif ($categoryID > 0)
		{
			$categoryId = (int)$categoryID;
		}
		$categoryId = max($categoryId, 0);

		// RECURRING_SWITCHER is used for old deal edit template
		$fields['RECURRING']['RECURRING_SWITCHER'] = 'Y';
		$recurringFields = array(
			"START_DATE" => $startDate,
			"LIMIT_DATE" => $limitDate,
			"LIMIT_REPEAT" => $fields['RECURRING']['LIMIT_REPEAT'],
			"IS_LIMIT" => $limit,
			"CATEGORY_ID" => $categoryId,
			"PARAMS" => $fields['RECURRING']
		);

		if (is_array($recurring) && !$isNew)
		{
			Recurring\Manager::update($recurring['ID'],$recurringFields,Recurring\Manager::DEAL);
			unset($fields['RECURRING']);
		}
		else
		{
			if ($isNew)
			{
				unset($fields['RECURRING']);
				$dealFields = $fields;
			}
			else
			{
				$dealFields = \CCrmDeal::GetByID($ID);

				$userType = new \CCrmUserType($USER_FIELD_MANAGER, \CCrmDeal::GetUserFieldEntityID());
				$userFields = $userType->GetEntityFields($ID);

				foreach($userFields as $key => $field)
				{
					$dealFields[$key] = $field['VALUE'];
				}

				$isNew = true;
			}
			$result = Recurring\Manager::createEntity($dealFields,	$recurringFields, Recurring\Manager::DEAL);

			if (is_array($productRows) && !empty($productRows))
			{
				foreach ($productRows as &$product)
				{
					unset($product['ID'], $product['OWNER_ID']);
				}
			}

			if($isNew)
			{
				if ($result->isSuccess())
				{
					$resultData = $result->getData();
					$ID = $resultData['DEAL_ID'];
				}
				else
				{
					$messages = $result->getErrorMessages();
					__CrmDealDetailsEndJsonResonse(array('ERROR' => end($messages)));
				}
			}
			$isRecurringSaving = true;
		}
	}
	//endregion

	$conversionWizard = null;
	if(isset($params['LEAD_ID']) && $params['LEAD_ID'] > 0)
	{
		$leadID = (int)$params['LEAD_ID'];
		$fields['LEAD_ID'] = $leadID;
		$conversionWizard = \Thurly\Crm\Conversion\LeadConversionWizard::load($leadID);
	}
	elseif(isset($params['QUOTE_ID']) && $params['QUOTE_ID'] > 0)
	{
		$quoteID = (int)$params['QUOTE_ID'];
		$fields['QUOTE_ID'] = $quoteID;
		$conversionWizard = \Thurly\Crm\Conversion\QuoteConversionWizard::load($quoteID);
	}

	if($conversionWizard !== null)
	{
		$conversionWizard->setSliderEnabled(true);
		$conversionWizard->prepareDataForSave(CCrmOwnerType::Deal, $fields);
	}

	if(!empty($fields) || $enableProductRows)
	{
		if (!empty($fields) && !isset($isRecurringSaving))
		{
			if(isset($fields['ASSIGNED_BY_ID']) && $fields['ASSIGNED_BY_ID'] > 0)
			{
				\Thurly\Crm\Entity\EntityEditor::registerSelectedUser($fields['ASSIGNED_BY_ID']);
			}

			if($isCopyMode)
			{
				if(!isset($fields['ASSIGNED_BY_ID']))
				{
					$fields['ASSIGNED_BY_ID'] = $currentUserID;
				}

				$merger = new \Thurly\Crm\Merger\DealMerger($currentUserID, false);
				$merger->mergeFields($sourceFields, $fields, true);
			}

			if (isset($fields['COMMENTS']))
			{
				$fields['COMMENTS'] = \Thurly\Crm\Format\TextHelper::sanitizeHtml($fields['COMMENTS']);
			}

			$entity = new \CCrmDeal(false);
			if($isNew)
			{
				$now = time() + CTimeZone::GetOffset();
				if(!isset($fields['TYPE_ID']))
				{
					$fields['TYPE_ID'] = \CCrmStatus::GetFirstStatusID('DEAL_TYPE');
				}

				if(!isset($fields['BEGINDATE']))
				{
					$fields['BEGINDATE'] = ConvertTimeStamp($now, 'SHORT', SITE_ID);
				}

				if(!isset($fields['CLOSEDATE']))
				{
					$fields['CLOSEDATE'] = ConvertTimeStamp($now + (7 * 86400), 'SHORT', SITE_ID);
				}

				if(!isset($fields['OPENED']))
				{
					$fields['OPENED'] = \Thurly\Crm\Settings\DealSettings::getCurrent()->getOpenedFlag() ? 'Y' : 'N';
				}

				if(!isset($fields['CURRENCY_ID']))
				{
					$fields['CURRENCY_ID'] = CCrmCurrency::GetBaseCurrencyID();
				}

				$fields['EXCH_RATE'] = CCrmCurrency::GetExchangeRate($fields['CURRENCY_ID']);

				$ID = $entity->Add($fields, true, array('REGISTER_SONET_EVENT' => true));
				if ($ID <= 0)
				{
					__CrmDealDetailsEndJsonResonse(array('ERROR' => $entity->LAST_ERROR));
				}
			}
			else
			{
				if(isset($fields['OPPORTUNITY']) || isset($fields['CURRENCY_ID']))
				{
					if(!isset($fields['OPPORTUNITY']))
					{
						if(is_array($previousFields) && isset($previousFields['OPPORTUNITY']))
						{
							$fields['OPPORTUNITY'] = $previousFields['OPPORTUNITY'];
						}
						elseif(isset($sourceFields['OPPORTUNITY']))
						{
							$fields['OPPORTUNITY'] = $sourceFields['OPPORTUNITY'];
						}
					}

					if(!isset($fields['CURRENCY_ID']))
					{
						if(is_array($previousFields) && isset($previousFields['CURRENCY_ID']))
						{
							$fields['CURRENCY_ID'] = $previousFields['CURRENCY_ID'];
						}
						elseif(isset($sourceFields['CURRENCY_ID']))
						{
							$fields['CURRENCY_ID'] = $sourceFields['CURRENCY_ID'];
						}
						else
						{
							$fields['CURRENCY_ID'] = CCrmCurrency::GetBaseCurrencyID();
						}
					}

					$fields['EXCH_RATE'] = CCrmCurrency::GetExchangeRate($fields['CURRENCY_ID']);
				}

				if (!$entity->Update($ID, $fields, true, true, array('REGISTER_SONET_EVENT' => true)))
				{
					__CrmDealDetailsEndJsonResonse(array('ERROR' => $entity->LAST_ERROR));
				}
			}
		}

		if(!$isExternal && $enableProductRows && (!$isNew || !empty($productRows)))
		{
			if(!\CCrmDeal::SaveProductRows($ID, $productRows, true, true, false))
			{
				__CrmDealDetailsEndJsonResonse(array('ERROR' => GetMessage('CRM_DEAL_PRODUCT_ROWS_SAVING_ERROR')));
			}
		}

		if(!empty($productRowSettings))
		{
			if(!$isNew)
			{
				$productRowSettings = array_merge(
					\CCrmProductRow::LoadSettings('D', $ID),
					$productRowSettings
				);
			}
			\CCrmProductRow::SaveSettings('D', $ID, $productRowSettings);
		}

		if($primaryClientID > 0 && $primaryClientTypeID === \CCrmOwnerType::Company)
		{
			if(!empty($unboundSecondaryClientIDs))
			{
				\Thurly\Crm\Binding\ContactCompanyTable::unbindContactIDs($primaryClientID, $unboundSecondaryClientIDs);
			}
			if(!empty($boundSecondaryClientIDs))
			{
				\Thurly\Crm\Binding\ContactCompanyTable::bindContactIDs($primaryClientID, $boundSecondaryClientIDs);
			}
		}

		if($requisiteID > 0)
		{
			\Thurly\Crm\Requisite\EntityLink::register(
				CCrmOwnerType::Deal,
				$ID,
				$requisiteID,
				$bankDetailID
			);
		}
		elseif(!$isNew)
		{
			\Thurly\Crm\Requisite\EntityLink::unregister(
				CCrmOwnerType::Deal,
				$ID
			);
		}

		$arErrors = array();
		if (!isset($isRecurringSaving))
		{
			\CCrmBizProcHelper::AutoStartWorkflows(
				\CCrmOwnerType::Deal,
				$ID,
				$isNew ? \CCrmBizProcEventType::Create : \CCrmBizProcEventType::Edit,
				$arErrors,
				isset($_POST['bizproc_parameters']) ? $_POST['bizproc_parameters'] : null
			);

			if($isNew)
			{
				\Thurly\Crm\Automation\Factory::runOnAdd(\CCrmOwnerType::Deal, $ID);
			}
			else if(is_array($previousFields)
				&& isset($fields['STAGE_ID'])
				&& isset($previousFields['STAGE_ID'])
				&& $fields['STAGE_ID'] !== $previousFields['STAGE_ID']
			)
			{
				\Thurly\Crm\Automation\Factory::runOnStatusChanged(\CCrmOwnerType::Deal, $ID);
			}
		}

		if($conversionWizard !== null)
		{
			$conversionWizard->attachNewlyCreatedEntity(\CCrmOwnerType::DealName, $ID);
			$url = $conversionWizard->getRedirectUrl();
			if($url !== '')
			{
				$responseData = array('ENTITY_ID' => $ID, 'REDIRECT_URL' => $url);
				$eventParams = $conversionWizard->getClientEventParams();
				if(is_array($eventParams))
				{
					$responseData['EVENT_PARAMS'] = $eventParams;
				}

				__CrmDealDetailsEndJsonResonse($responseData);
			}
		}
	}

	CThurlyComponent::includeComponentClass('thurly:crm.deal.details');
	$component = new CCrmDealDetailsComponent();
	$component->initializeParams(
		isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array()
	);
	$component->setEntityID($ID);
	$result = array('ENTITY_ID' => $ID, 'ENTITY_DATA' => $component->prepareEntityData());
	if($isNew)
	{
		$result['REDIRECT_URL'] = \CCrmOwnerType::GetDetailsUrl(
			\CCrmOwnerType::Deal,
			$ID,
			false,
			array('OPEN_IN_SLIDER' => true)
		);
	}

	__CrmDealDetailsEndJsonResonse($result);
}
elseif($action === 'CONVERT')
{
	$entityID = isset($_POST['ENTITY_ID']) ? (int)$_POST['ENTITY_ID'] : 0;
	if($entityID <= 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => array('MESSAGE' => GetMessage('CRM_DEAL_CONVERSION_ID_NOT_DEFINED'))));
	}

	if(!\CCrmDeal::Exists($entityID))
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => array('MESSAGE' => GetMessage('CRM_DEAL_CONVERSION_NOT_FOUND'))));
	}

	if(!\CCrmDeal::CheckReadPermission($entityID, $currentUserPermissions))
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => array('MESSAGE' => GetMessage('CRM_DEAL_CONVERSION_ACCESS_DENIED'))));
	}

	$configParams = isset($_POST['CONFIG']) && is_array($_POST['CONFIG']) ? $_POST['CONFIG'] : null;
	if(is_array($configParams))
	{
		$config = new DealConversionConfig();
		$config->fromJavaScript($configParams);
		$config->save();
	}
	else
	{
		$config = DealConversionConfig::load();
		if($config === null)
		{
			$config = DealConversionConfig::getDefault();
		}
	}


	if(!isset($_POST['ENABLE_SYNCHRONIZATION']) || $_POST['ENABLE_SYNCHRONIZATION'] !== 'Y')
	{
		$needForSync = false;
		$entityConfigs = $config->getItems();
		$syncFieldNames = array();
		foreach($entityConfigs as $entityTypeID => $entityConfig)
		{
			$entityTypeName = CCrmOwnerType::ResolveName($entityTypeID);
			if(!CCrmAuthorizationHelper::CheckCreatePermission($entityTypeName, $currentUserPermissions)
				&& !CCrmAuthorizationHelper::CheckUpdatePermission($entityTypeName, 0, $currentUserPermissions))
			{
				continue;
			}

			$enableSync = $entityConfig->isActive();
			if($enableSync)
			{
				$syncFields = UserFieldSynchronizer::getSynchronizationFields(CCrmOwnerType::Deal, $entityTypeID);
				$enableSync = !empty($syncFields);
				foreach($syncFields as $field)
				{
					$syncFieldNames[$field['ID']] = UserFieldSynchronizer::getFieldLabel($field);
				}
			}

			if($enableSync && !$needForSync)
			{
				$needForSync = true;
			}
			$entityConfig->enableSynchronization($enableSync);
		}

		if($needForSync)
		{
			__CrmDealDetailsEndJsonResonse(
				array(
					'REQUIRED_ACTION' => array(
						'NAME' => 'SYNCHRONIZE',
						'DATA' => array(
							'CONFIG' => $config->toJavaScript(),
							'FIELD_NAMES' => array_values($syncFieldNames)
						)
					)
				)
			);
		}
	}
	else
	{
		$entityConfigs = $config->getItems();
		foreach($entityConfigs as $entityTypeID => $entityConfig)
		{
			$entityTypeName = CCrmOwnerType::ResolveName($entityTypeID);
			if(!CCrmAuthorizationHelper::CheckCreatePermission($entityTypeName, $currentUserPermissions)
				&& !CCrmAuthorizationHelper::CheckUpdatePermission($entityTypeName, 0, $currentUserPermissions))
			{
				continue;
			}

			if(!$entityConfig->isActive())
			{
				continue;
			}

			if(!UserFieldSynchronizer::needForSynchronization(CCrmOwnerType::Deal, $entityTypeID))
			{
				continue;
			}

			if($entityConfig->isSynchronizationEnabled())
			{
				UserFieldSynchronizer::synchronize(\CCrmOwnerType::Deal, $entityTypeID);
			}
			else
			{
				UserFieldSynchronizer::markAsSynchronized(\CCrmOwnerType::Deal, $entityTypeID);
			}
		}
	}

	DealConversionWizard::remove($entityID);
	$wizard = new DealConversionWizard($entityID, $config);
	$wizard->setOriginUrl(isset($_POST['ORIGIN_URL']) ? $_POST['ORIGIN_URL'] : '');
	$wizard->setSliderEnabled(true);

	if($wizard->execute())
	{
		__CrmDealDetailsEndJsonResonse(
			array(
				'DATA' => array(
					'URL' => $wizard->getRedirectUrl(),
					'IS_FINISHED' => $wizard->isFinished() ? 'Y' : 'N'
				)
			)
		);
	}
	else
	{
		$url = $wizard->getRedirectUrl();
		if($url !== '')
		{
			__CrmDealDetailsEndJsonResonse(
				array(
					'DATA' => array(
						'URL' => $url,
						'IS_FINISHED' => $wizard->isFinished() ? 'Y' : 'N'
					)
				)
			);
		}
		else
		{
			__CrmDealDetailsEndJsonResonse(array('ERROR' => array('MESSAGE' => $wizard->getErrorText())));
		}
	}
}
elseif($action === 'DELETE')
{
	$ID = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;
	if($ID <= 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => GetMessage('CRM_DEAL_CONVERSION_NOT_FOUND')));
	}

	$categoryID = CCrmDeal::GetCategoryID($ID);
	$permissionAttrs = \CCrmDeal::GetPermissionAttributes(array($ID), $categoryID);

	if(!\CCrmDeal::CheckDeletePermission(
		$ID,
		$currentUserPermissions,
		$categoryID,
		array('ENTITY_ATTRS' => $permissionAttrs))
	)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => GetMessage('CRM_DEAL_CONVERSION_ACCESS_DENIED')));
	}

	$bizProc = new CCrmBizProc('DEAL');
	if (!$bizProc->Delete($ID, $permissionAttrs, array('DealCategoryId' => $categoryID)))
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => $bizProc->LAST_ERROR));
	}

	$entity = new \CCrmDeal(false);
	if (!$entity->Delete($ID, array('PROCESS_BIZPROC' => false)))
	{
		/** @var CApplicationException $ex */
		$ex = $APPLICATION->GetException();
		__CrmDealDetailsEndJsonResonse(
			array(
				'ERROR' => ($ex instanceof CApplicationException) ? $ex->GetString() : GetMessage('CRM_DEAL_DELETION_ERROR')
			)
		);
	}
	__CrmDealDetailsEndJsonResonse(array('ENTITY_ID' => $ID));
}
elseif($action === 'GET_BINDING_INFOS')
{
	$userPermissions = CCrmPerms::GetCurrentUserPermissions();
	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	$entityTypeName = isset($params['ENTITY_TYPE_NAME']) ? $params['ENTITY_TYPE_NAME'] : '';
	if($entityTypeName === '')
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Entity type is not specified.'));
	}

	$entityTypeID = CCrmOwnerType::ResolveID($entityTypeName);
	if($entityTypeID !== CCrmOwnerType::Contact)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Entity type is not supported in current context.'));
	}

	$ownerTypeName = isset($params['OWNER_TYPE_NAME']) ? $params['OWNER_TYPE_NAME'] : '';
	if($ownerTypeName === '')
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Owner type is not specified.'));
	}

	$ownerTypeID = CCrmOwnerType::ResolveID($ownerTypeName);
	if($ownerTypeID === CCrmOwnerType::Undefined)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Undefined owner type is specified.'));
	}

	$ownerID = isset($params['OWNER_ID']) ? (int)$params['OWNER_ID'] : 0;
	if($ownerID <= 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Owner ID is not specified.'));
	}

	if(!CCrmAuthorizationHelper::CheckReadPermission($ownerTypeID, $ownerID, $userPermissions))
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Access denied.'));
	}

	$entityIDs = null;
	if($ownerTypeID === CCrmOwnerType::Company)
	{
		$entityIDs = \Thurly\Crm\Binding\ContactCompanyTable::getCompanyContactIDs($ownerID);
	}

	$nameTemplate = isset($params['NAME_TEMPLATE'])
		? $params['NAME_TEMPLATE'] : \Thurly\Crm\Format\PersonNameFormatter::getFormat();

	$data = array();
	foreach($entityIDs as $entityID)
	{
		$isReadPermitted = CCrmContact::CheckReadPermission($entityID, $userPermissions);
		$data[] = CCrmEntitySelectorHelper::PrepareEntityInfo(
			CCrmOwnerType::ContactName,
			$entityID,
			array(
				'ENTITY_EDITOR_FORMAT' => true,
				'REQUIRE_REQUISITE_DATA' => $isReadPermitted,
				'REQUIRE_MULTIFIELDS' => $isReadPermitted,
				'NAME_TEMPLATE' => $nameTemplate
			)
		);
	}
	__CrmDealDetailsEndJsonResonse(array('DATA' => $data));
}
elseif($action === 'ADD_BINDING')
{
	$userPermissions = CCrmPerms::GetCurrentUserPermissions();
	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	$entityTypeName = isset($params['ENTITY_TYPE_NAME']) ? $params['ENTITY_TYPE_NAME'] : '';
	if($entityTypeName === '')
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Entity type is not specified.'));
	}

	$entityTypeID = CCrmOwnerType::ResolveID($entityTypeName);
	if($entityTypeID !== CCrmOwnerType::Contact)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Entity type is not supported in current context.'));
	}

	$entityID = isset($params['ENTITY_ID']) ? (int)$params['ENTITY_ID'] : 0;
	if($entityID <= 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Entity ID is not specified.'));
	}

	$ownerTypeName = isset($params['OWNER_TYPE_NAME']) ? $params['OWNER_TYPE_NAME'] : '';
	if($ownerTypeName === '')
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Owner type is not specified.'));
	}

	$ownerTypeID = CCrmOwnerType::ResolveID($ownerTypeName);
	if($ownerTypeID === CCrmOwnerType::Undefined)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Undefined owner type is specified.'));
	}

	$ownerID = isset($params['OWNER_ID']) ? (int)$params['OWNER_ID'] : 0;
	if($ownerID <= 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Owner ID is not specified.'));
	}

	if(!CCrmAuthorizationHelper::CheckUpdatePermission($ownerTypeID, $ownerID, $userPermissions))
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Access denied.'));
	}

	$entityIDs = null;
	if($ownerTypeID !== CCrmOwnerType::Company)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Owner type is not supported.'));
	}

	\Thurly\Crm\Binding\ContactCompanyTable::bindContactIDs($ownerID, array($entityID));
	$entityIDs = \Thurly\Crm\Binding\ContactCompanyTable::getCompanyContactIDs($ownerID);

	$nameTemplate = isset($params['NAME_TEMPLATE'])
		? $params['NAME_TEMPLATE'] : \Thurly\Crm\Format\PersonNameFormatter::getFormat();

	$data = array();
	foreach($entityIDs as $entityID)
	{
		$isReadPermitted = CCrmContact::CheckReadPermission($entityID, $userPermissions);
		$data[] = CCrmEntitySelectorHelper::PrepareEntityInfo(
			CCrmOwnerType::ContactName,
			$entityID,
			array(
				'ENTITY_EDITOR_FORMAT' => true,
				'REQUIRE_REQUISITE_DATA' => $isReadPermitted,
				'REQUIRE_MULTIFIELDS' => $isReadPermitted,
				'NAME_TEMPLATE' => $nameTemplate
			)
		);
	}
	__CrmDealDetailsEndJsonResonse(array('DATA' => $data));
}
elseif($action === 'DELETE_BINDING')
{
	$userPermissions = CCrmPerms::GetCurrentUserPermissions();
	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	$entityTypeName = isset($params['ENTITY_TYPE_NAME']) ? $params['ENTITY_TYPE_NAME'] : '';
	if($entityTypeName === '')
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Entity type is not specified.'));
	}

	$entityTypeID = CCrmOwnerType::ResolveID($entityTypeName);
	if($entityTypeID !== CCrmOwnerType::Contact)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Entity type is not supported in current context.'));
	}

	$entityID = isset($params['ENTITY_ID']) ? (int)$params['ENTITY_ID'] : 0;
	if($entityID <= 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Entity ID is not specified.'));
	}

	$ownerTypeName = isset($params['OWNER_TYPE_NAME']) ? $params['OWNER_TYPE_NAME'] : '';
	if($ownerTypeName === '')
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Owner type is not specified.'));
	}

	$ownerTypeID = CCrmOwnerType::ResolveID($ownerTypeName);
	if($ownerTypeID === CCrmOwnerType::Undefined)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Undefined owner type is specified.'));
	}

	$ownerID = isset($params['OWNER_ID']) ? (int)$params['OWNER_ID'] : 0;
	if($ownerID <= 0)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Owner ID is not specified.'));
	}

	if(!CCrmAuthorizationHelper::CheckUpdatePermission($ownerTypeID, $ownerID, $userPermissions))
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Access denied.'));
	}

	$entityIDs = null;
	if($ownerTypeID !== CCrmOwnerType::Company)
	{
		__CrmDealDetailsEndJsonResonse(array('ERROR' => 'Owner type is not supported.'));
	}

	\Thurly\Crm\Binding\ContactCompanyTable::unbindContactIDs($ownerID, array($entityID));
	__CrmDealDetailsEndJsonResonse(
		array(
			'DATA' => array(
				'ENTITY_TYPE_NAME' => CCrmOwnerType::ResolveName($entityTypeID),
				'ENTITY_ID' => $entityID
			)
		)
	);
}
