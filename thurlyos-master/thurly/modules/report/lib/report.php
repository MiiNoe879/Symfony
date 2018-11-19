<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage report
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Report;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class ReportTable extends Entity\DataManager
{
	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'OWNER_ID' => array(
				'data_type' => 'string'
			),
			'TITLE' => array(
				'data_type' => 'string'
			),
			'DESCRIPTION' => array(
				'data_type' => 'string'
			),
			'CREATED_DATE' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
			),
			'CREATED_BY' => array(
				'data_type' => 'integer'
			),
			'CREATED_BY_USER' => array(
				'data_type' => 'Thurly\Main\User',
				'reference' => array('=this.CREATED_BY' => 'ref.ID')
			),
			'SETTINGS' => array(
				'data_type' => 'string'
			),
			'MARK_DEFAULT' => array(
				'data_type' => 'integer'
			)
		);

		return $fieldsMap;
	}

}
