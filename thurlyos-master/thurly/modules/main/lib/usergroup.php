<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Main;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class UserGroupTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_user_group';
	}

	public static function getMap()
	{
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'USER' => array(
				'data_type' => 'User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'GROUP' => array(
				'data_type' => 'Group',
				'reference' => array('=this.GROUP_ID' => 'ref.ID')
			),
			'DATE_ACTIVE_FROM' => array(
				'data_type' => 'datetime',
			),
			'DATE_ACTIVE_TO' => array(
				'data_type' => 'datetime',
			),
		);
	}
}
