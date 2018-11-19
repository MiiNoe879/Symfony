<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Main\UserConsent\Internals;

use Thurly\Main\Entity;
use Thurly\Main\Type\DateTime;
use Thurly\Main\Localization\Loc;
use Thurly\Crm\WebForm\Helper;

Loc::loadMessages(__FILE__);

class ConsentTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_consent_user_consent';
	}

	/**
	 * Get map.
	 *
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
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new DateTime(),
			),
			'AGREEMENT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'IP' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'URL' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'ORIGIN_ID' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'ORIGINATOR_ID' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'USER' => array(
				'data_type' => 'Thurly\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
		);
	}
}
