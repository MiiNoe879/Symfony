<?
namespace Thurly\Tasks\Dispatcher;

use Thurly\Tasks\Item;
use Thurly\Tasks\Util\Collection;
use Thurly\Tasks\Processor\Task\Result\Impact;

final class ExecutionResult extends \Thurly\Tasks\Util\Result
{
	public function isSuccess()
	{
		return $this->getErrors()->checkNoFatals() && !$this->getErrors()->checkHasErrorOfType(\Thurly\Tasks\Dispatcher::ERROR_TYPE_PARSE);
	}
}