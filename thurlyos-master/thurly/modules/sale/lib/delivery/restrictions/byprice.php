<?php
namespace Thurly\Sale\Delivery\Restrictions;

use \Thurly\Main\Localization\Loc;
use Thurly\Sale\Delivery\Restrictions\Base;
use Thurly\Sale\Internals\CollectableEntity;
use Thurly\Sale\Internals\Entity;
use Thurly\Sale\Services\Base\RestrictionManager;
use Thurly\Sale\Shipment;

Loc::loadMessages(__FILE__);

/**
 * Class ByWeight
 * Restricts delivery by weight
 * @package Thurly\Sale\Delivery\Restrictions
 */
class ByPrice extends Base
{
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_PRICE_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_PRICE_DESCRIPT");
	}

	public static function check($price, array $restrictionParams, $deliveryId = 0)
	{
		if(empty($restrictionParams))
			return true;

		if($price < 0)
			return true;

		$price = floatval($price);

		if(floatval($restrictionParams["MIN_PRICE"]) > 0  && $price < floatval($restrictionParams["MIN_PRICE"]))
			$result = false;
		elseif(floatval($restrictionParams["MAX_PRICE"]) > 0 && $price > floatval($restrictionParams["MAX_PRICE"]))
			$result = false;
		else
			$result = true;

		return $result;
	}


	public static function checkByEntity(Entity $shipment, array $restrictionParams, $mode, $deliveryId = 0)
	{
		$severity = self::getSeverity($mode);

		if($severity == RestrictionManager::SEVERITY_NONE)
			return RestrictionManager::SEVERITY_NONE;

		$price = self::extractParams($shipment);
		$sCurrency = $shipment->getCurrency();

		if (!empty($sCurrency) && !empty($restrictionParams["CURRENCY"]) && \Thurly\Main\Loader::includeModule('currency'))
		{
			$price = \CCurrencyRates::convertCurrency(
				$price,
				$sCurrency,
				$restrictionParams["CURRENCY"]
			);
		}

		$res = self::check($price, $restrictionParams, $deliveryId);
		return $res ? RestrictionManager::SEVERITY_NONE : $severity;
	}

	protected static function extractParams(Entity $entity)
	{
		if ($entity instanceof Shipment)
		{
			if(!$itemCollection = $entity->getShipmentItemCollection())
				return -1;
		}
		else
		{
			return -1;
		}

		return $itemCollection->getPrice();
	}

	public static function getParamsStructure($entityId = 0)
	{
		return array(
			"MIN_PRICE" => array(
				"TYPE" => "NUMBER",
				"DEFAULT" => "0",
				'MIN' => 0,
				"LABEL" => Loc::getMessage("SALE_DLVR_RSTR_BY_PRICE_MIN_PRICE")
			),

			"MAX_PRICE" => array(
				"TYPE" => "NUMBER",
				"DEFAULT" => "0",
				'MIN' => 0,
				"LABEL" => Loc::getMessage("SALE_DLVR_RSTR_BY_PRICE_MAX_PRICE")
			),

			"CURRENCY" => array(
				"TYPE" => "ENUM",
				"DEFAULT" => "RUB",
				"LABEL" => Loc::getMessage("SALE_DLVR_RSTR_BY_PRICE_CURRECY"),
				"OPTIONS" => \Thurly\Sale\Delivery\Helper::getCurrenciesList()
			)
		);
	}
}