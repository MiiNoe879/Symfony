<?php
namespace Thurly\Crm\Timeline;

use Thurly\Sender\Integration\Crm\Timeline\RecipientController as SenderRecipientController;

class TimelineManager
{
	/**
	 * @param array $item
	 * @return EntityController|null
	 */
	public static function resolveController(array $item)
	{
		$typeID = isset($item['TYPE_ID']) ? (int)$item['TYPE_ID'] : 0;
		$assocEntityTypeID = isset($item['ASSOCIATED_ENTITY_TYPE_ID'])
			? (int)$item['ASSOCIATED_ENTITY_TYPE_ID'] : 0;

		if($typeID === TimelineType::WAIT)
		{
			return WaitController::getInstance();
		}

		if($typeID === TimelineType::BIZPROC)
		{
			return BizprocController::getInstance();
		}

		if($typeID === TimelineType::SENDER)
		{
			return SenderRecipientController::getInstance();
		}

		if($typeID === TimelineType::COMMENT)
		{
			return CommentController::getInstance();
		}

		if($assocEntityTypeID === \CCrmOwnerType::Activity)
		{
			if($typeID === TimelineType::MODIFICATION)
			{
				$settings = isset($item['SETTINGS']) && is_array($item['SETTINGS']) ? $item['SETTINGS'] : array();
				$entity = isset($settings['ENTITY']) && is_array($settings['ENTITY']) ? $settings['ENTITY'] : array();
				$activityTypeID = isset($entity['TYPE_ID']) ? (int)$entity['TYPE_ID'] : 0;

				if($activityTypeID === \CCrmActivityType::Task)
				{
					return TaskController::getInstance();
				}
			}

			return ActivityController::getInstance();
		}
		elseif($assocEntityTypeID === \CCrmOwnerType::Lead)
		{
			return LeadController::getInstance();
		}
		elseif($assocEntityTypeID === \CCrmOwnerType::Contact)
		{
			return ContactController::getInstance();
		}
		elseif($assocEntityTypeID === \CCrmOwnerType::Company)
		{
			return CompanyController::getInstance();
		}
		elseif($assocEntityTypeID === \CCrmOwnerType::Deal)
		{
			return DealController::getInstance();
		}
		elseif($assocEntityTypeID === \CCrmOwnerType::DealRecurring)
		{
			return DealRecurringController::getInstance();
		}

		return null;
	}
	public static function prepareItemDisplayData(array &$item)
	{
		$items = array($item);
		self::prepareDisplayData($items);
		$item = $items[0];
	}
	public static function prepareDisplayData(array &$items)
	{
		$entityMap = array();
		foreach($items as $ID => $item)
		{
			$assocEntityTypeID = isset($item['ASSOCIATED_ENTITY_TYPE_ID']) ? (int)$item['ASSOCIATED_ENTITY_TYPE_ID'] : 0;
			$assocEntityID = isset($item['ASSOCIATED_ENTITY_ID']) ? (int)$item['ASSOCIATED_ENTITY_ID'] : 0;

			if($assocEntityTypeID === \CCrmOwnerType::Undefined)
			{
				continue;
			}

			if(!isset($entityMap[$assocEntityTypeID]))
			{
				$entityMap[$assocEntityTypeID] = array();
			}

			if(!isset($entityMap[$assocEntityTypeID][$assocEntityID]))
			{
				$entityMap[$assocEntityTypeID][$assocEntityID] = array('ITEM_IDS' => array());
			}

			$entityMap[$assocEntityTypeID][$assocEntityID]['ITEM_IDS'][] = $ID;
		}

		foreach($entityMap as $entityTypeID => $entityInfos)
		{
			if($entityTypeID === \CCrmOwnerType::Wait)
			{
				$entityIDs = array_keys($entityInfos);
				$dbResult = \Thurly\Crm\Pseudoactivity\Entity\WaitTable::getList(
					array('filter' => array('@ID' => $entityIDs))
				);

				while($fields = $dbResult->fetch())
				{
					$assocEntityID = (int)$fields['ID'];
					if(isset($entityInfos[$assocEntityID]))
					{
						$itemIDs = isset($entityInfos[$assocEntityID]['ITEM_IDS'])
							? $entityInfos[$assocEntityID]['ITEM_IDS'] : array();

						$fields = WaitController::prepareEntityDataModel(
							$assocEntityID,
							$fields
						);

						foreach($itemIDs as $itemID)
						{
							$items[$itemID]['ASSOCIATED_ENTITY'] = $fields;
						}
					}
				}
			}
			elseif($entityTypeID === \CCrmOwnerType::Activity)
			{
				$activityIDs = array_keys($entityInfos);
				$dbResult = \CCrmActivity::GetList(
					array(),
					array('@ID' => $activityIDs, 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array(
						'ID', 'OWNER_ID', 'OWNER_TYPE_ID', 'TYPE_ID', 'PROVIDER_ID', 'PROVIDER_TYPE_ID', 'PROVIDER_PARAMS',
						'ASSOCIATED_ENTITY_ID', 'DIRECTION', 'SUBJECT', 'STATUS', 'DEADLINE',
						'DESCRIPTION', 'DESCRIPTION_TYPE', 'ASSOCIATED_ENTITY_ID',
						'STORAGE_TYPE_ID', 'STORAGE_ELEMENT_IDS', 'ORIGIN_ID', 'SETTINGS'
					)
				);
				while($fields = $dbResult->Fetch())
				{
					$assocEntityID = (int)$fields['ID'];
					if(isset($entityInfos[$assocEntityID]))
					{
						$itemIDs = isset($entityInfos[$assocEntityID]['ITEM_IDS'])
							? $entityInfos[$assocEntityID]['ITEM_IDS'] : array();

						$fields = ActivityController::prepareEntityDataModel(
							$assocEntityID,
							$fields,
							array('ENABLE_COMMUNICATIONS' => false)
						);

						foreach($itemIDs as $itemID)
						{
							$items[$itemID]['ASSOCIATED_ENTITY'] = $fields;
						}
					}
				}

				$communications = \CCrmActivity::PrepareCommunicationInfos($activityIDs);
				foreach($communications as $assocEntityID => $info)
				{
					if(isset($entityInfos[$assocEntityID]))
					{
						$itemIDs = isset($entityInfos[$assocEntityID]['ITEM_IDS'])
							? $entityInfos[$assocEntityID]['ITEM_IDS'] : array();

						foreach($itemIDs as $itemID)
						{
							$items[$itemID]['ASSOCIATED_ENTITY']['COMMUNICATION'] = $info;
						}
					}
				}
			}
			else
			{
				if ($entityTypeID === \CCrmOwnerType::DealRecurring)
				{
					$entityTypeID = \CCrmOwnerType::Deal;
				}
				\CCrmOwnerType::PrepareEntityInfoBatch($entityTypeID, $entityInfos, false);
				foreach($entityInfos as $entityID => $entityInfo)
				{
					$itemIDs = isset($entityInfo['ITEM_IDS']) ? $entityInfo['ITEM_IDS'] : array();
					foreach($itemIDs as $itemID)
					{
						$items[$itemID]['ASSOCIATED_ENTITY'] = $entityInfo;
					}
				}
			}
		}

		$defaultController = new EntityController();
		foreach($items as $ID => &$item)
		{
			$controller = self::resolveController($item);
			if(!$controller)
			{
				$controller = $defaultController;
			}
			$item = $controller->prepareHistoryDataModel($item);
		}
		unset($item);

		EntityController::prepareAuthorInfoBulk($items);
	}
}