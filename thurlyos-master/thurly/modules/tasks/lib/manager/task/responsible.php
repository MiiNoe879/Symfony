<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access privatees
 */

namespace Thurly\Tasks\Manager\Task;

final class Responsible extends \Thurly\Tasks\Manager\Task\Member
{
	public static function getLegacyFieldName()
	{
		return 'RESPONSIBLE_ID';
	}

	public static function getIsMultiple()
	{
		return true;
	}

	public static function adaptSet(array &$data)
	{
		$to = static::getLegacyFieldName();

		// for responsible, field RESPONSIBLE_ID has higher priority than SE_RESPONSIBLE

		if(array_key_exists($to, $data))
		{
			return;
		}

		parent::adaptSet($data);
	}
}