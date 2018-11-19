<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access privatees
 */

namespace Thurly\Tasks\Manager\Task;

final class Originator extends \Thurly\Tasks\Manager\Task\Member
{
	public static function getLegacyFieldName()
	{
		return 'CREATED_BY';
	}
}