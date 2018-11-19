<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Crm\WebForm\Internals;

use Thurly\Main\Entity;
use Thurly\Main\Type\DateTime;
use Thurly\Main\Localization\Loc;
use Thurly\Crm\WebForm\Helper;

Loc::loadMessages(__FILE__);

class QueueTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_crm_webform_queue';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'FORM_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'WORK_TIME' => array(
				'data_type' => 'boolean',
				'required' => false,
				'default_value' => 'N',
				'values' => array('N','Y')
			),
		);
	}
}
