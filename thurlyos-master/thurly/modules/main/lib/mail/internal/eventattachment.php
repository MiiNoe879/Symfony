<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main\Mail\Internal;

use Thurly\Main\Entity;

class EventAttachmentTable extends Entity\DataManager
{

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_event_attachment';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'EVENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
			),
			'IS_FILE_COPIED' => array(
				'data_type' => 'boolean',
				'required' => true,
				'values' => array('N', 'Y'),
				'default_value' => 'Y'
			),
		);
	}

}
