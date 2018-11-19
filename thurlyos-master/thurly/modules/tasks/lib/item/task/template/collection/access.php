<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 * @internal
 */

namespace Thurly\Tasks\Item\Task\Template\Collection;

use Thurly\Main\NotImplementedException;
use Thurly\Tasks\Item;
use Thurly\Tasks\Util\Result;

class Access extends \Thurly\Tasks\Item\Collection
{
	/**
	 * @return Item
	 */
	protected static function getItemClass()
	{
		return \Thurly\Tasks\Item\Task\Template\Access::getClass();
	}

	public function grantAccessLevel($groupCode, $level)
	{
		$groupCode = trim((string) $groupCode);
		$level = trim((string) $level);

		if(!is_numeric($level))
		{
			$level = \Thurly\Tasks\Util\User::getAccessLevel('TASK_TEMPLATE', $level);
			if($level)
			{
				$level = $level['ID'];
			}
		}

		$level = intval($level);
		if($level)
		{
			$className = static::getItemClass();

			// todo: here we do not have reference to a parent object. we must have it, because we need to forward userId
			$member = new $className(array(
				'TASK_ID' => $level,
				'GROUP_CODE' => $groupCode,
			));

			// todo: implement ->push() here, instead of this
			$this->push($member);
		}
	}

	public function revokeAll()
	{
		$this->values = array();
		$this->onChange();
	}

	public function revokeAccessLevel($groupCode, $level)
	{
		throw new NotImplementedException();
	}
}