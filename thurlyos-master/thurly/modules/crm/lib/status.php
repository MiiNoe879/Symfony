<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Crm;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class StatusTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_crm_status';
	}

	public static function getMap()
	{
		return array(
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'STATUS_ID' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'SORT' => array(
				'data_type' => 'integer'
			)
		);
	}
}
