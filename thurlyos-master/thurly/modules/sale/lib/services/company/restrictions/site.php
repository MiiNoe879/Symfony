<?php
namespace Thurly\Sale\Services\Company\Restrictions;

use Thurly\Sale\Order;
use Thurly\Sale\Payment;
use Thurly\Sale\PaymentCollection;
use Thurly\Sale\Services;
use Thurly\Sale\Internals;
use Thurly\Sale\Shipment;
use Thurly\Sale\ShipmentCollection;

/**
 * Class Site
 * @package Thurly\Sale\Services\Company\Restrictions
 */
class Site extends Services\PaySystem\Restrictions\Site
{
	protected static function extractParams(Internals\Entity $entity)
	{
		if (!($entity instanceof Payment) && !($entity instanceof Shipment) && !($entity instanceof Order))
			return false;


		if ($entity instanceof Order)
		{
			$order = $entity;
		}
		else
		{
			/** @var PaymentCollection|ShipmentCollection $collection */
			$collection = $entity->getCollection();

			/** @var Order $order */
			$order = $collection->getOrder();
		}

		return $order->getSiteId();
	}
}