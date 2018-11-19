<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task;

use Thurly\Tasks\Internals\Task\ProjectDependenceTable;

final class ProjectDependence extends \Thurly\Tasks\Item\SubItem
{
	protected static function getParentConnectorField()
	{
		return 'TASK_ID';
	}

	public static function getDataSourceClass()
	{
		return ProjectDependenceTable::getClass();
	}

	protected static function getBindCondition($parentId)
	{
		return parent::getBindCondition($parentId) + array('=DIRECT' => '1');
	}
}