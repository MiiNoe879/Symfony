<?
/**
 * Class ViewedTable
 *
 * @package Thurly\Tasks
 **/

namespace Thurly\Tasks\Internals\Task;

use Thurly\Main,
	Thurly\Main\Localization\Loc;
//Loc::loadMessages(__FILE__);

class ViewedTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_tasks_viewed';
	}

	/**
	 * @return static
	 */
	public static function getClass()
	{
		return get_called_class();
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'TASK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'VIEWED_DATE' => array(
				'data_type' => 'datetime',
				'required' => true,
			),

			// references
			'USER' => array(
				'data_type' => 'Thurly\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'TASK' => array(
				'data_type' => 'Thurly\Tasks\TaskTable',
				'reference' => array('=this.TASK_ID' => 'ref.ID')
			),
			'MEMBERS' => array(
				'data_type' => 'Thurly\Tasks\MemberTable',
				'reference' => array('=this.TASK_ID' => 'ref.TASK_ID', '=this.USER_ID'=>'ref.USER_ID')
			),
		);
	}
}