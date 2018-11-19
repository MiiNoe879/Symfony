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

use Thurly\Tasks\Util\Type\DateTime;

class Date extends Scalar
{
	public function translateValueFromOutside($value, $key, $item)
	{
		return DateTime::createFromObjectOrString($value);
	}
}