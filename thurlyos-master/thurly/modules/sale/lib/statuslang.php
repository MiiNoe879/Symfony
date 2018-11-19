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

class StatusLangTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_status_lang';
	}

	public static function getMap()
	{
		return array(
			'STATUS_ID' => array(
				'data_type' => 'string',
				'primary' => true
			),
			// field for filter operation on entity
			'ID' => array(
				'data_type' => 'string',
				'expression' => array(
					'%s', 'STATUS_ID'
				)
			),
			'LID' => array(
				'data_type' => 'string'
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'DESCRIPTION' => array(
				'data_type' => 'string'
			)
		);
	}
}
