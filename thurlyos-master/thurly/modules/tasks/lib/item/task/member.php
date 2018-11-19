<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task;

use Thurly\Tasks\Internals\Task\MemberTable;

final class Member extends \Thurly\Tasks\Item\SubItem
{
	protected static function getParentConnectorField()
	{
		return 'TASK_ID';
	}

	public static function getDataSourceClass()
	{
		return MemberTable::getClass();
	}

	public static function getCollectionClass()
	{
		return \Thurly\Tasks\Item\Task\Collection\Member::getClass();
	}
}