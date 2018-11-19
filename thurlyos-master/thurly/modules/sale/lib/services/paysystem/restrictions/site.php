<?php
namespace Thurly\Sale\Services\PaySystem\Restrictions;

use Thurly\Main\Localization\Loc;
use Thurly\Sale\Delivery\Restrictions;
use Thurly\Sale\Internals;
use Thurly\Sale\Order;
use Thurly\Sale\Payment;
use Thurly\Sale\PaymentCollection;

Loc::loadMessages(__FILE__);

/**
 * Class Site
 * @package Thurly\Sale\Services\PaySystem\Restrictions
 */
class Site extends Restrictions\BySite
{
	/**
	 * @param Internals\Entity $entity
	 * @return null|string
	 */
	protected static function extractParams(Internals\Entity $entity)
	{
		if (!($entity instanceof Payment))
			return false;

		if ($entity instanceof Internals\CollectableEntity)
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

		return $order->getSiteId();
	}
} 