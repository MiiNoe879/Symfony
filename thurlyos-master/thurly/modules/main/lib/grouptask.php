<?php
namespace Thurly\Main;

use Thurly\Main,
	Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class GroupTaskTable
 * 
 * Fields:
 * <ul>
 * <li> GROUP_ID int mandatory
 * <li> TASK_ID int mandatory
 * <li> EXTERNAL_ID string(50) optional
 * <li> GROUP reference to {@link \Thurly\Main\GroupTable}
 * <li> TASK reference to {@link \Thurly\Main\TaskTable}
 * </ul>
 *
 * @package Thurly\Main
 **/

class GroupTaskTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_group_task';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'TASK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExternalId'),
			),
			'GROUP' => array(
				'data_type' => 'Thurly\Main\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'TASK' => array(
				'data_type' => 'Thurly\Main\TaskTable',
				'reference' => array('=this.TASK_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
}