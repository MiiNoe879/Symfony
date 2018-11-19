<?php
namespace Thurly\Sale\Services\PaySystem\Restrictions;

use Thurly\Main\Localization\Loc;
use Thurly\Sale\Delivery\Services;
use Thurly\Sale\Internals\CollectableEntity;
use Thurly\Sale\Internals\DeliveryPaySystemTable;
use Thurly\Sale\Internals\Entity;
use Thurly\Sale\Order;
use Thurly\Sale\Payment;
use Thurly\Sale\PaymentCollection;
use Thurly\Sale\Services\Base\Restriction;
use Thurly\Sale\Services\Base\RestrictionManager;
use Thurly\Sale\ShipmentCollection;

Loc::loadMessages(__FILE__);

/**
 * Class Delivery
 * @package Thurly\Sale\Services\PaySystem\Restrictions
 */
class Delivery extends Restriction
{
	public static $easeSort = 200;
	protected static $preparedData = array();

	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_SRV_RSTR_BY_DELIVERY_NAME");
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_SRV_RSTR_BY_DELIVERY_DESC");
	}

	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		if(intval($serviceId) <= 0)
			return true;

		if(empty($params))
			return true;

		$deliveries = self::getDeliveryByPaySystemsId($serviceId);

		if(empty($deliveries))
			return true;

		$diff = array_diff($params, $deliveries);

		return empty($diff);
	}

	/**
	 * @param Entity $entity
	 * @return array
	 */
	protected static function extractParams(Entity $entity)
	{
		$shipmentCollection = null;
		$result = array();

		if ($entity instanceof Payment)
		{
			/** @var PaymentCollection $paymentCollection */
			$paymentCollection = $entity->getCollection();

			/** @var Order $order */
			$order = $paymentCollection->getOrder();

			/** @var ShipmentCollection $shipmentCollection */
			$shipmentCollection = $order->getShipmentCollection();

		}
		elseif ($entity instanceof Order)
		{
			$shipmentCollection = $entity->getShipmentCollection();
		}

		if ($shipmentCollection)
		{
			/** @var \Thurly\Sale\Shipment $shipment */
			foreach ($shipmentCollection as $shipment)
			{
				if (!$shipment->isSystem())
				{
					$deliveryId = $shipment->getDeliveryId();
					if ($deliveryId)
						$result[] = $deliveryId;
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	protected static function getDeliveryServiceList()
	{
		static $result = null;

		if ($result !== null)
			return $result;

		$serviceList = array();
		$dbRes = Services\Table::getList(array('select' => array('ID', 'NAME', 'PARENT_ID', 'CLASS_NAME')));
		while ($service = $dbRes->fetch())
			$serviceList[$service['ID']] = $service;

		foreach ($serviceList as $service)
		{
			if (is_callable($service['CLASS_NAME'].'::canHasChildren') && $service['CLASS_NAME']::canHasChildren())
				continue;

			if ((int)$service['PARENT_ID'] > 0 && array_key_exists($service['PARENT_ID'], $serviceList))
			{
				$parentService = $serviceList[$service['PARENT_ID']];

				if (is_callable($parentService['CLASS_NAME'].'::canHasChildren') && $parentService['CLASS_NAME']::canHasChildren())
					$name = $service['NAME'].' ['.$service['ID'].']';
				else
					$name = $parentService['NAME'].': '.$service['NAME'].' ['.$service['ID'].']';
			}
			else
			{
				$name = $service['NAME'].' ['.$service['ID'].']';
			}

			$result[$service['ID']] = $name;
		}

		return $result;
	}

	/**
	 * @param int $entityId
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0)
	{
		$result =  array(
			"DELIVERY" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("SALE_SRV_RSTR_BY_DELIVERY_PRM_PS"),
				"OPTIONS" => self::getDeliveryServiceList()
			)
		);

		return $result;
	}

	/**
	 * @param int $paySystemId
	 * @return array|\int[]
	 * @throws \Thurly\Main\ArgumentOutOfRangeException
	 */
	protected static function getDeliveryByPaySystemsId($paySystemId = 0)
	{
		if ($paySystemId == 0)
			return array();

		$result = DeliveryPaySystemTable::getLinks($paySystemId, DeliveryPaySystemTable::ENTITY_TYPE_PAYSYSTEM, self::$preparedData);
		return $result;
	}

	/**
	 * @param array $params
	 * @param int $paySystemId
	 * @return array
	 * @throws \Thurly\Main\ArgumentNullException
	 * @throws \Thurly\Main\ArgumentOutOfRangeException
	 */
	protected static function prepareParamsForSaving(array $params = array(), $paySystemId = 0)
	{
		if(intval($paySystemId) <= 0)
			return $params;

		if(isset($params["DELIVERY"]) && is_array($params["DELIVERY"]))
		{
			DeliveryPaySystemTable::setLinks(
				$paySystemId,
				DeliveryPaySystemTable::ENTITY_TYPE_PAYSYSTEM,
				$params["DELIVERY"],
				true
			);

			unset($params["DELIVERY"]);
		}

		return $params;
	}

	/**
	 * @param array $fields
	 * @param int $restrictionId
	 * @return \Thurly\Main\Entity\AddResult|\Thurly\Main\Entity\UpdateResult
	 */
	public static function save(array $fields, $restrictionId = 0)
	{
		$params = $fields["PARAMS"];
		$fields["PARAMS"] = array();

		$result = parent::save($fields, $restrictionId);

		self::prepareParamsForSaving($params, $fields["SERVICE_ID"]);
		return $result;
	}

	/**
	 * @param array $paramsValues
	 * @param int $entityId
	 * @return array
	 */
	public static function prepareParamsValues(array $paramsValues, $entityId = 0)
	{
		return array("DELIVERY" => self::getDeliveryByPaySystemsId($entityId));
	}

	/**
	 * @param $restrictionId
	 * @param int $entityId
	 * @return \Thurly\Main\Entity\DeleteResult
	 * @throws \Thurly\Main\ArgumentNullException
	 * @throws \Thurly\Main\ArgumentOutOfRangeException
	 */
	public static function delete($restrictionId, $entityId = 0)
	{
		DeliveryPaySystemTable::setLinks(
			$entityId,
			DeliveryPaySystemTable::ENTITY_TYPE_PAYSYSTEM,
			array(),
			true
		);

		return parent::delete($restrictionId);
	}

	/**
	 * @param array $servicesIds
	 * @return string
	 */
	public static function prepareData(array $servicesIds)
	{
		if(empty($servicesIds))
			return;

		self::$preparedData = DeliveryPaySystemTable::prepareData($servicesIds, DeliveryPaySystemTable::ENTITY_TYPE_PAYSYSTEM);
	}
} 