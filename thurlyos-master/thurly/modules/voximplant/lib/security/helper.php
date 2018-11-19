<?php

namespace Thurly\Voximplant\Security;

use Thurly\ThurlyOS\Feature;
use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;
use Thurly\Voximplant\Model;
use Thurly\Voximplant\PhoneTable;

Loc::loadMessages(__FILE__);

class Helper
{
	/**
	 * @param int $userId
	 * @param string $permission
	 * @return array|null Returns array of owner user ids if there is limit and null if query should not be limited.
	 */
	public static function getAllowedUserIds($userId, $permission)
	{
		$result = array();
		switch ($permission)
		{
			case Permissions::PERMISSION_NONE:
				$result = array();
				break;
			case Permissions::PERMISSION_SELF:
				$result = array($userId);
				break;
			case Permissions::PERMISSION_DEPARTMENT:
				$result = self::getUserColleagues($userId);
				break;
			case Permissions::PERMISSION_ANY:
				$result = null;
				break;
		}

		return $result;
	}

	/**
	 * @return int
	 */
	public static function getCurrentUserId()
	{
		return (int)$GLOBALS['USER']->GetID();
	}

	/**
	 * @param $userId
	 * @return array
	 */
	public static function getUserColleagues($userId)
	{
		if(!Loader::includeModule('intranet'))
			return array();

		$result = array();
		$cursor = \CIntranetUtils::getDepartmentColleagues($userId, true);

		while ($row = $cursor->Fetch())
		{
			$result[] = (int)$row['ID'];
		}
		return $result;
	}

	public static function isMainMenuEnabled()
	{
		return (
			self::isBalanceMenuEnabled() ||
			self::isSettingsMenuEnabled() ||
			self::isLinesMenuEnabled() ||
			self::isUsersMenuEnabled()
		);
	}

	public static function isBalanceMenuEnabled()
	{
		$permissions = Permissions::createWithCurrentUser();
		return (
			$permissions->canPerform(Permissions::ENTITY_CALL_DETAIL, Permissions::ACTION_VIEW) ||
			$permissions->canPerform(Permissions::ENTITY_LINE, Permissions::ACTION_MODIFY)
		);
	}

	public static function isSettingsMenuEnabled()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canModifySettings();
	}

	public static function isLinesMenuEnabled()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canModifyLines();
	}

	public static function isUsersMenuEnabled()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canPerform(Permissions::ENTITY_USER, Permissions::ACTION_MODIFY);
	}

	public static function clearMenuCache()
	{
		\Thurly\Main\Application::getInstance()->getTaggedCache()->clearByTag('thurly:menu');
	}

	public static function canCurrentUserPerformCalls()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canPerform(Permissions::ENTITY_CALL, Permissions::ACTION_PERFORM);
	}

	public static function canCurrentUserCallFromCrm()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canPerform(Permissions::ENTITY_CALL, Permissions::ACTION_PERFORM, Permissions::PERMISSION_CALL_CRM);
	}

	public static function canCurrentUserPerformAnyCall()
	{
		$permissions = Permissions::createWithCurrentUser();
		return $permissions->canPerform(Permissions::ENTITY_CALL, Permissions::ACTION_PERFORM, Permissions::PERMISSION_ANY);
	}

	public static function canUserCallNumber($userId, $number)
	{
		$result = false;
		$userPermissions = Permissions::createWithUserId($userId);
		$callPermission = $userPermissions->getPermission(Permissions::ENTITY_CALL, Permissions::ACTION_PERFORM);

		switch ($callPermission)
		{
			case Permissions::PERMISSION_NONE:
				$result = false;
				break;
			case Permissions::PERMISSION_ANY:
				$result = true;
				break;
			case Permissions::PERMISSION_CALL_CRM:
				$result = (\CVoxImplantCrmHelper::GetCrmEntity($number, $userId) !== false);
				break;
			case Permissions::PERMISSION_CALL_USERS:
				$result = (\CVoxImplantCrmHelper::GetCrmEntity($number, $userId) !== false);
				if(!$result)
				{
					$cursor = PhoneTable::getList(array(
						'filter' => array(
							'PHONE_NUMBER' => $number,
						)
					));
					$result = ($cursor->fetch() !== false);					
				}
				break;
		}
		return $result;
	}

	public static function canUse()
	{
		if(!Loader::includeModule('thurlyos'))
			return true;

		return Feature::isFeatureEnabled('voximplant_security');
	}

	/**
	 * Deletes oll roles and permissions and creates default ones instead.
	 * @return null
	 */
	public static function resetToDefault()
	{
		Model\RoleTable::truncate();
		Model\RoleAccessTable::truncate();
		Model\RolePermissionTable::truncate();

		static::createDefaultRoles();
	}

	/**
	 * Creates default roles and associates them whith access tokens.
	 * @throws \Thurly\Main\ArgumentException
	 * @throws \Thurly\Main\LoaderException
	 * @throws \Exception
	 */
	public static function createDefaultRoles()
	{
		$checkCursor = \Thurly\Voximplant\Model\RoleTable::getList(array(
			'limit' => 1
		));

		if($checkCursor->fetch())
			return false;

		$defaultRoles = array(
			'admin' => array(
				'NAME' => Loc::getMessage('VOXIMPLANT_ROLE_ADMIN'),
				'PERMISSIONS' => array(
					'CALL_DETAIL' => array(
						'VIEW' => 'X',
					),
					'CALL' => array(
						'PERFORM' => 'X'
					),
					'CALL_RECORD' => array(
						'LISTEN' => 'X'
					),
					'USER' => array(
						'MODIFY' => 'X'
					),
					'SETTINGS' => array(
						'MODIFY' => 'X'
					),
					'LINE' => array(
						'MODIFY' => 'X'
					)
				)
			),
			'chief' => array(
				'NAME' => Loc::getMessage('VOXIMPLANT_ROLE_CHIEF'),
				'PERMISSIONS' => array(
					'CALL_DETAIL' => array(
						'VIEW' => 'X',
					),
					'CALL' => array(
						'PERFORM' => 'X'
					),
					'CALL_RECORD' => array(
						'LISTEN' => 'X'
					),
				)
			),
			'department_head' => array(
				'NAME' => Loc::getMessage('VOXIMPLANT_ROLE_DEPARTMENT_HEAD'),
				'PERMISSIONS' => array(
					'CALL_DETAIL' => array(
						'VIEW' => 'D',
					),
					'CALL' => array(
						'PERFORM' => 'X'
					),
					'CALL_RECORD' => array(
						'LISTEN' => 'D'
					),
				)
			),
			'manager' => array(
				'NAME' => Loc::getMessage('VOXIMPLANT_ROLE_MANAGER'),
				'PERMISSIONS' => array(
					'CALL_DETAIL' => array(
						'VIEW' => 'A',
					),
					'CALL' => array(
						'PERFORM' => 'X'
					),
					'CALL_RECORD' => array(
						'LISTEN' => 'A'
					),
				)
			)
		);

		$roleIds = array();
		foreach ($defaultRoles as $roleCode => $role)
		{
			$addResult = \Thurly\Voximplant\Model\RoleTable::add(array(
				'NAME' => $role['NAME'],
			));

			$roleId = $addResult->getId();
			if($roleId)
			{
				$roleIds[$roleCode] = $roleId;
				\Thurly\Voximplant\Security\RoleManager::setRolePermissions($roleId, $role['PERMISSIONS']);
			}
		}

		if(isset($roleIds['admin']))
		{
			\Thurly\Voximplant\Model\RoleAccessTable::add(array(
				'ROLE_ID' => $roleIds['admin'],
				'ACCESS_CODE' => 'G1'
			));
		}

		if(isset($roleIds['manager']) && \Thurly\Main\Loader::includeModule('intranet'))
		{
			$departmentTree = \CIntranetUtils::GetDeparmentsTree();
			$rootDepartment = (int)$departmentTree[0][0];

			if($rootDepartment > 0)
			{
				\Thurly\Voximplant\Model\RoleAccessTable::add(array(
					'ROLE_ID' => $roleIds['manager'],
					'ACCESS_CODE' => 'DR'.$rootDepartment
				));
			}
		}

		return true;
	}
}