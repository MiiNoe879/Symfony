<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main\Mail\Internal;

use Thurly\Main\Entity;

class EventTypeTable extends Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_event_type';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'EVENT_NAME' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
			),
			'SORT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 100,
			),
		);
	}

}
