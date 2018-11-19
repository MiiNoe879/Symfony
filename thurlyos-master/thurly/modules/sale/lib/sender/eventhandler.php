<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Sale\Sender;


class EventHandler
{
	/**
	 * @param $data
	 * @return mixed
	 */
	public static function onConnectorListBuyer($data)
	{
		$data['CONNECTOR'] = '\Thurly\Sale\Sender\ConnectorBuyer';

		return $data;
	}

	public static function onTriggerList($data)
	{
		$data['TRIGGER'] = array(
			'\Thurly\Sale\Sender\TriggerOrderNew',
			'\Thurly\Sale\Sender\TriggerOrderCancel',
			'\Thurly\Sale\Sender\TriggerOrderPaid',
			'\Thurly\Sale\Sender\TriggerBasketForgotten',
			'\Thurly\Sale\Sender\TriggerDontBuy',
			'\Thurly\Sale\Sender\TriggerOrderStatusChange',
		);

		return $data;
	}

	public static function onConnectorOrder($data)
	{
		$data['CONNECTOR'] = '\Thurly\Sale\Sender\ConnectorOrder';

		return $data;
	}

	public static function onPresetMailingList()
	{
		$resultList = array();
		$resultList[] = \Thurly\Sale\Sender\PresetMailing::getForgottenCart(1);
		$resultList[] = \Thurly\Sale\Sender\PresetMailing::getCanceledOrder();
		$resultList[] = \Thurly\Sale\Sender\PresetMailing::getPaidOrder();
		$resultList[] = \Thurly\Sale\Sender\PresetMailing::getDontBuy(90);
		$resultList[] = \Thurly\Sale\Sender\PresetMailing::getDontAuth(111);
		$resultList[] = \Thurly\Sale\Sender\PresetMailing::getDontBuy(180);
		$resultList[] = \Thurly\Sale\Sender\PresetMailing::getDontBuy(360);

		return $resultList;
	}

	public static function onPresetTemplateList()
	{
		$resultList = array();

		return $resultList;
	}
}