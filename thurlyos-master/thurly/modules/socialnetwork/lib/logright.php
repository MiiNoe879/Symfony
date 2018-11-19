<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage socialnetwork
 * @copyright 2001-2017 Thurly
 */
namespace Thurly\Socialnetwork;

use Thurly\Main\Entity;

class LogRightTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_right';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LOG_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'LOG' => array(
				'data_type' => '\Thurly\Socialnetwork\Log',
				'reference' => array('=this.LOG_ID' => 'ref.ID')
			),
			'GROUP_CODE' => array(
				'data_type' => 'string',
			)
		);

		return $fieldsMap;
	}
}
