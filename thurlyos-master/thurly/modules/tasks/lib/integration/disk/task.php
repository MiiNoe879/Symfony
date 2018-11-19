<?
/**
 * Class implements all further interactions with "disk" module considering "task" entity
 *
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Thurly\Tasks\Integration\Disk;

use \Thurly\Tasks\Util\User;

final class Task extends \Thurly\Tasks\Integration\Disk
{
	public static function getSysUFCode()
	{
		return 'UF_TASK_WEBDAV_FILES';
	}
}