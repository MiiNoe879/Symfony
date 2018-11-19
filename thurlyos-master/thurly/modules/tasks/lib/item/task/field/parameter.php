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

namespace Thurly\Tasks\Item\Task\Field;

use Thurly\Tasks\Item\Task;

class Parameter extends \Thurly\Tasks\Item\Field\Collection\Item
{
	protected static function getItemClass()
	{
		return Task\Parameter::getClass();
	}
}