<?php
namespace Thurly\Sale\Cashbox\Internals;

use Thurly\Main\Config\Option;
use	Thurly\Main\Entity\DataManager;
use	Thurly\Main\Type\DateTime;

class CashboxConnectTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_cashbox_connect';
	}

	public static function getMap()
	{
		return array(
			'HASH' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y'
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime
			)
		);
	}
}
