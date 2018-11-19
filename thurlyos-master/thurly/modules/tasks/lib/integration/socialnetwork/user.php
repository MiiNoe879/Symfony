<?
/**
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Thurly\Tasks\Integration\SocialNetwork;

final class User extends \Thurly\Tasks\Integration\SocialNetwork
{
	public static function isAdmin($userId = 0)
	{
		if(static::includeModule())
		{
			return \CSocNetUser::isCurrentUserModuleAdmin();
		}

		return false;
	}
}