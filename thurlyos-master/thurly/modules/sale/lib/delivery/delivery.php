<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale\Delivery;

use Thurly\Sale;
use Thurly\Main;
use Thurly\Main\Entity;

class DeliveryTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_delivery';
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'PRICE' => array(
				'data_type' => 'float'
			)
		);
	}
}
