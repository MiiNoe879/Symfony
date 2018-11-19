<?php
namespace Thurly\Sale\Services\Company\Restrictions;

use Thurly\Main\Localization\Loc;
use Thurly\Sale\Order;
use Thurly\Sale\Payment;
use Thurly\Sale\PaymentCollection;
use Thurly\Sale\Services\Base\Restriction;
use Thurly\Sale\Internals;
use Thurly\Sale\Services\Company;
use Thurly\Sale\Shipment;
use Thurly\Sale\ShipmentCollection;

Loc::loadMessages(__FILE__);

class EntityType extends Restriction
{
	const ENTITY_NONE = 'N';
	const ENTITY_PAYMENT = 'P';
	const ENTITY_SHIPMENT = 'S';
	const ENTITY_ORDER = 'O';

	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_COMPANY_RULES_BY_ENTITY_TITLE');
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_COMPANY_RULES_BY_ENTITY_DESC');
	}


	/**
	 * @param int $entityId
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0)
	{
		return array(
			"ENTITY_TYPE" => array(
				"TYPE" => "ENUM",
				"LABEL" => Loc::getMessage("SALE_COMPANY_RULES_BY_ENTITY"),
				"OPTIONS" => array(
					self::ENTITY_NONE => Loc::getMessage('SALE_COMPANY_RULES_BY_ENTITY_NONE'),
					self::ENTITY_PAYMENT => Loc::getMessage('SALE_COMPANY_RULES_BY_ENTITY_PAYMENT'),
					self::ENTITY_SHIPMENT => Loc::getMessage('SALE_COMPANY_RULES_BY_ENTITY_SHIPMENT'),
					self::ENTITY_ORDER => Loc::getMessage('SALE_COMPANY_RULES_BY_ENTITY_ORDER'),
				)
			)
		);
	}


	/**
	 * @param Internals\Entity $entity
	 * @return string
	 */
	protected static function extractParams(Internals\Entity $entity)
	{
		/** @var PaymentCollection|ShipmentCollection $collection */
		if ($entity instanceof Payment)
			return self::ENTITY_PAYMENT;

		if ($entity instanceof Shipment)
			return self::ENTITY_SHIPMENT;

		if ($entity instanceof Order)
			return self::ENTITY_ORDER;

		return self::ENTITY_NONE;
	}

	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		return $params == $restrictionParams['ENTITY_TYPE'];
	}

	/**
	 * @param $mode
	 * @return int
	 */
	public static function getSeverity($mode)
	{
		return Company\Restrictions\Manager::SEVERITY_STRICT;
	}
}