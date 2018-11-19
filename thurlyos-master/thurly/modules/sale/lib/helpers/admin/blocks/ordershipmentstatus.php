<?php

namespace Thurly\Sale\Helpers\Admin\Blocks;

use Thurly\Main\Application;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\DeliveryStatus;
use Thurly\Sale\Internals\StatusTable;
use Thurly\Sale\Order;

Loc::loadMessages(__FILE__);

class OrderShipmentStatus
{
	public static function getEdit($shipment, $index = 0)
	{
		$data = self::prepareData($shipment);

		return self::getEditTemplate($data, ++$index);
	}

	public static function getEditTemplate($data, $index)
	{
		return '
			<div class="adm-bus-pay">
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
					<tbody>
						<tr>
							<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_SHIPMENT_STATUS').':</td>
							<td class="adm-detail-content-cell-r">'.
							\Thurly\Sale\Helpers\Admin\OrderEdit::makeSelectHtml(
								"SHIPMENT[".$index."][STATUS_ID]",
								self::getShipmentStatusList($data['STATUS_ID']),
								$data['STATUS_ID'],
								false,
								array(
									"class" => "adm-bus-select",
									"id" => "SHIPMENT_STATUS_ID"
								)
							)
							.'</td>
						</tr>
					</tbody>
				</table>
			</div>';
	}

	/**
	 * @param $status
	 *
	 * @return array
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function getShipmentStatusList($status)
	{
		global $USER;

		$shipmentStatuses = array();

		$allStatusList = DeliveryStatus::getAllStatusesNames();
		if (array_key_exists($status, $allStatusList))
		{
			$shipmentStatuses[$status] = $allStatusList[$status] . " [" . $status . "]";
		}

		$statusList = DeliveryStatus::getAllowedUserStatuses($USER->GetID(), $status);
		if (!empty($statusList) && is_array($statusList))
		{
			foreach ($statusList as $code => $title)
			{
				$shipmentStatuses[$code] = $title . " [" . $code . "]";
			}
		}
		return $shipmentStatuses;
	}

	public static function getView(Order $order)
	{
		return __METHOD__;
	}

	/**
	 * @param \Thurly\Sale\ShipmentItem $shipment
	 * @return mixed
	 */
	protected static function prepareData($shipment)
	{
		$data['STATUS_ID'] = $shipment->getField('STATUS_ID');
		return $data;
	}
}