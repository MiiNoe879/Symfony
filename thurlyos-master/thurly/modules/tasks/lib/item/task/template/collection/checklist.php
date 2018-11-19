<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 * @internal
 */

namespace Thurly\Tasks\Item\Task\Template\Collection;

class CheckList extends \Thurly\Tasks\Item\Task\Collection\CheckList
{
	protected static function getItemClass()
	{
		return '\\Thurly\\Tasks\\Item\\Task\\Template\\CheckList';
	}

	protected static function getSortColumnName()
	{
		return 'SORT';
	}

	protected static function getCheckedColumnName()
	{
		return 'CHECKED';
	}
}
