<?php
namespace Thurly\Disk\Internals;

use Thurly\Main\Application;
use Thurly\Main\Type\DateTime;

/**
 * Class ObjectTtlTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> OBJECT_ID int mandatory
 * <li> CREATE_TIME datetime mandatory
 * <li> DEATH_TIME datetime mandatory
 * </ul>
 *
 * @package Thurly\Disk\Internals
 **/
final class ObjectTtlTable extends DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_disk_object_ttl';
	}

	/**
	 * Returns entity map definition
	 *
	 * @return array
	 */
	public static function getMap()
	{
		$connection = Application::getConnection();
		$now = $connection->getSqlHelper()->getCurrentDateTimeFunction();

		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'OBJECT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'OBJECT' => array(
				'data_type' => 'Thurly\Disk\Internals\ObjectTable',
				'reference' => array(
					'=this.OBJECT_ID' => 'ref.ID'
				),
				'join_type' => 'INNER',
			),
			'CREATE_TIME' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new DateTime(),
			),
			'DEATH_TIME' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new DateTime(),
			),
			'IS_EXPIRED' => array(
				'data_type' => 'boolean',
				'expression' => array(
					"CASE WHEN (%s IS NOT NULL AND %s > {$now} OR %s IS NULL) THEN 0 ELSE 1 END",
					'DEATH_TIME', 'DEATH_TIME', 'DEATH_TIME'
				),
				'values' => array(0, 1),
			),
		);
	}
}
