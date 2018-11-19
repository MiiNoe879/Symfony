<?php
namespace Thurly\Sale\TradingPlatform;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class OrderTable
 * Links external order id with internal order id.
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ORDER_ID int mandatory
 * <li> TRADING_PLATFORM_ID int mandatory
 * <li> EXTERNAL_ORDER_ID string(100) mandatory
 * <li> EXTERNAL_ORDER \Thurly\Sale\TradingPlatform optional
 * </ul>
 *
 * @package Thurly\Sale\TradingPlatform
 **/

class OrderTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_tp_order';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('TRADING_PLATFORM_ORDER_ENTITY_ID_FIELD'),
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TRADING_PLATFORM_ORDER_ENTITY_ORDER_ID_FIELD'),
			),
			'ORDER' => array(
				'data_type' => '\Thurly\Sale\Internals\OrderTable',
				'reference' => array('=this.ORDER_ID' => 'ref.ID'),
				'title' => Loc::getMessage('TRADING_PLATFORM_ORDER_ENTITY_ORDER_FIELD')
			),
			'EXTERNAL_ORDER_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateExternalOrderId'),
				'title' => Loc::getMessage('TRADING_PLATFORM_ORDER_ENTITY_EXTERNAL_ORDER_ID_FIELD'),
			),
			'PARAMS' => array(
				'data_type' => 'string',
				'required' => false,
				'serialized' => true,
				'title' => Loc::getMessage('TRADING_PLATFORM_ORDER_ENTITY_EXTERNAL_ORDER_LINES_FIELD'),
			),
			'TRADING_PLATFORM_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TRADING_PLATFORM_ORDER_ENTITY_TRADING_PLATFORM_ID_FIELD'),
			),
			'TRADING_PLATFORM' => array(
				'data_type' => '\Thurly\Sale\TradingPlatform',
				'reference' => array('=this.TRADING_PLATFORM_ID' => 'ref.ID'),
				'title' => Loc::getMessage('TRADING_PLATFORM_ORDER_ENTITY_TRADING_PLATFORM_FIELD'),
			));
	}
	public static function validateExternalOrderId()
	{
		return array(
			new Entity\Validator\Length(null, 100),
		);
	}

	public static function deleteByOrderId($orderId)
	{
		if(intval($orderId) <= 0)
			return false;

		$con = \Thurly\Main\Application::getConnection();
		$sqlHelper = $con->getSqlHelper();
		$id = $sqlHelper->forSql($orderId);
		$con->queryExecute("DELETE FROM b_sale_tp_order WHERE ORDER_ID=".$id);
		return true;
	}
}