<?
/**
 * Class TagTable
 *
 * @package Thurly\Tasks
 **/

namespace Thurly\Tasks\Internals\Task;

use Thurly\Main,
	Thurly\Main\Localization\Loc;
//Loc::loadMessages(__FILE__);

class TagTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_tasks_tag';
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
			'NAME' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateName'),
			),

			// references
			'TASK' => array(
				'data_type' => 'Task',
				'reference' => array('=this.TASK_ID' => 'ref.ID')
			),
			'USER' => array(
				'data_type' => 'Thurly\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}