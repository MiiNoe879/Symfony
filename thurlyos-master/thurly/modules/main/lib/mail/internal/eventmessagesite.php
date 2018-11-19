<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main\Mail\Internal;

use Thurly\Main\Entity;

class EventMessageSiteTable extends Entity\DataManager
{

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_event_message_site';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'EVENT_MESSAGE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),

			'SITE_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
		);
	}

}
