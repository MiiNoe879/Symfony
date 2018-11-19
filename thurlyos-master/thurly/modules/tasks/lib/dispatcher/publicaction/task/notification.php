<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 * 
 * @access private
 *
 * Each method you put here you`ll be able to call as ENTITY_NAME.METHOD_NAME via AJAX and\or REST, so be careful.
 */

namespace Thurly\Tasks\Dispatcher\PublicAction\Task;

//use \Thurly\Tasks\Util\Error\Collection;

final class Notification extends \Thurly\Tasks\Dispatcher\RestrictedAction
{
	/**
	 * Deliver all notification being throttled
	 */
	public function throttleRelease()
	{
		\CTaskNotifications::throttleRelease();
	}
}