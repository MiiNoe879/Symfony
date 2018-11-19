<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage seo
 * @copyright 2001-2013 Thurly
 */
namespace Thurly\Seo\Retargeting\Internals;

use \Thurly\Main\Entity;
use \Thurly\Main\Localization\Loc;
use \Thurly\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class ServiceLogTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_seo_service_log';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime()
			),
			'GROUP_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'CODE' => array(
				'data_type' => 'string',
			),
			'MESSAGE' => array(
				'data_type' => 'string',
				'required' => true,
			)
		);

		return $fieldsMap;
	}
}
