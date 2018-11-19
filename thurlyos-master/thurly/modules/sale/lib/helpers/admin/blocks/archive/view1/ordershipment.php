<?php

namespace Thurly\Sale\Helpers\Admin\Blocks\Archive\View1;

use Thurly\Sale\Helpers\Admin\Blocks\Archive\Template,
	Thurly\Main\Page\Asset,
	Thurly\Sale\Helpers\Admin\Blocks;

class OrderShipment extends Template
{
	protected $name = "delivery";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		$result = "";
		$index = 0;
		Asset::getInstance()->addJs("/thurly/js/sale/admin/order_shipment_basket.js");
		$shipmentCollection = $this->order->getShipmentCollection();
		foreach ($shipmentCollection as $shipment)
		{
			if (!$shipment->isSystem())
			{
				$result .= Blocks\OrderShipment::getView($shipment, $index++, 'archive');
			}
		}
		return $result;
	}
}