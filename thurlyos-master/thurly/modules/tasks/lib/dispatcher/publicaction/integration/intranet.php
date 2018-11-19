<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 *
 * Each method you put here you`ll be able to call as ENTITY_NAME.METHOD_NAME via AJAX and\or REST, so be careful.
 */

namespace Thurly\Tasks\Dispatcher\PublicAction\Integration;

use Thurly\Tasks;
use Thurly\Tasks\Util\Result;
use \Thurly\Tasks\Dispatcher\PublicAction;

final class Intranet extends PublicAction
{
	public static function absence(array $userIds)
	{
		$list = \Thurly\Tasks\Util\User::isAbsence($userIds);

		return $list;
	}
}