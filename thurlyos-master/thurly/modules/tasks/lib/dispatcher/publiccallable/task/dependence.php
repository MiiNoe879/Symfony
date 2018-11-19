<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 * 
 * @access private
 * 
 * Each public method you put here you`ll be able to call as ENTITY_NAME.METHOD_NAME, so be careful.
 */

namespace Thurly\Tasks\Dispatcher\PublicCallable\Task;

use \Thurly\Tasks\DependenceTable;
use \Thurly\Tasks\DB\Tree;

final class Dependence extends \Thurly\Tasks\Dispatcher\PublicCallable
{
	/**
	 * Add a new dependence between two tasks
	 */
	public function add($taskIdFrom, $taskIdTo, $linkType)
	{
		global $USER;

		try
		{
			$task = new \CTaskItem($taskIdTo, $USER->GetId());
			$task->addDependOn($taskIdFrom, $linkType);
		}
		catch(Tree\Exception $e)
		{
			$this->errors->add('ILLEGAL_NEW_LINK', \Thurly\Tasks\Dispatcher::proxyExceptionMessage($e));
		}

		return array();
	}

	/**
	 * Delete an existing dependence between two tasks
	 */
	public function delete($taskIdFrom, $taskIdTo)
	{
		global $USER;

		try
		{
			$task = new \CTaskItem($taskIdTo, $USER->GetId());
			$task->deleteDependOn($taskIdFrom, $linkType);
		}
		catch(Tree\Exception $e)
		{
			$this->errors->add('ILLEGAL_LINK', \Thurly\Tasks\Dispatcher::proxyExceptionMessage($e));
		}

		return array();
	}
}