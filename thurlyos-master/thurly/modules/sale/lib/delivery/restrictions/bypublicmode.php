<?php
namespace Thurly\Sale\Delivery\Restrictions;

use Thurly\Main\Application;
use Thurly\Sale\Delivery\Restrictions;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Internals\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class ByPublicMode
 * @package Thurly\Sale\Delivery\Restrictions
 */

class ByPublicMode extends Restrictions\Base
{
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_PUBLIC_MODE_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_PUBLIC_MODE_DESCRIPT");
	}

	public static function check($dummy, array $restrictionParams, $deliveryId = 0)
	{
		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();

		if (empty($restrictionParams) || $request->isAdminSection())
			return true;

		return $restrictionParams["PUBLIC_SHOW"] == 'Y';
	}

	protected static function extractParams(Entity $shipment)
	{
		return null;
	}

	public static function getParamsStructure($entityId = 0)
	{
		return array(
			"PUBLIC_SHOW" => array(
				'TYPE' => 'Y/N',
				'VALUE' => 'Y',
				'LABEL' => Loc::getMessage("SALE_DLVR_RSTR_BY_PUBLIC_MODE_SHOW")
			)
		);
	}
}