<?
/**
 * Class DependenceTable
 *
 * @package Thurly\Tasks
 **/

namespace Thurly\Tasks\Internals\Task\Template;

use Thurly\Main,
	Thurly\Main\Localization\Loc;
//Loc::loadMessages(__FILE__);

/**
 * Class DependenceTable
 * @package Thurly\Tasks\Internals\Task\Template
 *
 * Note: \Thurly\Tasks\Internals\DataBase\Tree is deprecated,
 * @see \Thurly\Tasks\Internals\Helper\Task\Template\Dependence instead.
 * Therefore, use this class ONLY as a datamanager class for table b_tasks_template_dep!
 */
class DependenceTable extends \Thurly\Tasks\Internals\DataBase\Tree
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_tasks_template_dep';
	}

	public static function getIDColumnName()
	{
		return 'TEMPLATE_ID';
	}

	public static function getPARENTIDColumnName()
	{
		return 'PARENT_TEMPLATE_ID';
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
		return array_merge(array(
			'TEMPLATE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'PARENT_TEMPLATE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),

			// reference
			'TEMPLATE' => array(
				'data_type' => '\Thurly\Tasks\Template',
				'reference' => array(
					'=this.TEMPLATE_ID' => 'ref.ID'
				),
				'join_type' => 'inner'
			),
			'PARENT_TEMPLATE' => array(
				'data_type' => '\Thurly\Tasks\Template',
				'reference' => array(
					'=this.PARENT_TEMPLATE_ID' => 'ref.ID'
				),
				'join_type' => 'inner'
			),
		), parent::getMap('\Thurly\Tasks\Internals\Task\Template\Dependence'));
	}
}