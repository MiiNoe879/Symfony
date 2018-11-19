<?
/**
 * @access private
 */
namespace Thurly\Tasks\Integration\Rest\Item;

final class UserField extends \Thurly\Tasks\Integration\Rest\UserField
{
	public static function getTargetEntityId()
	{
		return 'TASKS_TASK';
	}
}