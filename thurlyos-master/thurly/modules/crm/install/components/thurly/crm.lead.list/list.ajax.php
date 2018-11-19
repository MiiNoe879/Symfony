<?
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('DisableEventsCheck', true);

$action = isset($_REQUEST['ACTION']) ? $_REQUEST['ACTION'] : '';
/**
 * AGENTS ARE REQUIRED FOR FOLLOWING ACTIONS:
 * 	REBUILD SEARCH INDEX
 * 	BUILD TIMELINE
 */
define(
	'NO_AGENT_CHECK',
	!in_array($action, array('REBUILD_SEARCH_CONTENT', 'BUILD_TIMELINE', 'REFRESH_ACCOUNTING'), true)
);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');
global $DB, $APPLICATION;
if(!function_exists('__CrmLeadListEndResonse'))
{
	function __CrmLeadListEndResonse($result)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
		if(!empty($result))
		{
			echo CUtil::PhpToJSObject($result);
		}
		require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');
		die();
	}
}

if (!CModule::IncludeModule('crm'))
{
	__CrmLeadListEndResonse(array('ERROR' => 'Could not include crm module.'));
}

$userPerms = CCrmPerms::GetCurrentUserPermissions();
if(!CCrmPerms::IsAuthorized())
{
	__CrmLeadListEndResonse(array('ERROR' => 'Access denied.'));
}

if (isset($_REQUEST['MODE']) && $_REQUEST['MODE'] === 'SEARCH')
{
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmLead::CheckReadPermission(0, $userPerms))
	{
		__CrmLeadListEndResonse(array('ERROR' => 'Access denied.'));
	}

	CUtil::JSPostUnescape();
	$APPLICATION->RestartBuffer();

	// Limit count of items to be found
	$nPageTop = 50;		// 50 items by default
	if (isset($_REQUEST['LIMIT_COUNT']) && ($_REQUEST['LIMIT_COUNT'] >= 0))
	{
		$rawNPageTop = (int) $_REQUEST['LIMIT_COUNT'];
		if ($rawNPageTop === 0)
			$nPageTop = false;		// don't limit
		elseif ($rawNPageTop > 0)
			$nPageTop = $rawNPageTop;
	}

	$arData = array();
	$search = trim($_REQUEST['VALUE']);
	if (!empty($search))
	{
		$multi = isset($_REQUEST['MULTI']) && $_REQUEST['MULTI'] == 'Y' ? true : false;
		$arFilter = array();
		if (is_numeric($search))
		{
			$arFilter['ID'] = (int)$search;
			$arFilter['%TITLE'] = $search;
			$arFilter['LOGIC'] = 'OR';
		}
		else if (preg_match('/(.*)\[(\d+?)\]/i' . BX_UTF_PCRE_MODIFIER, $search, $arMatches))
		{
			$arFilter['ID'] = (int)$arMatches[2];
			$arFilter['%TITLE'] = trim($arMatches[1]);
			$arFilter['%FULL_NAME'] = trim($arMatches[1]);
			$arFilter['LOGIC'] = 'OR';
		}
		else
		{
			$arFilter['%TITLE'] = trim($search);
			$arFilter['%FULL_NAME'] = trim($search);
			$arFilter['LOGIC'] = 'OR';
		}

		$arSelect = array('ID', 'TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'STATUS_ID');
		$arOrder = array('TITLE' => 'ASC');
		$obRes = CCrmLead::GetListEx($arOrder, $arFilter, false, array('nTopCount' => $nPageTop), $arSelect);
		$arFiles = array();
		$i = 0;
		$leadIndex = array();
		while ($arRes = $obRes->Fetch())
		{
			$arData[$i] =
				array(
					'id' => $multi ? 'L_' . $arRes['ID'] : $arRes['ID'],
					'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_lead_show'),
						array(
							'lead_id' => $arRes['ID']
						)
					),
					'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
					'desc' => CCrmLead::PrepareFormattedName(
						array(
							'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
							'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
							'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
							'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
						)
					),
					'type' => 'lead'
				);
			$leadIndex[$arRes['ID']] = &$arData[$i];
			$i++;
		}

		// advanced info - phone number, e-mail
		$obRes = CCrmFieldMulti::GetList(array('ID' => 'asc'), array('ENTITY_ID' => 'LEAD', 'ELEMENT_ID' => array_keys($leadIndex)));
		while ($arRes = $obRes->Fetch())
		{
			if (isset($leadIndex[$arRes['ELEMENT_ID']])
				&& ($arRes['TYPE_ID'] === 'PHONE' || $arRes['TYPE_ID'] === 'EMAIL'))
			{
				$item = &$leadIndex[$arRes['ELEMENT_ID']];
				if (!is_array($item['advancedInfo']))
					$item['advancedInfo'] = array();
				if (!is_array($item['advancedInfo']['multiFields']))
					$item['advancedInfo']['multiFields'] = array();
				$item['advancedInfo']['multiFields'][] = array(
					'ID' => $arRes['ID'],
					'TYPE_ID' => $arRes['TYPE_ID'],
					'VALUE_TYPE' => $arRes['VALUE_TYPE'],
					'VALUE' => $arRes['VALUE']
				);
				unset($item);
			}
		}
		unset($leadIndex);
	}

	__CrmLeadListEndResonse($arData);
}
elseif ($action === 'REBUILD_SEARCH_CONTENT')
{
	$agent = \Thurly\Crm\Agent\Search\LeadSearchContentRebuildAgent::getInstance();
	if(!$agent->isEnabled())
	{
		__CrmLeadListEndResonse(array('STATUS' => 'COMPLETED'));
	}

	$progressData = $agent->getProgressData();
	__CrmLeadListEndResonse(
		array(
			'STATUS' => 'PROGRESS',
			'PROCESSED_ITEMS' => $progressData['PROCESSED_ITEMS'],
			'TOTAL_ITEMS' => $progressData['TOTAL_ITEMS'],
		)
	);
}
elseif ($action === 'REFRESH_ACCOUNTING')
{
	$agent = \Thurly\Crm\Agent\Accounting\LeadAccountSyncAgent::getInstance();
	if(!$agent->isEnabled())
	{
		__CrmLeadListEndResonse(array('STATUS' => 'COMPLETED'));
	}

	$progressData = $agent->getProgressData();
	__CrmLeadListEndResonse(
		array(
			'STATUS' => 'PROGRESS',
			'PROCESSED_ITEMS' => $progressData['PROCESSED_ITEMS'],
			'TOTAL_ITEMS' => $progressData['TOTAL_ITEMS'],
		)
	);
}
elseif ($action === 'BUILD_TIMELINE')
{
	$agent = \Thurly\Crm\Agent\Timeline\LeadTimelineBuildAgent::getInstance();
	if(!$agent->isEnabled())
	{
		__CrmLeadListEndResonse(array('STATUS' => 'COMPLETED'));
	}

	$progressData = $agent->getProgressData();
	__CrmLeadListEndResonse(
		array(
			'STATUS' => 'PROGRESS',
			'PROCESSED_ITEMS' => $progressData['PROCESSED_ITEMS'],
			'TOTAL_ITEMS' => $progressData['TOTAL_ITEMS'],
		)
	);
}
elseif ($action === 'SAVE_PROGRESS')
{
	$ID = isset($_REQUEST['ID']) ? intval($_REQUEST['ID']) : 0;
	$typeName = isset($_REQUEST['TYPE']) ? $_REQUEST['TYPE'] : '';
	$statusID = isset($_REQUEST['VALUE']) ? $_REQUEST['VALUE'] : '';

	$targetTypeName = CCrmOwnerType::ResolveName(CCrmOwnerType::Lead);
	if($statusID === '' || $ID <= 0  || $typeName !== $targetTypeName)
	{
		$APPLICATION->RestartBuffer();
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Invalid data!')
		);
		die();
	}

	$entityAttrs = $userPerms->GetEntityAttr($targetTypeName, array($ID));
	if (!$userPerms->CheckEnityAccess($targetTypeName, 'WRITE', $entityAttrs[$ID]))
	{
		$APPLICATION->RestartBuffer();
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Access denied!')
		);
		die();
	}

	$arFields = CCrmLead::GetByID($ID, false);

	if(!is_array($arFields))
	{
		$APPLICATION->RestartBuffer();
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Not found!')
		);
		die();
	}

	if(isset($arFields['CREATED_BY_ID']))
	{
		unset($arFields['CREATED_BY_ID']);
	}

	if(isset($arFields['DATE_CREATE']))
	{
		unset($arFields['DATE_CREATE']);
	}

	if(isset($arFields['MODIFY_BY_ID']))
	{
		unset($arFields['MODIFY_BY_ID']);
	}

	if(isset($arFields['DATE_MODIFY']))
	{
		unset($arFields['DATE_MODIFY']);
	}

	if(isset($arFields['STATUS_ID']) && $arFields['STATUS_ID'] === $statusID)
	{
		__CrmLeadListEndResonse(array('TYPE' => $targetTypeName, 'ID' => $ID, 'VALUE' => $statusID));
	}

	$arFields['STATUS_ID'] = $statusID;
	$CCrmLead = new CCrmLead(false);
	if($CCrmLead->Update($ID, $arFields, true, true, array('DISABLE_USER_FIELD_CHECK' => true, 'REGISTER_SONET_EVENT' => true)))
	{
		$arErrors = array();
		CCrmBizProcHelper::AutoStartWorkflows(
			CCrmOwnerType::Lead,
			$ID,
			CCrmBizProcEventType::Edit,
			$arErrors
		);

		//Region automation
		\Thurly\Crm\Automation\Factory::runOnStatusChanged(\CCrmOwnerType::Lead, $ID);
		//end region
	}

	__CrmLeadListEndResonse(array('TYPE' => $targetTypeName, 'ID' => $ID, 'VALUE' => $statusID));
}
elseif ($action === 'REBUILD_DUPLICATE_INDEX')
{
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	$entityTypeName = isset($params['ENTITY_TYPE_NAME']) ? $params['ENTITY_TYPE_NAME'] : '';
	if($entityTypeName === '')
	{
		__CrmLeadListEndResonse(array('ERROR' => 'Entity type is not specified.'));
	}

	$entityTypeID = CCrmOwnerType::ResolveID($entityTypeName);
	if($entityTypeID === CCrmOwnerType::Undefined)
	{
		__CrmLeadListEndResonse(array('ERROR' => 'Undefined entity type is specified.'));
	}

	if($entityTypeID !== CCrmOwnerType::Lead)
	{
		__CrmLeadListEndResonse(array('ERROR' => "The '{$entityTypeName}' type is not supported in current context."));
	}

	if(!CCrmLead::CheckUpdatePermission(0))
	{
		__CrmLeadListEndResonse(array('ERROR' => 'Access denied.'));
	}

	if(COption::GetOptionString('crm', '~CRM_REBUILD_LEAD_DUP_INDEX', 'N') !== 'Y')
	{
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'NOT_REQUIRED',
				'SUMMARY' => GetMessage('CRM_LEAD_LIST_REBUILD_DUPLICATE_INDEX_NOT_REQUIRED_SUMMARY')
			)
		);
	}

	$progressData = COption::GetOptionString('crm', '~CRM_REBUILD_LEAD_DUP_INDEX_PROGRESS',  '');
	$progressData = $progressData !== '' ? unserialize($progressData) : array();

	if(empty($progressData) && intval(\Thurly\Crm\BusinessTypeTable::getCount()) === 0)
	{
		//Try to fill BusinessTypeTable on first iteration
		\Thurly\Crm\BusinessTypeTable::installDefault();
	}

	$lastItemID = isset($progressData['LAST_ITEM_ID']) ? intval($progressData['LAST_ITEM_ID']) : 0;
	$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? intval($progressData['PROCESSED_ITEMS']) : 0;
	$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? intval($progressData['TOTAL_ITEMS']) : 0;
	if($totalItemQty <= 0)
	{
		$totalItemQty = CCrmLead::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}

	$filter = array('CHECK_PERMISSIONS' => 'N');
	if($lastItemID > 0)
	{
		$filter['>ID'] = $lastItemID;
	}

	$dbResult = CCrmLead::GetListEx(
		array('ID' => 'ASC'),
		$filter,
		false,
		array('nTopCount' => 100),
		array('ID')
	);

	$itemIDs = array();
	$itemQty = 0;
	if(is_object($dbResult))
	{
		while($fields = $dbResult->Fetch())
		{
			$itemIDs[] = intval($fields['ID']);
			$itemQty++;
		}
	}

	if($itemQty > 0)
	{
		CCrmLead::RebuildDuplicateIndex($itemIDs);

		$progressData['TOTAL_ITEMS'] = $totalItemQty;
		$processedItemQty += $itemQty;
		$progressData['PROCESSED_ITEMS'] = $processedItemQty;
		$progressData['LAST_ITEM_ID'] = $itemIDs[$itemQty - 1];

		COption::SetOptionString('crm', '~CRM_REBUILD_LEAD_DUP_INDEX_PROGRESS', serialize($progressData));
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_REBUILD_DUPLICATE_INDEX_PROGRESS_SUMMARY',
					array(
						'#PROCESSED_ITEMS#' => $processedItemQty,
						'#TOTAL_ITEMS#' => $totalItemQty
					)
				)
			)
		);
	}
	else
	{
		COption::RemoveOption('crm', '~CRM_REBUILD_LEAD_DUP_INDEX');
		COption::RemoveOption('crm', '~CRM_REBUILD_LEAD_DUP_INDEX_PROGRESS');
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_REBUILD_DUPLICATE_INDEX_COMPLETED_SUMMARY',
					array('#PROCESSED_ITEMS#' => $processedItemQty)
				)
			)
		);
	}
}
elseif ($action === 'REBUILD_STATISTICS')
{
	//~CRM_REBUILD_LEAD_STATISTICS
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmLead::CheckUpdatePermission(0))
	{
		__CrmLeadListEndResonse(array('ERROR' => 'Access denied.'));
	}

	if(COption::GetOptionString('crm', '~CRM_REBUILD_LEAD_STATISTICS', 'N') !== 'Y')
	{
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'NOT_REQUIRED',
				'SUMMARY' => GetMessage('CRM_LEAD_LIST_REBUILD_STATISTICS_NOT_REQUIRED_SUMMARY')
			)
		);
	}

	$progressData = COption::GetOptionString('crm', '~CRM_REBUILD_LEAD_STATISTICS_PROGRESS',  '');
	$progressData = $progressData !== '' ? unserialize($progressData) : array();
	$lastItemID = isset($progressData['LAST_ITEM_ID']) ? intval($progressData['LAST_ITEM_ID']) : 0;
	$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? intval($progressData['PROCESSED_ITEMS']) : 0;
	$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? intval($progressData['TOTAL_ITEMS']) : 0;
	if($totalItemQty <= 0)
	{
		$totalItemQty = CCrmLead::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}

	$filter = array('CHECK_PERMISSIONS' => 'N');
	if($lastItemID > 0)
	{
		$filter['>ID'] = $lastItemID;
	}

	$dbResult = CCrmLead::GetListEx(
		array('ID' => 'ASC'),
		$filter,
		false,
		array('nTopCount' => 200),
		array('ID')
	);

	$itemIDs = array();
	$itemQty = 0;
	if(is_object($dbResult))
	{
		while($fields = $dbResult->Fetch())
		{
			$itemIDs[] = (int)$fields['ID'];
			$itemQty++;
		}
	}

	if($itemQty > 0)
	{
		CCrmLead::RebuildStatistics($itemIDs);

		$progressData['TOTAL_ITEMS'] = $totalItemQty;
		$processedItemQty += $itemQty;
		$progressData['PROCESSED_ITEMS'] = $processedItemQty;
		$progressData['LAST_ITEM_ID'] = $itemIDs[$itemQty - 1];

		COption::SetOptionString('crm', '~CRM_REBUILD_LEAD_STATISTICS_PROGRESS', serialize($progressData));
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_REBUILD_STATISTICS_PROGRESS_SUMMARY',
					array(
						'#PROCESSED_ITEMS#' => $processedItemQty,
						'#TOTAL_ITEMS#' => $totalItemQty
					)
				)
			)
		);
	}
	else
	{
		COption::RemoveOption('crm', '~CRM_REBUILD_LEAD_STATISTICS');
		COption::RemoveOption('crm', '~CRM_REBUILD_LEAD_STATISTICS_PROGRESS');
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_REBUILD_STATISTICS_COMPLETED_SUMMARY',
					array('#PROCESSED_ITEMS#' => $processedItemQty)
				)
			)
		);
	}
}
elseif ($action === 'REBUILD_SUM_STATISTICS')
{
	//~CRM_REBUILD_LEAD_SUM_STATISTICS
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmLead::CheckUpdatePermission(0))
	{
		__CrmLeadListEndResonse(array('ERROR' => 'Access denied.'));
	}

	if(COption::GetOptionString('crm', '~CRM_REBUILD_LEAD_SUM_STATISTICS', 'N') !== 'Y')
	{
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'NOT_REQUIRED',
				'SUMMARY' => GetMessage('CRM_LEAD_LIST_REBUILD_STATISTICS_NOT_REQUIRED_SUMMARY')
			)
		);
	}

	$progressData = COption::GetOptionString('crm', '~CRM_REBUILD_LEAD_SUM_STATISTICS_PROGRESS',  '');
	$progressData = $progressData !== '' ? unserialize($progressData) : array();
	$lastItemID = isset($progressData['LAST_ITEM_ID']) ? intval($progressData['LAST_ITEM_ID']) : 0;
	$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? intval($progressData['PROCESSED_ITEMS']) : 0;
	$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? intval($progressData['TOTAL_ITEMS']) : 0;
	if($totalItemQty <= 0)
	{
		$totalItemQty = CCrmLead::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}

	$filter = array('CHECK_PERMISSIONS' => 'N');
	if($lastItemID > 0)
	{
		$filter['>ID'] = $lastItemID;
	}

	$dbResult = CCrmLead::GetListEx(
		array('ID' => 'ASC'),
		$filter,
		false,
		array('nTopCount' => 20),
		array('ID')
	);

	$itemIDs = array();
	$itemQty = 0;
	if(is_object($dbResult))
	{
		while($fields = $dbResult->Fetch())
		{
			$itemIDs[] = (int)$fields['ID'];
			$itemQty++;
		}
	}

	if($itemQty > 0)
	{
		CCrmLead::RebuildStatistics(
			$itemIDs,
			array(
				'FORCED' => true,
				'ENABLE_SUM_STATISTICS' => true,
				'ENABLE_HISTORY'=> false,
				'ENABLE_ACTIVITY_STATISTICS' => false
			)
		);

		$progressData['TOTAL_ITEMS'] = $totalItemQty;
		$processedItemQty += $itemQty;
		$progressData['PROCESSED_ITEMS'] = $processedItemQty;
		$progressData['LAST_ITEM_ID'] = $itemIDs[$itemQty - 1];

		COption::SetOptionString('crm', '~CRM_REBUILD_LEAD_SUM_STATISTICS_PROGRESS', serialize($progressData));
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_REBUILD_STATISTICS_PROGRESS_SUMMARY',
					array(
						'#PROCESSED_ITEMS#' => $processedItemQty,
						'#TOTAL_ITEMS#' => $totalItemQty
					)
				)
			)
		);
	}
	else
	{
		COption::RemoveOption('crm', '~CRM_REBUILD_LEAD_SUM_STATISTICS');
		COption::RemoveOption('crm', '~CRM_REBUILD_LEAD_SUM_STATISTICS_PROGRESS');
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_REBUILD_STATISTICS_COMPLETED_SUMMARY',
					array('#PROCESSED_ITEMS#' => $processedItemQty)
				)
			)
		);
	}
}
elseif ($action === 'REBUILD_SEMANTICS')
{
	//~CRM_REBUILD_LEAD_SEMANTICS
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmLead::CheckUpdatePermission(0))
	{
		__CrmLeadListEndResonse(array('ERROR' => 'Access denied.'));
	}

	if(COption::GetOptionString('crm', '~CRM_REBUILD_LEAD_SEMANTICS', 'N') !== 'Y')
	{
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'NOT_REQUIRED',
				'SUMMARY' => GetMessage('CRM_LEAD_LIST_REBUILD_SEMANTICS_NOT_REQUIRED_SUMMARY')
			)
		);
	}

	$progressData = COption::GetOptionString('crm', '~CRM_REBUILD_LEAD_SEMANTICS_PROGRESS',  '');
	$progressData = $progressData !== '' ? unserialize($progressData) : array();
	$lastItemID = isset($progressData['LAST_ITEM_ID']) ? intval($progressData['LAST_ITEM_ID']) : 0;
	$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? intval($progressData['PROCESSED_ITEMS']) : 0;
	$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? intval($progressData['TOTAL_ITEMS']) : 0;
	if($totalItemQty <= 0)
	{
		$totalItemQty = CCrmLead::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}

	$filter = array('CHECK_PERMISSIONS' => 'N');
	if($lastItemID > 0)
	{
		$filter['>ID'] = $lastItemID;
	}

	$dbResult = CCrmLead::GetListEx(
		array('ID' => 'ASC'),
		$filter,
		false,
		array('nTopCount' => 200),
		array('ID')
	);

	$itemIDs = array();
	$itemQty = 0;
	if(is_object($dbResult))
	{
		while($fields = $dbResult->Fetch())
		{
			$itemIDs[] = (int)$fields['ID'];
			$itemQty++;
		}
	}

	if($itemQty > 0)
	{
		CCrmLead::RebuildSemantics($itemIDs, array('FORCED' => true));

		$progressData['TOTAL_ITEMS'] = $totalItemQty;
		$processedItemQty += $itemQty;
		$progressData['PROCESSED_ITEMS'] = $processedItemQty;
		$progressData['LAST_ITEM_ID'] = $itemIDs[$itemQty - 1];

		COption::SetOptionString('crm', '~CRM_REBUILD_LEAD_SEMANTICS_PROGRESS', serialize($progressData));
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_REBUILD_SEMANTICS_PROGRESS_SUMMARY',
					array(
						'#PROCESSED_ITEMS#' => $processedItemQty,
						'#TOTAL_ITEMS#' => $totalItemQty
					)
				)
			)
		);
	}
	else
	{
		COption::RemoveOption('crm', '~CRM_REBUILD_LEAD_SEMANTICS');
		COption::RemoveOption('crm', '~CRM_REBUILD_LEAD_SEMANTICS_PROGRESS');
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_REBUILD_SEMANTICS_COMPLETED_SUMMARY',
					array('#PROCESSED_ITEMS#' => $processedItemQty)
				)
			)
		);
	}
}
elseif ($action === 'GET_ROW_COUNT')
{
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmLead::CheckReadPermission(0, $userPerms))
	{
		__CrmLeadListEndResonse(array('ERROR' => 'Access denied.'));
	}

	$params = isset($_REQUEST['PARAMS']) && is_array($_REQUEST['PARAMS']) ? $_REQUEST['PARAMS'] : array();
	$gridID = isset($params['GRID_ID']) ? $params['GRID_ID'] : '';
	if(!($gridID !== ''
		&& isset($_SESSION['CRM_GRID_DATA'])
		&& isset($_SESSION['CRM_GRID_DATA'][$gridID])
		&& is_array($_SESSION['CRM_GRID_DATA'][$gridID])))
	{
		__CrmLeadListEndResonse(array('DATA' => array('TEXT' => '')));
	}

	$gridData = $_SESSION['CRM_GRID_DATA'][$gridID];
	$filter = isset($gridData['FILTER']) && is_array($gridData['FILTER']) ? $gridData['FILTER'] : array();
	$result = CCrmLead::GetListEx(array(), $filter, array(), false, array(), array());

	$text = '';
	if(is_numeric($result))
	{
		$text = GetMessage('CRM_LEAD_LIST_ROW_COUNT', array('#ROW_COUNT#' => $result));
		if($text === '')
		{
			$text = $result;
		}
	}
	__CrmLeadListEndResonse(array('DATA' => array('TEXT' => $text)));
}
elseif ($action === 'DELETE')
{
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmLead::CheckDeletePermission(0, $userPerms))
	{
		__CrmLeadListEndResonse(array('ERROR' => GetMessage('CRM_LEAD_LIST_DELETION_ACCESS_ERROR')));
	}

	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	$entityTypeName = isset($params['ENTITY_TYPE_NAME']) ? $params['ENTITY_TYPE_NAME'] : '';
	if($entityTypeName === '')
	{
		__CrmLeadListEndResonse(array('ERROR' => GetMessage('CRM_LEAD_LIST_DELETION_PARAM_ERROR')));
	}

	$entityTypeID = CCrmOwnerType::ResolveID($entityTypeName);
	if($entityTypeID === CCrmOwnerType::Undefined)
	{
		__CrmLeadListEndResonse(array('ERROR' => GetMessage('CRM_LEAD_LIST_DELETION_PARAM_ERROR')));
	}

	if($entityTypeID !== CCrmOwnerType::Lead)
	{
		__CrmLeadListEndResonse(array('ERROR' => GetMessage('CRM_LEAD_LIST_DELETION_PARAM_ERROR')));
	}

	$gridID = isset($params['GRID_ID']) ? $params['GRID_ID'] : '';
	if($gridID === '')
	{
		__CrmLeadListEndResonse(array('ERROR' => GetMessage('CRM_LEAD_LIST_DELETION_PARAM_ERROR')));
	}

	$contextID = isset($params['CONTEXT_ID']) ? $params['CONTEXT_ID'] : '';
	if($contextID === '')
	{
		__CrmLeadListEndResonse(array('ERROR' => GetMessage('CRM_LEAD_LIST_DELETION_PARAM_ERROR')));
	}

	$progressData = isset($_SESSION['CRM_LEAD_DELETE_PROGRESS'])
	&& isset($_SESSION['CRM_LEAD_DELETE_PROGRESS'][$contextID])
	&& is_array($_SESSION['CRM_LEAD_DELETE_PROGRESS'][$contextID])
		? $_SESSION['CRM_LEAD_DELETE_PROGRESS'][$contextID] : array();

	$lastItemID = isset($progressData['LAST_ITEM_ID']) ? $progressData['LAST_ITEM_ID'] : 0;
	$totalItemQty = 0;
	$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? $progressData['PROCESSED_ITEMS'] : 0;

	$processAll = isset($params['PROCESS_ALL']) && strtoupper($params['PROCESS_ALL']) === 'Y';
	$slotLength = 5;
	$effectiveItemIDs = array();
	if(!$processAll)
	{
		$entityIDs = isset($params['ENTITY_IDS']) && is_array($params['ENTITY_IDS']) ? $params['ENTITY_IDS'] : array();
		if(empty($entityIDs))
		{
			__CrmLeadListEndResonse(array('ERROR' => 'Entity IDs are not specified.'));
		}
		$totalItemQty = count($entityIDs);

		sort($entityIDs, SORT_NUMERIC);
		$index = $lastItemID > 0 ? array_search($lastItemID, $entityIDs) : false;
		if(is_int($index) && $index >= 0)
		{
			$index++;
		}
		else
		{
			$index = 0;
		}

		$effectiveItemIDs = array_slice($entityIDs, $index, $slotLength);
		for($i = 0; $i < $slotLength; $i++)
		{
			if(!CCrmLead::CheckDeletePermission($effectiveItemIDs[$i], $userPerms))
			{
				unset($effectiveItemIDs[$i]);
			}
		}
	}
	else
	{
		$userFilterHash = isset($params['USER_FILTER_HASH']) ? $params['USER_FILTER_HASH'] : '';
		if($userFilterHash === '')
		{
			__CrmLeadListEndResonse(array('ERROR' => 'Filter hash is not specified.'));
		}

		$filter = \Thurly\Crm\Context\GridContext::getFilter($gridID);
		$filterHash = \Thurly\Crm\Context\GridContext::getFilterHash($gridID);

		if(!is_array($filter) || $filterHash === '')
		{
			__CrmLeadListEndResonse(array('ERROR' => GetMessage('CRM_LEAD_LIST_DELETION_FILTER_NOT_FOUND_ERROR')));
		}

		if($filterHash !== $userFilterHash)
		{
			__CrmLeadListEndResonse(array('ERROR' => GetMessage('CRM_LEAD_LIST_DELETION_FILTER_OUTDATED_ERROR')));
		}

		$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? $progressData['TOTAL_ITEMS'] : 0;
		if($totalItemQty <= 0)
		{
			$totalItemQty = CCrmLead::GetListEx(array(), $filter, array(), false, array(), array());
		}

		if($lastItemID > 0)
		{
			$filter['>ID'] = $lastItemID;
		}

		$result = CCrmLead::GetListEx(
			array('ID' => 'ASC'),
			$filter,
			false,
			array('nTopCount' => $slotLength),
			array('ID')
		);

		if(is_object($result))
		{
			while($fields = $result->Fetch())
			{
				$itemID = (int)$fields['ID'];
				if(CCrmLead::CheckDeletePermission($itemID, $userPerms))
				{
					$effectiveItemIDs[] = $itemID;
				}
			}
		}
	}

	$entity = new CCrmLead(false);
	$bizProc = new CCrmBizProc(CCrmOwnerType::LeadName);
	$itemQty = 0;

	if(!empty($effectiveItemIDs))
	{
		foreach($effectiveItemIDs as $itemID)
		{
			$itemID = (int)$itemID;

			$bizProc->Delete($itemID, $userPerms->GetEntityAttr(CCrmOwnerType::LeadName, array($itemID)));
			$entity->Delete($itemID, array('CHECK_DEPENDENCIES' => true, 'PROCESS_BIZPROC' => false));

			$lastItemID = $itemID;
			$itemQty++;
		}
	}

	if($itemQty > 0)
	{
		$processedItemQty += $itemQty;
		$_SESSION['CRM_LEAD_DELETE_PROGRESS'][$contextID] = array(
			'LAST_ITEM_ID' => $lastItemID,
			'PROCESSED_ITEMS' => $processedItemQty,
			'TOTAL_ITEMS' => $totalItemQty
		);

		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_DELETION_PROGRESS_SUMMARY',
					array(
						'#PROCESSED_ITEMS#' => $processedItemQty,
						'#TOTAL_ITEMS#' => $totalItemQty
					)
				)
			)
		);
	}
	else
	{
		__CrmLeadListEndResonse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_LEAD_LIST_DELETION_COMPLETED_SUMMARY',
					array('#PROCESSED_ITEMS#' => $processedItemQty)
				)
			)
		);
	}
}
?>
