<?
/**
 * Class implements all further interactions with "extranet" module
 *
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Thurly\Tasks\Integration\Intranet;

final class User extends \Thurly\Tasks\Integration\Intranet
{
	/**
	 * Returns userfield code by which users are connected with departments
	 * @return string
	 */
	public static function getDepartmentUFCode()
	{
		return 'UF_DEPARTMENT';
	}

	/**
	 * Checks if a given user is a director (has subordinate users or departments)
	 *
	 * @param int $userId
	 * @return bool
	 */
	public static function isDirector($userId = 0)
	{
		if(!$userId)
		{
			$userId = \Thurly\Tasks\Util\User::getId();
		}

		if(!$userId)
		{
			return false;
		}

		$subs = Department::getSubordinateIds($userId);

		return !empty($subs);
	}

	public static function getSubordinateSubDepartments($userId = 0, $allowedDepartments = null)
	{
		return static::getSubordinate($userId, $allowedDepartments, true);
	}

	public static function getSubordinate($userId = 0, $allowedDepartments = null, $includeSubDepartments = false)
	{
		if(!static::includeModule())
		{
			return array();
		}

		$arDepartmentHeads = array();

		$arQueueDepartmentsEmployees = array();	// IDs of departments where we need employees

		// Departments where given user is head
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		$arManagedDepartments = \CIntranetUtils::getSubordinateDepartments($userId ? $userId : null, $includeSubDepartments);

		if (is_array($allowedDepartments))
		{
			$arManagedDepartments = array_intersect(
				$arManagedDepartments,
				$allowedDepartments
			);
		}

		if (is_array($arManagedDepartments))
		{
			foreach ($arManagedDepartments as $departmentId)
			{
				$arQueueDepartmentsEmployees[] = $departmentId;

				$result = static::searchImmediateEmployeesInSubDepartments($departmentId);

				$arDepartmentHeads = array_merge(
					$arDepartmentHeads,
					$result['arDepartmentHeads']
				);

				$arQueueDepartmentsEmployees = array_merge(
					$arQueueDepartmentsEmployees,
					$result['arQueueDepartmentsEmployees']
				);
			}
		}

		$arEmployees = $arDepartmentHeads;

		if ( ! empty($arQueueDepartmentsEmployees) )
		{
			$arEmployees = array_merge(
				$arEmployees,
				static::getDepartmentsUsersIds($arQueueDepartmentsEmployees)
			);
		}

		if ( ! empty($arEmployees) )
		{
			$arEmployees = array_unique(array_filter($arEmployees));

			// Remove itself
			$curUserIndex = array_search($userId, $arEmployees);
			if ($curUserIndex !== false)
			{
				unset($arEmployees[$curUserIndex]);
			}
		}

		return ($arEmployees);
	}

	public static function getByDepartments($arDepartmentsIds, $arFields = array('ID'))
	{
		$dbRes = false;
		$arDepartmentsIds = array_unique(array_filter($arDepartmentsIds));

		if ( ! empty($arDepartmentsIds) )
		{
			$dbRes = \CUser::GetList(
				$by = 'ID',
				$order = 'ASC',
				array('ACTIVE' => 'Y', 'UF_DEPARTMENT' => $arDepartmentsIds),
				array(
					'SELECT' => array('UF_DEPARTMENT'),
					'FIELDS' => $arFields
				)
			);
		}

		return ($dbRes);
	}

	private static function searchImmediateEmployeesInSubDepartments($departmentId)
	{
		$arDepartmentHeads           = array();
		$arQueueDepartmentsEmployees = array();	// IDs of departments where we need employees

		$arSubDepartments = \CIntranetUtils::getSubDepartments($departmentId);
		if (is_array($arSubDepartments))
		{
			foreach ($arSubDepartments as $subDepId)
			{
				$headUserId = \CIntranetUtils::GetDepartmentManagerID($subDepId);

				if ($headUserId)
					$arDepartmentHeads[] = $headUserId;
				else
				{
					$arQueueDepartmentsEmployees[] = $subDepId;

					$result = static::searchImmediateEmployeesInSubDepartments($subDepId);

					$arDepartmentHeads = array_merge(
						$arDepartmentHeads,
						$result['arDepartmentHeads']
					);

					$arQueueDepartmentsEmployees = array_merge(
						$arQueueDepartmentsEmployees,
						$result['arQueueDepartmentsEmployees']
					);
				}
			}
		}

		return (array(
			'arDepartmentHeads'           => $arDepartmentHeads,
			'arQueueDepartmentsEmployees' => $arQueueDepartmentsEmployees
		));
	}

	private static function getDepartmentsUsersIds($arDepartmentsIds)
	{
		$arUsersIds = array();

		$dbRes = static::getByDepartments($arDepartmentsIds, $arFields = array('ID'));

		if ($dbRes !== false)
		{
			while ($arRes = $dbRes->fetch())
			{
				$arUsersIds[] = $arRes['ID'];
			}
		}

		return ($arUsersIds);
	}
}