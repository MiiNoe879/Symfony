<?php

namespace Thurly\Sale\Services\Company\Restrictions;

use Thurly\Sale\Internals\CollectableEntity;
use Thurly\Sale\Internals\Entity;
use Thurly\Sale\Order;
use Thurly\Sale\Payment;
use Thurly\Sale\Services\PaySystem\Restrictions;
use Thurly\Sale\Shipment;

class Price extends Restrictions\Price
{
	/**
	 * @param Entity $entity
	 *
	 * @return array
	 */
	protected static function extractParams(Entity $entity)
	{
		/** @var \Thurly\Sale\PaymentCollection|\Thurly\Sale\ShipmentCollection|null $collection */
		$collection = null;

		if ($entity instanceof Payment)
			$collection = $entity->getCollection();
		elseif ($entity instanceof Shipment)
			$collection = $entity->getCollection();
		elseif ($entity instanceof Order)
		{
			return array('PRICE_PAYMENT' => $entity->getPrice());
		}

		if ($collection)
		{
			/** @var \Thurly\Sale\Order $order */
			$order = $collection->getOrder();

			return array('PRICE_PAYMENT' => $order->getPrice());
		}

		return array('PRICE_PAYMENT' => null);
	}
}