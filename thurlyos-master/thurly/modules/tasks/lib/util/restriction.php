<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 */

namespace Thurly\Tasks\Util;

use Thurly\Main\Localization\Loc;
use Thurly\Tasks\Internals\Task\ProjectDependenceTable;
use Thurly\Tasks\Integration\ThurlyOS;

Loc::loadMessages(__FILE__);

final class Restriction
{
	public static function checkCanCreateDependence($userId = 0)
	{
		// you can not skip this check for admin, because on thurlyos admin is just one of regular users

		if(ThurlyOS\Task::checkFeatureEnabled('gant'))
		{
			return true; // yes: you are using box, or you are in trial mode
		}

		// generally no, but you can make 5 dependences
		return ProjectDependenceTable::getLinkCountForUser($userId) < 5;
	}

	public static function canManageTask($userId = 0)
	{
		return ThurlyOS\Task::checkToolAvailable('tasks');
	}
}