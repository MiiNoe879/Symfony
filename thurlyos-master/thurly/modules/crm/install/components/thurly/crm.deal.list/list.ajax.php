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
	!in_array($action, array('REBUILD_SEARCH_CONTENT', 'BUILD_TIMELINE', 'BUILD_RECURRING_TIMELINE', 'REFRESH_ACCOUNTING'), true)
);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');
global $DB, $APPLICATION;
if(!function_exists('__CrmDealListEndResonse'))
{
	function __CrmDealListEndResonse($result)
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
	return;
}

$userPerms = CCrmPerms::GetCurrentUserPermissions();
if(!CCrmPerms::IsAuthorized())
{
	return;
}

global $APPLICATION;

if (isset($_REQUEST['MODE']) && $_REQUEST['MODE'] === 'SEARCH')
{
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmDeal::CheckReadPermission(0, $userPerms))
	{
		__CrmDealListEndResonse(array('ERROR' => 'Access denied.'));
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
			$arFilter['LOGIC'] = 'OR';
		}
		else
			$arFilter['%TITLE'] = $search;

		$arDealStageList = CCrmStatus::GetStatusListEx('DEAL_STAGE');
		$arSelect = array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME');
		$arOrder = array('TITLE' => 'ASC');
		$obRes = CCrmDeal::GetList($arOrder, $arFilter, $arSelect, $nPageTop);
		$arFiles = array();
		while ($arRes = $obRes->Fetch())
		{
			$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
			$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '') . $arRes['CONTACT_FULL_NAME'];

			$arData[] =
				array(
					'id' => $multi ? 'D_' . $arRes['ID'] : $arRes['ID'],
					'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_deal_show'),
						array(
							'deal_id' => $arRes['ID']
						)
					),
					'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
					'desc' => $clientTitle,
					'type' => 'deal'
				);
		}
	}

	__CrmDealListEndResonse($arData);
}
elseif ($action === 'REBUILD_SEARCH_CONTENT')
{
	$agent = \Thurly\Crm\Agent\Search\DealSearchContentRebuildAgent::getInstance();
	if(!$agent->isEnabled())
	{
		__CrmDealListEndResonse(array('STATUS' => 'COMPLETED'));
	}

	$progressData = $agent->getProgressData();
	__CrmDealListEndResonse(
		array(
			'STATUS' => 'PROGRESS',
			'PROCESSED_ITEMS' => $progressData['PROCESSED_ITEMS'],
			'TOTAL_ITEMS' => $progressData['TOTAL_ITEMS']
		)
	);
}
elseif ($action === 'REFRESH_ACCOUNTING')
{
	$agent = \Thurly\Crm\Agent\Accounting\DealAccountSyncAgent::getInstance();
	if(!$agent->isEnabled())
	{
		__CrmDealListEndResonse(array('STATUS' => 'COMPLETED'));
	}

	$progressData = $agent->getProgressData();
	__CrmDealListEndResonse(
		array(
			'STATUS' => 'PROGRESS',
			'PROCESSED_ITEMS' => $progressData['PROCESSED_ITEMS'],
			'TOTAL_ITEMS' => $progressData['TOTAL_ITEMS'],
		)
	);
}
elseif ($action === 'BUILD_TIMELINE')
{
	$agent = \Thurly\Crm\Agent\Timeline\DealTimelineBuildAgent::getInstance();
	if(!$agent->isEnabled())
	{
		__CrmDealListEndResonse(array('STATUS' => 'COMPLETED'));
	}

	$progressData = $agent->getProgressData();
	__CrmDealListEndResonse(
		array(
			'STATUS' => 'PROGRESS',
			'PROCESSED_ITEMS' => $progressData['PROCESSED_ITEMS'],
			'TOTAL_ITEMS' => $progressData['TOTAL_ITEMS'],
		)
	);
}
elseif ($action === 'BUILD_RECURRING_TIMELINE')
{
	$agent = \Thurly\Crm\Agent\Timeline\RecurringDealTimelineBuildAgent::getInstance();
	if(!$agent->isEnabled())
	{
		__CrmDealListEndResonse(array('STATUS' => 'COMPLETED'));
	}

	$progressData = $agent->getProgressData();
	__CrmDealListEndResonse(
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
	$stageID = isset($_REQUEST['VALUE']) ? $_REQUEST['VALUE'] : '';

	if($stageID === '' || $ID <= 0  || $typeName !== CCrmOwnerType::DealName)
	{
		__CrmDealListEndResonse(array('ERROR' => 'Invalid data.'));
	}

	if (!CCrmDeal::CheckUpdatePermission($ID, $userPerms))
	{
		__CrmDealListEndResonse(array('ERROR' => 'Access denied.'));
	}

	$dbResult = CCrmDeal::GetListEx(array(), array('=ID' => $ID,'CHECK_PERMISSIONS' => 'N'));
	$arPreviousFields = $dbResult->Fetch();
	if(!is_array($arPreviousFields))
	{
		__CrmDealListEndResonse(array('ERROR' => 'Not found.'));
	}

	if(isset($arPreviousFields['STAGE_ID']) && $arPreviousFields['STAGE_ID'] === $stageID)
	{
		__CrmDealListEndResonse(array('TYPE' => CCrmOwnerType::DealName, 'ID' => $ID, 'VALUE' => $stageID));
	}

	$arFields = array('STAGE_ID' => $stageID);
	$CCrmDeal = new CCrmDeal(false);
	if($CCrmDeal->Update($ID, $arFields, true, true, array('DISABLE_USER_FIELD_CHECK' => true, 'REGISTER_SONET_EVENT' => true)))
	{
		$arErrors = array();
		CCrmBizProcHelper::AutoStartWorkflows(
			CCrmOwnerType::Deal,
			$ID,
			CCrmBizProcEventType::Edit,
			$arErrors
		);

		//Region automation
		\Thurly\Crm\Automation\Factory::runOnStatusChanged(\CCrmOwnerType::Deal, $ID);
		//end region
	}

	__CrmDealListEndResonse(array('TYPE' => CCrmOwnerType::DealName, 'ID' => $ID, 'VALUE' => $stageID));
}
elseif ($action === 'REBUILD_STATISTICS')
{
	//~CRM_REBUILD_DEAL_STATISTICS
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmDeal::CheckUpdatePermission(0))
	{
		__CrmDealListEndResonse(array('ERROR' => 'Access denied.'));
	}

	if(COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_STATISTICS', 'N') !== 'Y')
	{
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'NOT_REQUIRED',
				'SUMMARY' => GetMessage('CRM_DEAL_LIST_REBUILD_STATISTICS_NOT_REQUIRED_SUMMARY')
			)
		);
	}

	$progressData = COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_STATISTICS_PROGRESS',  '');
	$progressData = $progressData !== '' ? unserialize($progressData) : array();
	$lastItemID = isset($progressData['LAST_ITEM_ID']) ? intval($progressData['LAST_ITEM_ID']) : 0;
	$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? intval($progressData['PROCESSED_ITEMS']) : 0;
	$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? intval($progressData['TOTAL_ITEMS']) : 0;
	if($totalItemQty <= 0)
	{
		$totalItemQty = CCrmDeal::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}

	$filter = array('CHECK_PERMISSIONS' => 'N');
	if($lastItemID > 0)
	{
		$filter['>ID'] = $lastItemID;
	}

	$dbResult = CCrmDeal::GetListEx(
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
		CCrmDeal::RebuildStatistics($itemIDs, array('FORCED' => true));

		$progressData['TOTAL_ITEMS'] = $totalItemQty;
		$processedItemQty += $itemQty;
		$progressData['PROCESSED_ITEMS'] = $processedItemQty;
		$progressData['LAST_ITEM_ID'] = $itemIDs[$itemQty - 1];

		COption::SetOptionString('crm', '~CRM_REBUILD_DEAL_STATISTICS_PROGRESS', serialize($progressData));
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_DEAL_LIST_REBUILD_STATISTICS_PROGRESS_SUMMARY',
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
		COption::RemoveOption('crm', '~CRM_REBUILD_DEAL_STATISTICS');
		COption::RemoveOption('crm', '~CRM_REBUILD_DEAL_STATISTICS_PROGRESS');
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_DEAL_LIST_REBUILD_STATISTICS_COMPLETED_SUMMARY',
					array('#PROCESSED_ITEMS#' => $processedItemQty)
				)
			)
		);
	}
}
elseif ($action === 'REBUILD_SUM_STATISTICS')
{
	//~CRM_REBUILD_DEAL_SUM_STATISTICS
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmDeal::CheckUpdatePermission(0))
	{
		__CrmDealListEndResonse(array('ERROR' => 'Access denied.'));
	}

	if(COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_SUM_STATISTICS', 'N') !== 'Y')
	{
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'NOT_REQUIRED',
				'SUMMARY' => GetMessage('CRM_DEAL_LIST_REBUILD_STATISTICS_NOT_REQUIRED_SUMMARY')
			)
		);
	}

	$progressData = COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_SUM_STATISTICS_PROGRESS',  '');
	$progressData = $progressData !== '' ? unserialize($progressData) : array();
	$lastItemID = isset($progressData['LAST_ITEM_ID']) ? intval($progressData['LAST_ITEM_ID']) : 0;
	$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? intval($progressData['PROCESSED_ITEMS']) : 0;
	$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? intval($progressData['TOTAL_ITEMS']) : 0;
	if($totalItemQty <= 0)
	{
		$totalItemQty = CCrmDeal::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}

	$filter = array('CHECK_PERMISSIONS' => 'N');
	if($lastItemID > 0)
	{
		$filter['>ID'] = $lastItemID;
	}

	$dbResult = CCrmDeal::GetListEx(
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
		CCrmDeal::RebuildStatistics(
			$itemIDs,
			array(
				'FORCED' => true,
				'ENABLE_SUM_STATISTICS' => true,
				'ENABLE_HISTORY'=> false,
				'ENABLE_INVOICE_STATISTICS' => false,
				'ENABLE_ACTIVITY_STATISTICS' => false
			)
		);

		$progressData['TOTAL_ITEMS'] = $totalItemQty;
		$processedItemQty += $itemQty;
		$progressData['PROCESSED_ITEMS'] = $processedItemQty;
		$progressData['LAST_ITEM_ID'] = $itemIDs[$itemQty - 1];

		COption::SetOptionString('crm', '~CRM_REBUILD_DEAL_SUM_STATISTICS_PROGRESS', serialize($progressData));
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_DEAL_LIST_REBUILD_STATISTICS_PROGRESS_SUMMARY',
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
		COption::RemoveOption('crm', '~CRM_REBUILD_DEAL_SUM_STATISTICS');
		COption::RemoveOption('crm', '~CRM_REBUILD_DEAL_SUM_STATISTICS_PROGRESS');
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_DEAL_LIST_REBUILD_STATISTICS_COMPLETED_SUMMARY',
					array('#PROCESSED_ITEMS#' => $processedItemQty)
				)
			)
		);
	}
}
elseif ($action === 'REBUILD_SEMANTICS')
{
	//~CRM_REBUILD_DEAL_SEMANTICS
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmDeal::CheckUpdatePermission(0))
	{
		__CrmDealListEndResonse(array('ERROR' => 'Access denied.'));
	}

	if(COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_SEMANTICS', 'N') !== 'Y')
	{
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'NOT_REQUIRED',
				'SUMMARY' => GetMessage('CRM_DEAL_LIST_REBUILD_SEMANTICS_NOT_REQUIRED_SUMMARY')
			)
		);
	}

	$progressData = COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_SEMANTICS_PROGRESS',  '');
	$progressData = $progressData !== '' ? unserialize($progressData) : array();
	$lastItemID = isset($progressData['LAST_ITEM_ID']) ? intval($progressData['LAST_ITEM_ID']) : 0;
	$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? intval($progressData['PROCESSED_ITEMS']) : 0;
	$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? intval($progressData['TOTAL_ITEMS']) : 0;
	if($totalItemQty <= 0)
	{
		$totalItemQty = CCrmDeal::GetListEx(array(), array('CHECK_PERMISSIONS' => 'N'), array(), false);
	}

	$filter = array('CHECK_PERMISSIONS' => 'N');
	if($lastItemID > 0)
	{
		$filter['>ID'] = $lastItemID;
	}

	$dbResult = CCrmDeal::GetListEx(
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
		CCrmDeal::RebuildSemantics($itemIDs, array('FORCED' => true));

		$progressData['TOTAL_ITEMS'] = $totalItemQty;
		$processedItemQty += $itemQty;
		$progressData['PROCESSED_ITEMS'] = $processedItemQty;
		$progressData['LAST_ITEM_ID'] = $itemIDs[$itemQty - 1];

		COption::SetOptionString('crm', '~CRM_REBUILD_DEAL_SEMANTICS_PROGRESS', serialize($progressData));
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'PROGRESS',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_DEAL_LIST_REBUILD_SEMANTICS_PROGRESS_SUMMARY',
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
		COption::RemoveOption('crm', '~CRM_REBUILD_DEAL_SEMANTICS');
		COption::RemoveOption('crm', '~CRM_REBUILD_DEAL_SEMANTICS_PROGRESS');
		__CrmDealListEndResonse(
			array(
				'STATUS' => 'COMPLETED',
				'PROCESSED_ITEMS' => $processedItemQty,
				'TOTAL_ITEMS' => $totalItemQty,
				'SUMMARY' => GetMessage(
					'CRM_DEAL_LIST_REBUILD_SEMANTICS_COMPLETED_SUMMARY',
					array('#PROCESSED_ITEMS#' => $processedItemQty)
				)
			)
		);
	}
}
elseif ($action === 'GET_ROW_COUNT')
{
	\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

	if(!CCrmDeal::CheckReadPermission(0, $userPerms))
	{
		__CrmDealListEndResonse(array('ERROR' => 'Access denied.'));
	}

	$params = isset($_REQUEST['PARAMS']) && is_array($_REQUEST['PARAMS']) ? $_REQUEST['PARAMS'] : array();
	$gridID = isset($params['GRID_ID']) ? $params['GRID_ID'] : '';
	if(!($gridID !== ''
		&& isset($_SESSION['CRM_GRID_DATA'])
		&& isset($_SESSION['CRM_GRID_DATA'][$gridID])
		&& is_array($_SESSION['CRM_GRID_DATA'][$gridID])))
	{
		__CrmDealListEndResonse(array('DATA' => array('TEXT' => '')));
	}

	$gridData = $_SESSION['CRM_GRID_DATA'][$gridID];
	$filter = isset($gridData['FILTER']) && is_array($gridData['FILTER']) ? $gridData['FILTER'] : array();
	if ($filter['IS_RECURRING'] === 'Y' || $filter['=IS_RECURRING'] === 'Y')
	{
		$options['FIELD_OPTIONS']['ADDITIONAL_FIELDS'][] = 'RECURRING';
	}
	else
	{
		$options = array();
	}

	$result = CCrmDeal::GetListEx(array(), $filter, array(), false, array(), $options);

	$text = '';
	if(is_numeric($result))
	{
		$text = GetMessage('CRM_DEAL_LIST_ROW_COUNT', array('#ROW_COUNT#' => $result));
		if($text === '')
		{
			$text = $result;
		}
	}
	__CrmDealListEndResonse(array('DATA' => array('TEXT' => $text)));
}
?>