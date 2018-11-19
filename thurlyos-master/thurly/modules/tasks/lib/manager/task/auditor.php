<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Manager\Task;

final class Auditor extends \Thurly\Tasks\Manager\Task\Member
{
	public static function getLegacyFieldName()
	{
		return 'AUDITORS';
	}

	public static function getIsMultiple()
	{
		return true;
	}
}