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

namespace Thurly\Tasks\Item\Field\Collection;

use Thurly\Tasks\Util\Type;
use Thurly\Tasks\Util;

class Integer extends \Thurly\Tasks\Item\Field\Collection\Scalar
{
	protected function clearArray($value)
	{
		// 0 is also allowed!
		return array_values(array_unique(array_map('intval', $value)));
	}
}