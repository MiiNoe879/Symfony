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

use Thurly\Tasks\Util\User;

final class Favorite extends \Thurly\Tasks\Dispatcher\RestrictedAction
{
	/**
	 * Add a task to users own favorite list
	 */
	public function add($taskId)
	{
		$result = array();

		if($taskId = $this->checkTaskId($taskId))
		{
			// user can add a task ONLY to his OWN favorite-list
			$task = new \CTaskItem($taskId, User::getId());
			$task->addToFavorite();
		}

		return $result;
	}

	/**
	 * Remove a task from users own favorite list
	 */
	public function delete($taskId)
	{
		$result = array();

		if($taskId = $this->checkTaskId($taskId))
		{
			// user can add a task ONLY to his OWN favorite-list
			$task = new \CTaskItem($taskId, User::getId());
			$task->deleteFromFavorite();
		}

		return $result;
	}
}