<?php
namespace Thurly\Report\Internals;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SharingTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_report_sharing';
	}

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
			'REPORT_ID' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'LINK_REPORT' => array(
				'data_type' => 'Thurly\Report\ReportTable',
				'reference' => array(
					'=this.REPORT_ID' => 'ref.ID'
				),
			),
			'ENTITY' => array(
				'data_type' => 'string',
				'required' => true
			),
			'RIGHTS' => array(
				'data_type' => 'string',
				'required' => true
			)
		);

		return $fieldsMap;
	}

}
