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

class FormViewTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_crm_webform_view';
	}

	public static function getMap()
	{
		return array(
			'FORM_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
			)
		);
	}
}
