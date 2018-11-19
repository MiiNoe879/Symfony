<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2013 Thurly
 */
namespace Thurly\Crm;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ActivityBindingTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_crm_act_bind';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'ACTIVITY_ID' => array(
				'data_type' => 'integer'
			),
			'OWNER_ID' => array(
				'data_type' => 'integer'
			),
			'OWNER_TYPE_ID' => array(
				'data_type' => 'integer'
			)
		);
	}
}
