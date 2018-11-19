<?php
namespace Thurly\Sale\Services\Company\Restrictions;

use Thurly\Currency\CurrencyManager;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Delivery\Restrictions;
use Thurly\Sale\Internals;
use Thurly\Sale;
use Thurly\Sale\PaymentCollection;
use Thurly\Sale\ShipmentCollection;
use Thurly\Sale\Services\Base;

Loc::loadMessages(__FILE__);

/**
 * Class Currency
 * @package Thurly\Sale\Services\Company\Restrictions
 */
class Currency extends Base\Restriction
{
	/**
	 * @param Internals\Entity $entity
	 * @return string
	 */
	protected static function extractParams(Internals\Entity $entity)
	{
		if ($entity instanceof Internals\CollectableEntity)
		{
			/** @var \Thurly\Sale\ShipmentCollection $collection */
			$collection = $entity->getCollection();

			/** @var \Thurly\Sale\Order $order */
			$order = $collection->getOrder();
		}
		elseif ($entity instanceof Sale\Order)
		{
			/** @var \Thurly\Sale\Order $order */
			$order = $entity;
		}

		if (!$order)
			return false;

		return $order->getCurrency();
	}

	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_COMPANY_RULES_BY_CURRENCY_TITLE');
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_COMPANY_RULES_BY_CURRENCY_DESC');
	}

	/**
	 * @param int $entityId
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0)
	{
		return array(
			"CURRENCY" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("SALE_COMPANY_RULES_BY_CURRENCY"),
				"OPTIONS" => CurrencyManager::getCurrencyList()
			)
		);
	}

	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		if (isset($restrictionParams) && is_array($restrictionParams['CURRENCY']))
			return in_array($params, $restrictionParams['CURRENCY']);

		return true;
	}
}