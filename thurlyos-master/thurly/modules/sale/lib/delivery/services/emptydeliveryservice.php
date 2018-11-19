<?php

namespace Thurly\Sale\Delivery\Services;

use Thurly\Main\Application;
use Thurly\Main\Localization\Loc;
use Thurly\Currency;
use Thurly\Sale\Internals\ServiceRestrictionTable;

Loc::loadMessages(__FILE__);

/**
 * Class EmptyDeliveryService
 * @package Thurly\Sale\Delivery\Services
 */

class EmptyDeliveryService extends Configurable
{
	const CACHE_ID = 'THURLY_SALE_EMPTY_DELIVERY_SRV_ID';
	const TTL = 31536000;

	/**
	 * @return string Class title.
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_DLVR_HANDL_EMP_DLV_SRV_TITLE');
	}

	/**
	 * @return string Class, service description.
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_DLVR_HANDL_EMP_DLV_SRV_DESC');
	}

	/**
	 * @return int
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function getEmptyDeliveryServiceId()
	{
		$id = 0;
		$cacheManager = Application::getInstance()->getManagedCache();

		if($cacheManager->read(self::TTL, self::CACHE_ID))
			$id = $cacheManager->get(self::CACHE_ID);

		if ($id <= 0)
		{
			$data = Table::getRow(
				array(
					'select' => array('ID'),
					'filter' => array('=CLASS_NAME' => '\Thurly\Sale\Delivery\Services\EmptyDeliveryService')
				)
			);
			if ($data !== null)
				$id = $data['ID'];
			else
				$id = self::create();

			if ($id > 0)
				$cacheManager->set(self::CACHE_ID, $id);
		}

		return $id;
	}

	/**
	 * @return int
	 * @throws \Thurly\Main\ArgumentException
	 */
	private static function create()
	{
		$fields["NAME"] = Loc::getMessage('SALE_DLVR_HANDL_EMP_DLV_SRV_TITLE');
		$fields["CLASS_NAME"] = '\Thurly\Sale\Delivery\Services\EmptyDeliveryService';
		$fields["PARENT_ID"] = 0;
		$fields["CURRENCY"] = Currency\CurrencyManager::getBaseCurrency();
		$fields["ACTIVE"] = "Y";
		$fields["CONFIG"] = array('MAIN' => array('CURRENCY' => Currency\CurrencyManager::getBaseCurrency(), 'PRICE' => 0, 'PERIOD' => array('FROM' => 0,'TO' => 0,'TYPE' => 'D')));
		$fields["SORT"] = 100;

		$res = Table::add($fields);

		if (!$res->isSuccess())
			return 0;

		ServiceRestrictionTable::add(array('SORT' => 100, 'SERVICE_ID' => $res->getId(), 'PARAMS' => array('PUBLIC_SHOW' => 'N'), 'SERVICE_TYPE' => '0', 'CLASS_NAME' => '\Thurly\Sale\Delivery\Restrictions\ByPublicMode'));

		return $res->getId();
	}
}