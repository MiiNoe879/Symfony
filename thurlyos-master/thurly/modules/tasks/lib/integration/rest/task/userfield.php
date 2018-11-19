<?
/**
 * Class implements all further interactions with "rest" module considering userfields for "task item" entity.
 * 
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 * 
 * @access private
 */

namespace Thurly\Tasks\Integration\Rest\Task;

use \Thurly\Tasks\Util\UserField\Restriction;

final class UserField extends \Thurly\Tasks\Integration\Rest\UserField
{
	public static function getTargetEntityId()
	{
		return 'TASKS_TASK';
	}

	public static function runRestMethod($executiveUserId, $methodName, array $args)
	{
		if(!Restriction::canManage(static::getTargetEntityId(), $executiveUserId))
		{
			// todo: raising an exception is bad, but still we got no error collection to return here
			throw new \Thurly\Main\SystemException('Action not allowed');
		}

		return parent::runRestMethod($executiveUserId, $methodName, $args);
	}
}