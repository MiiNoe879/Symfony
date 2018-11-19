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

namespace Thurly\Tasks\Dispatcher\PublicAction\Ui;

use Thurly\Tasks\Util\Result;
use Thurly\Tasks\Internals;

final class Counters extends \Thurly\Tasks\Dispatcher\PublicAction
{
	public function get($userId, $groupId = 0, $type = 'view_all')
	{
		$result = new Result();

		$counterInstance = Internals\Counter::getInstance($userId, $groupId);

		$result->setData($counterInstance->getCounters($type));

		return $result;
	}
}