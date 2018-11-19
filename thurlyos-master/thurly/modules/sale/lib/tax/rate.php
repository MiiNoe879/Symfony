<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2014 Thurly
 */
namespace Thurly\Sale\Tax;

use Thurly\Sale;
use Thurly\Main;
use Thurly\Main\Entity;

class RateTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_tax_rate';
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			)
		);
	}
}
