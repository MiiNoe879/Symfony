<?php
namespace Thurly\Sale\Delivery\Restrictions;

use Thurly\Sale\Delivery\Restrictions\Base;
use \Thurly\Main\Localization\Loc;
use Thurly\Sale\Internals\CollectableEntity;
use Thurly\Sale\Internals\Entity;
use Thurly\Sale\Order;

Loc::loadMessages(__FILE__);

/**
 * Class BySite
 * Restricts delivery by site
 * @package Thurly\Sale\Delivery\Restrictions
 */
class BySite extends Base
{
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_SITE_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_SITE_DESCRIPT");
	}
	public static function check($siteId, array $restrictionParams, $deliveryId = 0)
	{
		if(empty($restrictionParams))
			return true;

		$result = true;

		if(strlen($siteId) > 0 && isset($restrictionParams["SITE_ID"]) && is_array($restrictionParams["SITE_ID"]))
			$result = in_array($siteId, $restrictionParams["SITE_ID"]);

		return $result;
	}

	protected static function extractParams(Entity $entity)
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

		return $order->getSiteId();
	}

	public static function getParamsStructure($entityId = 0)
	{
		$siteList = array();

		$rsSite = \Thurly\Main\SiteTable::getList();

		while ($site = $rsSite->fetch())
			$siteList[$site["LID"]] = $site["NAME"]." (".$site["LID"].")";

		return array(
			"SITE_ID" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"DEFAULT" => SITE_ID,
				"LABEL" => Loc::getMessage("SALE_DLVR_RSTR_BY_SITE_SITE_ID"),
				"OPTIONS" => $siteList
			)
		);
	}

	public static function getSeverity($mode)
	{
		if($mode == Manager::MODE_MANAGER)
			return Manager::SEVERITY_STRICT;

		return parent::getSeverity($mode);
	}
} 