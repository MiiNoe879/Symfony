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

use Thurly\Tasks\Manager;
use Thurly\Tasks\Util\User;

final class ElapsedTime extends \Thurly\Tasks\Dispatcher\RestrictedAction
{
	/**
	 * Get all elapsed time items for a specified task
	 */
	public function getListByTask($taskId, array $order = array(), array $filter = array())
	{
		$result = array();

		if($taskId = $this->checkTaskId($taskId))
		{
			$result = Manager\Task\ElapsedTime::getListByParentEntity(User::getId(), $taskId);
		}

		return $result;
	}

	/**
	 * Add a new elapsed time record to a specified task
	 */
	public function add(array $data, array $parameters = array())
	{
		$mgrResult = Manager\Task\ElapsedTime::add(User::getId(), $data, array(
			'PUBLIC_MODE' => true,
			'ERRORS' => $this->errors,
			'RETURN_ENTITY' => $parameters['RETURN_ENTITY'], // just an exception for this type of entity
		));

		return array(
			'DATA' => $mgrResult['DATA'],
			'CAN' => $mgrResult['CAN'],
		);
	}

	/**
	 * Update an elapsed time record
	 */
	public function update($id, array $data, array $parameters = array())
	{
		$mgrResult = Manager\Task\ElapsedTime::update(User::getId(), $id, $data, array(
			'PUBLIC_MODE' => true,
			'ERRORS' => $this->errors,
			'RETURN_ENTITY' => $parameters['RETURN_ENTITY'],  // just an exception for this type of entity
		));

		return array(
			'DATA' => $mgrResult['DATA'],
			'CAN' => $mgrResult['CAN'],
		);
	}

	/**
	 * Delete an elapsed time record
	 */
	public function delete($id)
	{
		$result = array();

		if($id = $this->checkId($id))
		{
			// get task id
			$taskId = $this->getOwnerTaskId($id);
			if($taskId)
			{
				$task = \CTaskItem::getInstanceFromPool($taskId, User::getId()); // or directly, new \CTaskItem($taskId, User::getId());
				$item = new \CTaskElapsedItem($task, $id);
				$item->delete();
			}
		}

		return $result;
	}

	private function getOwnerTaskId($itemId)
	{
		$item = \CTaskElapsedTime::getList(array(), array('ID' => $itemId), array('skipJoinUsers' => false))->fetch();
		if(is_array($item) && !empty($item))
		{
			return $item['TASK_ID'];
		}
		else
		{
			$this->errors->add('ITEM_NOT_FOUND', 'Item not found');
		}

		return false;
	}
}