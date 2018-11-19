<?php
namespace Thurly\Crm\Entity;

use Thurly\Main;

class EntityEditor
{
	/**
	 * Prepare Multifield Data for save in Destination Entity.
	 * @param array $data Source Multifield Data.
	 * @param array $entityFields Destination Entity Fields.
	 */
	public static function internalizeMultifieldData(array $data, array &$entityFields)
	{
		foreach($data as $typeName => $items)
		{
			if(!isset($entityFields[$typeName]))
			{
				$entityFields[$typeName] = array();
			}

			foreach($items as $itemID => $item)
			{
				$entityFields[$typeName][] = array_merge(array('ID' => $itemID), $item);
			}
		}
	}

	/**
	 * @param \Thurly\Crm\Conversion\EntityConversionWizard $wizard
	 * @param int $entityTypeID
	 * @param array $entityFields
	 * @param array $userFields
	 */
	public static function prepareConvesionMap($wizard, $entityTypeID, array &$entityFields, array &$userFields)
	{
		$mappedFields = $wizard->mapEntityFields($entityTypeID, array('ENABLE_FILES' => false));
		foreach($mappedFields as $k => $v)
		{
			if(strpos($k, 'UF_CRM') === 0)
			{
				$userFields[$k] = $v;
			}
			elseif($k === 'FM')
			{
				self::internalizeMultifieldData($v, $entityFields);
			}
			else
			{
				$entityFields[$k] = $v;
			}
		}
	}

	/**
	 * Get User Selector Context
	 * Can be used in CSocNetLogDestination::GetDestinationSort
	 * @return string
	 */
	public static function getUserSelectorContext()
	{
		return 'CRM_ENTITY_EDITOR';
	}

	/**
	 * Save selected User in Finder API
	 * @param int $userID User ID.
	 * @return void
	 */
	public static function registerSelectedUser($userID)
	{
		if(!is_int($userID))
		{
			$userID = (int)$userID;
		}

		if($userID > 0)
		{
			Main\FinderDestTable::merge(
				array(
					'CONTEXT' => self::getUserSelectorContext(),
					'CODE' => Main\FinderDestTable::convertRights(array("U{$userID}"))
				)
			);
		}
	}
}