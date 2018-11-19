<?php

namespace Thurly\Sale\Delivery\Restrictions;

use Thurly\Sale\Internals\CollectableEntity;
use Thurly\Sale\Internals\Entity;
use Thurly\Sale\Internals\PersonTypeTable;
use Thurly\Sale\ShipmentCollection;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Order;

Loc::loadMessages(__FILE__);

class ByPersonType extends Base
{
	/**
	 * @param $personTypeId
	 * @param array $params
	 * @param int $deliveryId
	 * @return bool
	 */
	public static function check($personTypeId, array $params, $deliveryId = 0)
	{
		if (is_array($params) && isset($params['PERSON_TYPE_ID']))
		{
			return in_array($personTypeId, $params['PERSON_TYPE_ID']);
		}

		return true;
	}

	/**
	 * @param Entity $entity
	 * @return int
	 */
	public static function extractParams(Entity $entity)
	{
		if ($entity instanceof CollectableEntity)
		{
			/** @var \Thurly\Sale\ShipmentCollection $collection */
			$collection = $entity->getCollection();

			/** @var \Thurly\Sale\Order $order */
			$order = $collection->getOrder();
		}
		elseif ($entity instanceof Order)
		{
			/** @var \Thurly\Sale\Order $order */
			$order = $entity;
		}

		if (!$order)
			return false;

		$personTypeId = $order->getPersonTypeId();
		return $personTypeId;
	}

	/**
	 * @return mixed
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_DLVR_RSTR_BY_PERSON_TYPE');
	}

	/**
	 * @return mixed
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_DLVR_RSTR_BY_PERSON_TYPE_DESC');
	}

	/**
	 * @param $deliveryId
	 * @return array
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function getParamsStructure($deliveryId = 0)
	{
		$personTypeList = array();

		$dbRes = PersonTypeTable::getList();

		while ($personType = $dbRes->fetch())
			$personTypeList[$personType["ID"]] = $personType["NAME"]." (".$personType["ID"].")";

		return array(
			"PERSON_TYPE_ID" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("SALE_DLVR_RSTR_BY_PERSON_TYPE_NAME"),
				"OPTIONS" => $personTypeList
			)
		);
	}

	/**
	 * @param $mode
	 * @return int
	 */
	public static function getSeverity($mode)
	{
		return Manager::SEVERITY_STRICT;
	}
}