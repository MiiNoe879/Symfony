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

namespace Thurly\Tasks\Item\Field;

class Integer extends Scalar
{
	public function translateValueFromOutside($value, $key, $item) // from external level to business level
	{
		return $value === null ? null : intval($value);
	}
}