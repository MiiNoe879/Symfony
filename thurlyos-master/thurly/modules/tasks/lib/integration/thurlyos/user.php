<?
/**
 * Class implements all further interactions with "thurlyos" module
 *
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Thurly\Tasks\Integration\ThurlyOS;

final class User extends \Thurly\Tasks\Integration\ThurlyOS
{
	public static function isAdmin($userId = 0)
	{
		if(!static::includeModule())
		{
			return false;
		}

		if(!$userId)
		{
			$userId = \Thurly\Tasks\Util\User::getId();
		}

		if($userId)
		{
			static $cache = array();

			if(!isset($cache[$userId]))
			{
				$cache[$userId] = (boolean) \CThurlyOS::isPortalAdmin($userId);
			}

			return $cache[$userId];
		}

		return false;
	}
}