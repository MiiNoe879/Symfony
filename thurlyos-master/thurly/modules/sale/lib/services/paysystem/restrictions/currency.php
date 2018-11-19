<?php

namespace Thurly\Sale\Services\PaySystem\Restrictions;

use Thurly\Currency\CurrencyManager;
use Thurly\Main\ArgumentTypeException;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Internals\CollectableEntity;
use Thurly\Sale\Internals\Entity;
use Thurly\Sale\Order;
use Thurly\Sale\Payment;
use Thurly\Sale\PaySystem;
use Thurly\Sale\PaySystem\Service;
use Thurly\Sale\Services\Base;

Loc::loadMessages(__FILE__);

class Currency extends Base\Restriction
{
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

	/**
	 * @param Entity $entity
	 * @return string
	 * @throws ArgumentTypeException
	 */
	protected static function extractParams(Entity $entity)
	{
		if ($entity instanceof Payment)
		{
			/** @var \Thurly\Sale\PaymentCollection $collection */
			$collection = $entity->getCollection();

			/** @var \Thurly\Sale\Order $order */
			$order = $collection->getOrder();

			return $order->getCurrency();
		}
		elseif ($entity instanceof Order)
		{
			return $entity->getCurrency();
		}

		throw new ArgumentTypeException('');
	}

	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_CURRENCY');
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_PS_RESTRICTIONS_BY_CURRENCY_DESC');
	}

	public static function getParamsStructure($entityId = 0)
	{
		$data = PaySystem\Manager::getById($entityId);

		$currencyList = CurrencyManager::getCurrencyList();

		if ($data !== false)
		{
			/** @var Service $paySystem */
			$paySystem = new Service($data);
			$psCurrency = $paySystem->getCurrency();

			$options = array();
			foreach ($psCurrency as $code)
				$options[$code] = (isset($currencyList[$code])) ? $currencyList[$code] : $code;

			if ($options)
			{
				return array(
					"CURRENCY" => array(
						"TYPE" => "ENUM",
						'MULTIPLE' => 'Y',
						"LABEL" => Loc::getMessage("SALE_PS_RESTRICTIONS_BY_CURRENCY_NAME"),
						"OPTIONS" => $options
					)
				);
			}
		}

		return array();
	}

	public static function save(array $fields, $restrictionId = 0)
	{
		return parent::save($fields, $restrictionId);
	}


}