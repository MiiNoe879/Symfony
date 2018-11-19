<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Util\UserField;

class Task extends \Thurly\Tasks\Util\UserField
{
	public static function getEntityCode()
	{
		return 'TASKS_TASK';
	}

	/**
	 * Get system fields for this entity
	 */
	public static function getSysScheme()
	{
		return
			\Thurly\Tasks\Integration\Disk\UserField::getSysUFScheme() +
			\Thurly\Tasks\Integration\CRM\UserField::getSysUFScheme();
	}
}