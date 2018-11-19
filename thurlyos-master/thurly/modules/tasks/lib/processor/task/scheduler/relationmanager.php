<?
/**
 * This class could be changed (removed, renamed, relocated) in any time, so do not use it in public code
 *
 * @internal
 * @access private
 */

namespace Thurly\Tasks\Processor\Task\Scheduler;

use Thurly\Main\NotImplementedException;
use Thurly\Tasks\Processor\Task\Result;
use Thurly\Tasks\Processor\Task\Scheduler;
use Thurly\Tasks\Processor\Task\Scheduler\Result\Impact;

abstract class RelationManager
{
	protected $scheduler = null;

	public function getUserId()
	{
		return $this->getScheduler()->getUserId();
	}

	public function setScheduler($instance)
	{
		$this->scheduler = $instance;
	}

	/**
	 * @return Scheduler
	 */
	public function getScheduler()
	{
		return $this->scheduler;
	}

	/**
	 * @throws NotImplementedException
	 * @return string
	 */
	public static function getCode()
	{
		throw new NotImplementedException();
	}

	/**
	 * @param Impact $rootImpact
	 * @param Result $result
	 * @return array
	 */
	public function processTask($rootImpact, $result)
	{
		return array();
	}

	public static function isTaskBelong($id)
	{
		return false;
	}

	public function isDebugEnabled()
	{
		return $this->getScheduler()->isDebugEnabled();
	}
}