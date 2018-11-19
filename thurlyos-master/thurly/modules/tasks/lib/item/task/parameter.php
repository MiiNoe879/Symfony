<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task;

use Thurly\Tasks\Internals\Task\ParameterTable;
use Thurly\Tasks\Item\Task;

final class Parameter extends \Thurly\Tasks\Item\SubItem
{
	protected static function getParentConnectorField()
	{
		return 'TASK_ID';
	}

	public static function getDataSourceClass()
	{
		return ParameterTable::getClass();
	}

	public static function getParentClass()
	{
		return Task::getClass();
	}
}