<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2015 Thurly
 */

namespace Thurly\Tasks\Util\UserField;

use Thurly\Main\Config\Option;
use Thurly\Main\UserFieldTable;
use Thurly\Tasks\Integration\ThurlyOS;
use Thurly\Tasks\Util\UserField;

final class Restriction
{
	public static function canUse($entityCode, $userId = 0)
	{
		if(static::hadUserFieldsBefore($entityCode)) // you can read\write field values, but editing scheme is not guaranteed
		{
			return true;
		}

		// otherwise, thurlyos will tell us
		return ThurlyOS\Task::checkFeatureEnabled('task_user_field');
	}

	public static function canManage($entityCode, $userId = 0)
	{
		// for any entity, ask thurlyos
		return ThurlyOS\Task::checkFeatureEnabled('task_user_field');
	}

	public static function canCreateMandatory($entityCode, $userId = 0)
	{
		return static::canManage($entityCode, $userId) && (ThurlyOS::isLicensePaid() || ThurlyOS::isLicenseShareware());
	}

	private static function hadUserFieldsBefore($entityCode)
	{
		$optName = 'have_uf_'.ToLower($entityCode);

		$flag = Option::get('tasks', $optName);

		if($flag === '') // not checked before, check then
		{
			$filter = array(
				'=ENTITY_ID' => $entityCode,
			);

			$className = UserField::getControllerClassByEntityCode($entityCode);
			if($className)
			{
				$filter['!@FIELD_NAME'] = array_keys($className::getSysScheme());
			}

			$item = UserFieldTable::getList(array(
				'filter' => $filter,
				'limit' => 1,
				'select' => array(
					'ID'
				)
			))->fetch();

			Option::set('tasks', $optName, intval($item['ID']) ? '1' : '0');

			return intval($item['ID']) > 0;
		}
		else
		{
			return $flag == '1';
		}
	}
}