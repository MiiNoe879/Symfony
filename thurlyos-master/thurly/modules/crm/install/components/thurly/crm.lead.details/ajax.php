<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');
if (!CModule::IncludeModule('crm'))
{
	return;
}
/*
 * ONLY 'POST' METHOD SUPPORTED
 * SUPPORTED ACTIONS:
 * 'SAVE'
 * 'GET_FORMATTED_SUM'
 */
global $DB, $APPLICATION;
\Thurly\Main\Localization\Loc::loadMessages(__FILE__);
if(!function_exists('__CrmLeadDetailsEndJsonResonse'))
{
	function __CrmLeadDetailsEndJsonResonse($result)
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
	__CrmLeadDetailsEndJsonResonse(array('ERROR'=>'ACTION IS NOT DEFINED!'));
}
if($action === 'GET_FORMATTED_SUM')
{
	$sum = isset($_POST['SUM']) ? $_POST['SUM'] : 0.0;
	$currencyID = isset($_POST['CURRENCY_ID']) ? $_POST['CURRENCY_ID'] : '';
	if($currencyID === '')
	{
		$currencyID = \CCrmCurrency::GetBaseCurrencyID();
	}

	__CrmLeadDetailsEndJsonResonse(
		array(
			'FORMATTED_SUM' => \CCrmCurrency::MoneyToString($sum, $currencyID, '#'),
			'FORMATTED_SUM_WITH_CURRENCY' => \CCrmCurrency::MoneyToString($sum, $currencyID, '')
		)
	);
}
elseif($action === 'SAVE')
{
	$ID = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;
	if(($ID > 0 && !\CCrmLead::CheckUpdatePermission($ID, $currentUserPermissions))
		|| ($ID === 0 && !\CCrmLead::CheckCreatePermission($currentUserPermissions))
	)
	{
		__CrmLeadDetailsEndJsonResonse(array('ERROR'=>'PERMISSION DENIED!'));
	}

	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	$sourceEntityID =  isset($params['LEAD_ID']) ? (int)$params['LEAD_ID'] : 0;

	$isNew = $ID === 0;
	$isCopyMode = $isNew && $sourceEntityID > 0;
	//TODO: Implement external mode
	$isExternal = false;

	$previousFields = !$isNew ? \CCrmLead::GetByID($ID, false) : null;

	$fields = array();
	$fieldsInfo = \CCrmLead::GetFieldsInfo();
	$userType = new \CCrmUserType($GLOBALS['USER_FIELD_MANAGER'], \CCrmLead::GetUserFieldEntityID());
	$userType->PrepareFieldsInfo($fieldsInfo);
	\CCrmFieldMulti::PrepareFieldsInfo($fieldsInfo);

	$sourceFields = array();
	if($sourceEntityID > 0)
	{
		$dbResult = \CCrmLead::GetListEx(
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

		$sourceFields['FM'] = array();
		$multiFieldDbResult = \CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array(
				'ENTITY_ID' => CCrmOwnerType::LeadName,
				'ELEMENT_ID' => $sourceEntityID
			)
		);

		while($multiField = $multiFieldDbResult->Fetch())
		{
			$typeID = $multiField['TYPE_ID'];
			if(!isset($sourceFields['FM'][$typeID]))
			{
				$sourceFields['FM'][$typeID] = array();
			}
			$sourceFields['FM'][$typeID][$multiField['ID']] = array(
				'VALUE' => $multiField['VALUE'],
				'VALUE_TYPE' => $multiField['VALUE_TYPE']
			);
		}
	}

	foreach(array_keys($fieldsInfo) as $fieldName)
	{
		if(\CCrmFieldMulti::IsSupportedType($fieldName) && is_array($_POST[$fieldName]))
		{
			if(!isset($fields['FM']))
			{
				$fields['FM'] = array();
			}

			$fields['FM'][$fieldName] = $_POST[$fieldName];
		}
		elseif(isset($_POST[$fieldName]))
		{
			$fields[$fieldName] = $_POST[$fieldName];
		}
	}

	//region PRODUCT ROWS
	$enableProductRows = !array_key_exists('LEAD_PRODUCT_DATA_INVALIDATE', $_POST) && array_key_exists('LEAD_PRODUCT_DATA', $_POST);
	$productRows = array();
	$productRowSettings = array();
	if($enableProductRows)
	{
		if(isset($_POST['LEAD_PRODUCT_DATA']) && $_POST['LEAD_PRODUCT_DATA'] !== '')
		{
			$productRows = \CUtil::JsObjectToPhp($_POST['LEAD_PRODUCT_DATA']);
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

			$totals = \CCrmProductRow::CalculateTotalInfo('L', 0, false, $calculationParams, $productRows);
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

		if(isset($_POST['LEAD_PRODUCT_DATA_SETTINGS']) && $_POST['LEAD_PRODUCT_DATA_SETTINGS'] !== '')
		{
			$settings = \CUtil::JsObjectToPhp($_POST['LEAD_PRODUCT_DATA_SETTINGS']);
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

	if(!empty($fields) || $enableProductRows)
	{
		if(isset($fields['ASSIGNED_BY_ID']) && $fields['ASSIGNED_BY_ID'] > 0)
		{
			\Thurly\Crm\Entity\EntityEditor::registerSelectedUser($fields['ASSIGNED_BY_ID']);
		}

		if(!empty($fields))
		{
			if($isCopyMode)
			{
				if(!isset($fields['ASSIGNED_BY_ID']))
				{
					$fields['ASSIGNED_BY_ID'] = $currentUserID;
				}

				$merger = new \Thurly\Crm\Merger\LeadMerger($currentUserID, false);
				$merger->mergeFields($sourceFields, $fields, true);
			}

			if(isset($fields['COMMENTS']))
			{
				$fields['COMMENTS'] = \Thurly\Crm\Format\TextHelper::sanitizeHtml($fields['COMMENTS']);
			}

			$entity = new \CCrmLead(false);
			if($isNew)
			{
				if(!isset($fields['TITLE']) || $fields['TITLE'] === '')
				{
					$fields['TITLE'] = GetMessage('CRM_LEAD_DEAULT_TITLE');
				}

				if(!isset($fields['SOURCE_ID']))
				{
					$fields['SOURCE_ID'] = \CCrmStatus::GetFirstStatusID('SOURCE');
				}

				if(!isset($fields['OPENED']))
				{
					$fields['OPENED'] = \Thurly\Crm\Settings\LeadSettings::getCurrent()->getOpenedFlag() ? 'Y' : 'N';
				}

				if(!isset($fields['CURRENCY_ID']))
				{
					$fields['CURRENCY_ID'] = CCrmCurrency::GetBaseCurrencyID();
				}

				$fields['EXCH_RATE'] = CCrmCurrency::GetExchangeRate($fields['CURRENCY_ID']);

				$ID = $entity->Add($fields, true, array('REGISTER_SONET_EVENT' => true));
				if($ID <= 0)
				{
					__CrmLeadDetailsEndJsonResonse(array('ERROR' => $entity->LAST_ERROR));
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

				if(!$entity->Update($ID, $fields, true, true,  array('REGISTER_SONET_EVENT' => true)))
				{
					__CrmLeadDetailsEndJsonResonse(array('ERROR' => $entity->LAST_ERROR));
				}
			}
		}

		if(!$isExternal && $enableProductRows && (!$isNew || !empty($productRows)))
		{
			if(!\CCrmLead::SaveProductRows($ID, $productRows, true, true, false))
			{
				__CrmLeadDetailsEndJsonResonse(array('ERROR' => GetMessage('CRM_LEAD_PRODUCT_ROWS_SAVING_ERROR')));
			}
		}

		if(!empty($productRowSettings))
		{
			if(!$isNew)
			{
				$productRowSettings = array_merge(
					\CCrmProductRow::LoadSettings('L', $ID),
					$productRowSettings
				);
			}
			\CCrmProductRow::SaveSettings('L', $ID, $productRowSettings);
		}

		$arErrors = array();
		\CCrmBizProcHelper::AutoStartWorkflows(
			\CCrmOwnerType::Lead,
			$ID,
			$isNew ? \CCrmBizProcEventType::Create : \CCrmBizProcEventType::Edit,
			$arErrors,
			isset($_POST['bizproc_parameters']) ? $_POST['bizproc_parameters'] : null
		);

		if($isNew)
		{
			\Thurly\Crm\Automation\Factory::runOnAdd(\CCrmOwnerType::Lead, $ID);
		}
		else if(is_array($previousFields)
			&& isset($fields['STATUS_ID'])
			&& isset($previousFields['STATUS_ID'])
			&& $fields['STATUS_ID'] !== $previousFields['STATUS_ID']
		)
		{
			\Thurly\Crm\Automation\Factory::runOnStatusChanged(\CCrmOwnerType::Lead, $ID);
		}
	}

	CThurlyComponent::includeComponentClass('thurly:crm.lead.details');
	$component = new CCrmLeadDetailsComponent();
	$component->initializeParams(
		isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array()
	);
	$component->setEntityID($ID);
	$result = array('ENTITY_ID' => $ID, 'ENTITY_DATA' => $component->prepareEntityData());
	if($isNew)
	{
		$result['REDIRECT_URL'] = \CCrmOwnerType::GetDetailsUrl(
			\CCrmOwnerType::Lead,
			$ID,
			false,
			array('OPEN_IN_SLIDER' => true)
		);
	}

	__CrmLeadDetailsEndJsonResonse($result);
}
elseif($action === 'DELETE')
{
	$ID = isset($_POST['ACTION_ENTITY_ID']) ? max((int)$_POST['ACTION_ENTITY_ID'], 0) : 0;
	if($ID <= 0)
	{
		__CrmLeadDetailsEndJsonResonse(array('ERROR' => GetMessage('CRM_LEAD_NOT_FOUND')));
	}

	if(!\CCrmLead::CheckDeletePermission($ID, $currentUserPermissions))
	{
		__CrmLeadDetailsEndJsonResonse(array('ERROR' => GetMessage('CRM_LEAD_ACCESS_DENIED')));
	}

	$bizProc = new CCrmBizProc('LEAD');
	if (!$bizProc->Delete($ID, \CCrmLead::GetPermissionAttributes(array($ID))))
	{
		__CrmLeadDetailsEndJsonResonse(array('ERROR' => $bizProc->LAST_ERROR));
	}

	$entity = new \CCrmLead(false);
	if (!$entity->Delete($ID, array('PROCESS_BIZPROC' => false)))
	{
		/** @var CApplicationException $ex */
		$ex = $APPLICATION->GetException();
		__CrmLeadDetailsEndJsonResonse(
			array(
				'ERROR' => ($ex instanceof CApplicationException) ? $ex->GetString() : GetMessage('CRM_LEAD_DELETION_ERROR')
			)
		);
	}
	__CrmLeadDetailsEndJsonResonse(array('ENTITY_ID' => $ID));
}
