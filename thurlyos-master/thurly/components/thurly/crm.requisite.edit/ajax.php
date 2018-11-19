<?php
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

use Thurly\Crm\EntityRequisite;

global $DB, $APPLICATION;
if(!function_exists('__CrmRequisiteEditEndResonse'))
{
	function __CrmRequisiteEditEndResonse($result)
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
		header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
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
	__CrmRequisiteEditEndResonse(array('ERROR' => 'Could not include crm module.'));
}
/*
 * 'FIND_LOCALITIES'
 */

if (!CCrmSecurityHelper::IsAuthorized() || !check_thurly_sessid())
{
	__CrmRequisiteEditEndResonse(array('ERROR' => 'Access denied.'));
}
if ($_SERVER['REQUEST_METHOD'] != 'POST')
{
	__CrmRequisiteEditEndResonse(array('ERROR' => 'Request method is not allowed.'));
}

//\Thurly\Main\Localization\Loc::loadMessages(__FILE__);
CUtil::JSPostUnescape();
$GLOBALS['APPLICATION']->RestartBuffer();
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

$action = isset($_POST['ACTION']) ? $_POST['ACTION'] : '';
if($action === 'FIND_LOCALITIES')
{
	$localityType = isset($_POST['LOCALITY_TYPE']) ? $_POST['LOCALITY_TYPE'] : 'COUNTRY';
	$needle = isset($_POST['NEEDLE']) ? $_POST['NEEDLE'] : '';
	if($localityType === 'COUNTRY')
	{
		$result = \Thurly\Crm\EntityAddress::getCountries(array('CAPTION' => $needle));
		__CrmRequisiteEditEndResonse(array('DATA' => array('ITEMS' => $result)));
	}
	else
	{
		__CrmRequisiteEditEndResonse(array('ERROR' => "Locality '{$localityType}' is not supported in current context."));
	}
}
elseif($action === 'RESOLVE_EXTERNAL_CLIENT')
{
	$propertyTypeID = isset($_POST['PROPERTY_TYPE_ID']) ? strtoupper($_POST['PROPERTY_TYPE_ID']) : '';
	$propertyValue = isset($_POST['PROPERTY_VALUE']) ? $_POST['PROPERTY_VALUE'] : '';
	$countryID = isset($_POST['COUNTRY_ID']) ? $_POST['COUNTRY_ID'] : '';

	$result = \Thurly\Crm\Integration\ClientResolver::resolve(
		$propertyTypeID,
		$propertyValue,
		$countryID
	);
	__CrmRequisiteEditEndResonse(array('DATA' => array('ITEMS' => $result)));
}
elseif($action === 'GET_ENTITY_ADDRESS')
{
	$entityTypeID = isset($_POST['ENTITY_TYPE_ID']) ? (int)$_POST['ENTITY_TYPE_ID'] : CCrmOwnerType::Undefined;
	$entityID = isset($_POST['ENTITY_ID']) ? (int)$_POST['ENTITY_ID'] : 0;
	$typeID = isset($_POST['TYPE_ID']) ? (int)$_POST['TYPE_ID'] : 0;

	$fields = \Thurly\Crm\EntityAddress::getByOwner($typeID, $entityTypeID, $entityID);
	__CrmRequisiteEditEndResonse(
		array(
			'DATA' => array(
				'ENTITY_TYPE_ID' => $entityTypeID,
				'ENTITY_ID' => $entityID,
				'FIELDS' => $fields
			)
		)
	);
}
elseif ($action === 'FIND_DUPLICATES')
{
	$userPermissions = CCrmPerms::GetCurrentUserPermissions();
	$params = isset($_POST['PARAMS']) && is_array($_POST['PARAMS']) ? $_POST['PARAMS'] : array();
	$entityTypeName = isset($params['ENTITY_TYPE_NAME']) ? $params['ENTITY_TYPE_NAME'] : '';
	$entityID = isset($params['ENTITY_ID']) ? (int)$params['ENTITY_ID'] : 0;
	if($entityTypeName === '')
	{
		__CrmRequisiteEditEndResonse(array('ERROR' => 'Entity type is not specified.'));
	}

	$entityTypeID = CCrmOwnerType::ResolveID($entityTypeName);
	if($entityTypeID === CCrmOwnerType::Undefined)
	{
		__CrmRequisiteEditEndResonse(array('ERROR' => 'Undefined entity type is specified.'));
	}

	if($entityTypeID !== CCrmOwnerType::Company && $entityTypeID !== CCrmOwnerType::Contact)
	{
		__CrmRequisiteEditEndResonse(array('ERROR' => "The '{$entityTypeName}' type is not supported in current context."));
	}

	if(!EntityRequisite::checkUpdatePermissionOwnerEntity($entityTypeID, $entityID))
	{
		__CrmRequisiteEditEndResonse(array('ERROR' => 'Access denied.'));
	}

	$userProfileUrlTemplate = COption::GetOptionString("main", "TOOLTIP_PATH_TO_USER", "", SITE_ID);

	$checker = new \Thurly\Crm\Integrity\CompanyDuplicateChecker();
	$checker->setStrictComparison(false);

	$groupResults = array();
	$groupData = isset($params['GROUPS']) && is_array($params['GROUPS']) ? $params['GROUPS'] : array();
	foreach($groupData as &$group)
	{
		$fields = array();
		$fieldNames = array();

		// region Requisites
		$requisiteList = array();

		$duplicateRequsiiteFieldsMap = Thurly\Crm\EntityRequisite::getDuplicateCriterionFieldsMap();
		foreach ($duplicateRequsiiteFieldsMap as $countryId => $requisiteDupFields)
		{
			foreach ($requisiteDupFields as $requsiiteDupFieldName)
			{
				$groupId = $requsiiteDupFieldName.'|'.$countryId;
				if (is_array($group[$groupId]))
				{
					foreach ($group[$groupId] as $requisiteFields)
					{
						if (!empty($requisiteFields['ID']) && $requisiteFields['PRESET_ID'] > 0 &&
							$requisiteFields['PRESET_COUNTRY_ID'] > 0 && count($requisiteFields) > 3)
						{
							$requisitePseudoId = $requisiteFields['ID'];
							$presetId = (int)$requisiteFields['PRESET_ID'];
							$presetCountryId = (int)$requisiteFields['PRESET_COUNTRY_ID'];
							foreach ($requisiteFields as $fieldName => $value)
							{
								if (in_array($fieldName, $duplicateRequsiiteFieldsMap[$countryId], true))
								{
									if (!isset($requisiteList[$requisitePseudoId]))
									{
										$requisiteList[$requisitePseudoId] = array(
											'ID' => $requisitePseudoId,
											'PRESET_ID' => $presetId,
											'PRESET_COUNTRY_ID' => $presetCountryId
										);
									}
									$requisiteList[$requisitePseudoId][$fieldName] = $value;
								}
							}
						}
					}
				}
			}
		}
		unset($duplicateRequsiiteFieldsMap, $countryId, $requisiteDupFields, $requsiiteDupFieldName,
			$groupId, $requisiteFields, $requisitePseudoId, $presetId, $presetCountryId, $fieldName, $value);

		// region Bank details
		$duplicateBankDetailFieldsMap = Thurly\Crm\EntityBankDetail::getDuplicateCriterionFieldsMap();
		foreach ($duplicateBankDetailFieldsMap as $countryId => $bankDetailDupFields)
		{
			foreach ($bankDetailDupFields as $bankDetailDupFieldName)
			{
				$groupId = $bankDetailDupFieldName.'|'.$countryId;
				if (is_array($group[$groupId]))
				{
					foreach ($group[$groupId] as $bankDetailFields)
					{
						if (!empty($bankDetailFields['ID']) && !empty($bankDetailFields['REQUISITE_ID'])
							&& $bankDetailFields['PRESET_ID'] > 0 && $bankDetailFields['PRESET_COUNTRY_ID'] > 0
							&& count($bankDetailFields) > 4)
						{
							$bankDetailPseudoId = $bankDetailFields['ID'];
							$requisitePseudoId = $bankDetailFields['REQUISITE_ID'];
							$presetId = (int)$bankDetailFields['PRESET_ID'];
							$presetCountryId = (int)$bankDetailFields['PRESET_COUNTRY_ID'];
							foreach ($bankDetailFields as $fieldName => $value)
							{
								if (in_array($fieldName, $duplicateBankDetailFieldsMap[$countryId], true))
								{
									if (!isset($requisiteList[$requisitePseudoId]))
									{
										$requisiteList[$requisitePseudoId] = array(
											'ID' => $requisitePseudoId,
											'PRESET_ID' => $presetId,
											'PRESET_COUNTRY_ID' => $presetCountryId
										);
									}
									if (!isset($requisiteList[$requisitePseudoId]['BD']))
										$requisiteList[$requisitePseudoId]['BD'] = array();
									$bankDetailList = &$requisiteList[$requisitePseudoId]['BD'];
									if (!isset($bankDetailList[$bankDetailPseudoId]))
									{
										$bankDetailList[$bankDetailPseudoId] = array(
											'ID' => $bankDetailPseudoId,
											'ENTITY_TYPE_ID' => CCrmOwnerType::Requisite,
											'ENTITY_ID' => $requisitePseudoId,
											'COUNTRY_ID' => $presetCountryId
										);
									}
									$bankDetailList[$bankDetailPseudoId][$fieldName] = $value;
									unset($bankDetailList);
								}
							}
						}
					}
				}
			}
		}
		unset(
			$duplicateBankDetailFieldsMap, $countryId, $bankDetailDupFields, $bankDetailDupFieldName, $groupId,
			$bankDetailFields, $bankDetailPseudoId, $requisitePseudoId, $presetId, $presetCountryId, $fieldName, $value
		);
		// endregion Bank details

		if (!empty($requisiteList))
		{
			$fields['RQ'] = $requisiteList;
		}
		// endregion Requisites

		$adapter = \Thurly\Crm\EntityAdapterFactory::create($fields, CCrmOwnerType::Company);
		$dups = $checker->findDuplicates($adapter, new \Thurly\Crm\Integrity\DuplicateSearchParams($fieldNames));

		$entityInfoByType = array();
		foreach($dups as &$dup)
		{
			/** @var \Thurly\Crm\Integrity\Duplicate $dup */
			$entities = $dup->getEntities();
			if(!(is_array($entities) && !empty($entities)))
			{
				continue;
			}

			//Each entity type limited by 50 items
			foreach($entities as &$entity)
			{
				/** @var \Thurly\Crm\Integrity\DuplicateEntity $entity */
				$entityTypeID = $entity->getEntityTypeID();
				$entityID = $entity->getEntityID();

				if(!isset($entityInfoByType[$entityTypeID]))
				{
					$entityInfoByType[$entityTypeID] = array($entityID => array());
				}
				elseif(count($entityInfoByType[$entityTypeID]) < 50 && !isset($entityInfoByType[$entityTypeID][$entityID]))
				{
					$entityInfoByType[$entityTypeID][$entityID] = array();
				}
			}
		}
		unset($dup);

		$totalEntities = 0;
		$entityMultiFields = array();
		foreach($entityInfoByType as $entityTypeID => &$entityInfos)
		{
			$totalEntities += count($entityInfos);
			CCrmOwnerType::PrepareEntityInfoBatch(
				$entityTypeID,
				$entityInfos,
				false,
				array(
					'ENABLE_RESPONSIBLE' => true,
					'ENABLE_EDIT_URL' => true,
					'PHOTO_SIZE' => array('WIDTH'=> 21, 'HEIGHT'=> 21)
				)
			);

			$multiFieldResult = CCrmFieldMulti::GetListEx(
				array(),
				array(
					'=ENTITY_ID' => CCrmOwnerType::ResolveName($entityTypeID),
					'@ELEMENT_ID' => array_keys($entityInfos),
					'@TYPE_ID' => array('PHONE', 'EMAIL')
				),
				false,
				false,
				array('ELEMENT_ID', 'TYPE_ID', 'VALUE')
			);

			if(is_object($multiFieldResult))
			{
				$entityMultiFields[$entityTypeID] = array();
				while($multiFields = $multiFieldResult->Fetch())
				{
					$entityID = isset($multiFields['ELEMENT_ID']) ? intval($multiFields['ELEMENT_ID']) : 0;
					if($entityID <= 0)
					{
						continue;
					}

					if(!isset($entityMultiFields[$entityTypeID][$entityID]))
					{
						$entityMultiFields[$entityTypeID][$entityID] = array();
					}

					$typeID = isset($multiFields['TYPE_ID']) ? $multiFields['TYPE_ID'] : '';
					$value = isset($multiFields['VALUE']) ? $multiFields['VALUE'] : '';
					if($typeID === '' || $value === '')
					{
						continue;
					}

					if(!isset($entityMultiFields[$entityTypeID][$entityID][$typeID]))
					{
						$entityMultiFields[$entityTypeID][$entityID][$typeID] = array($value);
					}
					elseif(count($entityMultiFields[$entityTypeID][$entityID][$typeID]) < 10)
					{
						$entityMultiFields[$entityTypeID][$entityID][$typeID][] = $value;
					}
				}
			}
		}
		unset($entityInfos);

		$dupInfos = array();
		foreach($dups as &$dup)
		{
			if(!($dup instanceof \Thurly\Crm\Integrity\Duplicate))
			{
				continue;
			}

			$entities = $dup->getEntities();
			$entityCount = is_array($entities) ? count($entities) : 0;
			if($entityCount === 0)
			{
				continue;
			}

			$dupInfo = array('ENTITIES' => array());
			foreach($entities as &$entity)
			{
				if(!($entity instanceof \Thurly\Crm\Integrity\DuplicateEntity))
				{
					continue;
				}

				$entityTypeID = $entity->getEntityTypeID();
				$entityID = $entity->getEntityID();

				$info = array(
					'ENTITY_TYPE_ID' => $entityTypeID,
					'ENTITY_ID' => $entityID
				);

				if(isset($entityInfoByType[$entityTypeID]) && isset($entityInfoByType[$entityTypeID][$entityID]))
				{
					$entityInfo = $entityInfoByType[$entityTypeID][$entityID];
					if(isset($entityInfo['TITLE']))
					{
						$info['TITLE'] = $entityInfo['TITLE'];
					}
					if(isset($entityInfo['RESPONSIBLE_ID']))
					{
						$responsibleID = $entityInfo['RESPONSIBLE_ID'];

						$info['RESPONSIBLE_ID'] = $responsibleID;
						if(isset($entityInfo['RESPONSIBLE_FULL_NAME']))
						{
							$info['RESPONSIBLE_FULL_NAME'] = $entityInfo['RESPONSIBLE_FULL_NAME'];
						}
						if(isset($entityInfo['RESPONSIBLE_PHOTO_URL']))
						{
							$info['RESPONSIBLE_PHOTO_URL'] = $entityInfo['RESPONSIBLE_PHOTO_URL'];
						}
						$info['RESPONSIBLE_URL'] = CComponentEngine::MakePathFromTemplate(
							$userProfileUrlTemplate,
							array('user_id' => $responsibleID, 'USER_ID' => $responsibleID)
						);

						$entityTypeName = CCrmOwnerType::ResolveName($entityTypeID);
						$isReadable = CCrmAuthorizationHelper::CheckReadPermission($entityTypeName, $entityID, $userPermissions);
						$isEditable = CCrmAuthorizationHelper::CheckUpdatePermission($entityTypeName, $entityID, $userPermissions);

						if($isEditable && isset($entityInfo['EDIT_URL']))
						{
							$info['URL'] = $entityInfo['EDIT_URL'];
						}
						elseif($isReadable && isset($entityInfo['SHOW_URL']))
						{
							$info['URL'] = $entityInfo['SHOW_URL'];
						}
						else
						{
							$info['URL'] = '';
						}
					}
				}

				if(isset($entityMultiFields[$entityTypeID])
					&& isset($entityMultiFields[$entityTypeID][$entityID]))
				{
					$multiFields = $entityMultiFields[$entityTypeID][$entityID];
					if(isset($multiFields['PHONE']))
					{
						$info['PHONE'] = $multiFields['PHONE'];
					}
					if(isset($multiFields['EMAIL']))
					{
						$info['EMAIL'] = $multiFields['EMAIL'];
					}
				}

				$dupInfo['ENTITIES'][] = &$info;
				unset($info);
			}
			unset($entity);

			$criterion = $dup->getCriterion();
			if($criterion instanceof \Thurly\Crm\Integrity\DuplicateCriterion)
			{
				$dupInfo['CRITERION'] = array(
					'TYPE_NAME' => $criterion->getTypeName(),
					'MATCHES' => $criterion->getMatches()
				);
			}
			$dupInfos[] = &$dupInfo;
			unset($dupInfo);
		}
		unset($dup);

		$groupResults[] = array(
			'DUPLICATES' => &$dupInfos,
			'GROUP_ID' => isset($group['GROUP_ID']) ? $group['GROUP_ID'] : '',
			'FIELD_ID' => isset($group['FIELD_ID']) ? $group['FIELD_ID'] : '',
			'HASH_CODE' => isset($group['HASH_CODE']) ? intval($group['HASH_CODE']) : 0,
			'ENTITY_TOTAL_TEXT' => \Thurly\Crm\Integrity\Duplicate::entityCountToText($totalEntities)
		);
		unset($dupInfos);
	}
	unset($group);

	__CrmRequisiteEditEndResonse(array('GROUP_RESULTS' => $groupResults));
}
else
{
	__CrmRequisiteEditEndResonse(array('ERROR' => "Action '{$action}' is not supported in current context."));
}