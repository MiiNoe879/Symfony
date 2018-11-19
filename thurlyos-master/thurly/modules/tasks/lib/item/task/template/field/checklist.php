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

namespace Thurly\Tasks\Item\Task\Template\Field;

class CheckList extends \Thurly\Tasks\Item\Task\Field\CheckList
{
	protected static function getItemClass()
	{
		return \Thurly\Tasks\Item\Task\Template\CheckList::getClass();
	}
}