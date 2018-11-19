<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Util\UserField\Type;

final class Date extends DateTime
{
	protected static function getFormatName()
	{
		return 'SHORT';
	}

	protected static function getFormatValue()
	{
		return 'YYYY-MM-DD';
	}
}