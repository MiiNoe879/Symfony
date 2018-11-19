<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task;

use Thurly\Tasks\UI;
use Thurly\Tasks\Util\Type;
use Thurly\Tasks\Util\User;

final class Log extends \Thurly\Tasks\Item\SubItem
{
	protected static function getParentConnectorField()
	{
		return 'TASK_ID';
	}

	public static function getDataSourceClass()
	{
		return \Thurly\Tasks\Internals\Task\LogTable::getClass();
	}
}