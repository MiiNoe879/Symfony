<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class PaySystemTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_pay_system';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LID' => array(
				'data_type' => 'string'
			),
			'CURRENCY' => array(
				'data_type' => 'string'
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'SORT' => array(
				'data_type' => 'integer'
			),
			'DESCRIPTION' => array(
				'data_type' => 'string'
			)
		);
	}
}
