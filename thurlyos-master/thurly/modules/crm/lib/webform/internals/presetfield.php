<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Crm\WebForm\Internals;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;
use Thurly\Crm\WebForm\Helper;

Loc::loadMessages(__FILE__);

class PresetFieldTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_crm_webform_field_preset';
	}

	public static function getMap()
	{
		return array(
			'FORM_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'ENTITY_NAME' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'FIELD_NAME' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'VALUE' => array(
				'data_type' => 'string',
				'required' => true,
			),
		);
	}
}
