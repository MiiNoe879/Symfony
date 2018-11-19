<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Tasks\Item\Task;

use Thurly\Tasks\Internals\Task\TagTable;
use Thurly\Tasks\Util\User;
use Thurly\Tasks\Item\Task\Collection;

final class Tag extends \Thurly\Tasks\Item\SubItem
{
	protected static function getParentConnectorField()
	{
		return 'TASK_ID';
	}

	public static function getDataSourceClass()
	{
		return TagTable::getClass();
	}

	public static function getCollectionClass()
	{
		return Collection\Tag::getClass();
	}

	public function prepareData($result)
	{
		if(parent::prepareData($result))
		{
			$state = $this->getTransitionState();
			if($state->isModeCreate()) // it is add()
			{
				// set default user
				if(!$this->isFieldModified('USER_ID'))
				{
					$userId = User::getId();
					$parent = $this->getParent();
					if($parent)
					{
						$userId = $parent->getUserId();
					}

					$this['USER_ID'] = $userId;
				}
			}
		}

		return $result->isSuccess();
	}
}