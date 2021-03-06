<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Util;

use Thurly\Main\ModuleManager;
use Thurly\Main\OperationTable;
use Thurly\Main\TaskOperationTable;
use Thurly\Main\UserTable;
use Thurly\Tasks\Integration\Extranet;
use Thurly\Tasks\Integration\IMConnector;
use Thurly\Tasks\Integration\Mail;
use Thurly\Tasks\Integration\Replica;
use Thurly\Tasks\Integration\IMBot;
use Thurly\Tasks\Integration\Intranet;

final class User
{
	private static $accessLevels = array();
	private static $accessOperations = array();
	private static $accessLevel2Operation = null;

	/**
	 * @return \CUser
	 */
	public static function get()
	{
		return $GLOBALS['USER'];
	}

	/**
	 * Returns current user ID
	 * @return integer
	 */
	public static function getId()
	{
		global $USER;

		if(is_object($USER) && method_exists($USER, 'getId'))
		{
			$userId = intval($USER->getId());
			if($userId > 0)
			{
				return $userId;
			}
		}

		return 0;
	}

	/**
	 * Checks if the current user is authorized
	 * @return bool
	 */
	public static function isAuthorized()
	{
		$id = static::getId();
		return $id ? $GLOBALS['USER']->isAuthorized() : false;
	}

	/**
	 * Check if a given user is active
	 *
	 * @param $userId
	 * @return bool
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function isActive($userId)
	{
		$userId = intval($userId);
		if(!$userId)
		{
			return false;
		}

		$user = UserTable::getList(array('filter' => array(
			'=ID' => $userId
		), 'select' => array(
			'ID', 'ACTIVE'
		)))->fetch();

		return $user['ACTIVE'] == 'Y';
	}

	/**
	 * Get admin user ID
	 * @return bool|int|null
	 */
	public static function getAdminId()
	{
		global $USER;

		$id = static::getId();
		if($id && $USER->isAdmin())
		{
			return $id;
		}

		static $admin;

		if($admin === null)
		{
			$user = \CUser::GetList(
				($by = 'id'),
				($sort = 'asc'),
				array('GROUPS_ID' => array(1), 'ACTIVE' => 'Y'),
				array('FIELDS' => array('ID'), 'NAV_PARAMS' => array('nTopCount' => 1))
			)->fetch();

			if (is_array($user) && intval($user['ID']))
			{
				$admin = intval($user['ID']);
			}
		}

		return $admin === null ? 0 : $admin;
	}

	/**
	 * Return user id previously set by setOccurAsId()
	 *
	 * @return null
	 */
	public static function getOccurAsId()
	{
		return \CTasksPerHitOption::get('tasks', static::getOccurAsIdKey());
	}

	/**
	 * Set user id that will figure in all logs and notifications as the user performed an action.
	 * This allows to create task task under admin id and put to log someone else.
	 *
	 * In general, this is a hacky function, so it could be set deprecated in future as architecture changes.
	 *
	 * @param int $userId Use 0 or null or false to switch off user replacement
	 * @return string
	 */
	public static function setOccurAsId($userId = 0)
	{
		$userId = intval($userId);

		$key = static::getOccurAsIdKey();

		// todo: use user cache here, when implemented
		if(!$userId || !\CUser::getById($userId)->fetch())
		{
			$userId = null;
		}

		\CTasksPerHitOption::set('tasks', $key, $userId);

		return $key;
	}

	/**
	 * Check if a user with a given id is admin
	 *
	 * @param 0 $userId
	 * @return bool
	 */
	public static function isAdmin($userId = 0)
	{
		global $USER;
		static $arCache = array();

		if($userId === 0 || $userId === false)
		{
			$userId = null;
		}

		$isAdmin = false;
		$loggedInUserId = null;

		if ($userId === null)
		{
			if (is_object($USER) && method_exists($USER, 'GetID'))
			{
				$loggedInUserId = (int) $USER->GetID();
				$userId = $loggedInUserId;
			}
			else
			{
				$loggedInUserId = false;
			}
		}

		if ($userId > 0)
		{
			if ( ! isset($arCache[$userId]) )
			{
				if ($loggedInUserId === null)
				{
					if (is_object($USER) && method_exists($USER, 'GetID'))
					{
						$loggedInUserId = (int) $USER->GetID();
					}
				}

				if ((int)$userId === $loggedInUserId)
				{
					$arCache[$userId] = (bool)$USER->isAdmin();
				}
				else
				{
					/** @noinspection PhpDynamicAsStaticMethodCallInspection */
					$ar = \CUser::GetUserGroup($userId);
					if (in_array(1, $ar, true) || in_array('1', $ar, true))
						$arCache[$userId] = true;	// user is admin
					else
						$arCache[$userId] = false;	// user isn't admin
				}
			}

			$isAdmin = $arCache[$userId];
		}

		return ($isAdmin);
	}

	/**
	 * Practically, just an alias for static::isAdmin()
	 *
	 * @param int $userId
	 * @return bool
	 */
	public static function isSuper($userId = 0)
	{
		return static::isAdmin($userId) || \Thurly\Tasks\Integration\ThurlyOS\User::isAdmin($userId);
	}

	/**
	 * Return data for users we really can see
	 *
	 * todo: make static cache here, call this function everywhere (at least, in CTaskNotifications)
	 *
	 * @param array $userIds
	 * @return array
	 */
	public static function getData(array $userIds)
	{
		$users = array();
		$current = static::getId();

		if(empty($userIds))
		{
			$userIds = array($current);
		}

		$parsed = array_unique(array_filter($userIds, 'intval'));
		if(!empty($parsed))
		{
			$extranetUsers = Extranet\User::getAccessible($current);
			if(is_array($extranetUsers))
			{
				$extranetUsers = array_flip($extranetUsers);
			}

			// strip away all except mail users
			$specialEAIds = array_diff(static::getArtificialExternalAuthIds(), array(
				Mail\User::getExternalCode(), Replica\User::getExternalCode()
			));

			$departmentUFCode = Intranet\User::getDepartmentUFCode();

			$select = array('*');
			if(\Thurly\Tasks\Util\Userfield\User::checkFieldExists($departmentUFCode))
			{
				$select[] = $departmentUFCode;
			}

			$filter = array(
				'ID' => $parsed,
			);
			if(!empty($specialEAIds))
			{
				$filter['!=EXTERNAL_AUTH_ID'] = $specialEAIds;
			}

			$res = UserTable::getList(array('filter' => $filter, 'select' => $select));

			while($item = $res->fetch())
			{
				$item['IS_EXTRANET_USER'] = Extranet\User::isExtranet($item);
				$item['IS_EMAIL_USER'] = Mail\User::isEmail($item);
				$item['IS_NETWORK_USER'] = ($item["EXTERNAL_AUTH_ID"] === "replica");

				if($item['ID'] != $current)
				{
					// todo: you must check what users you have access to
					/*
					if($isExtranetUser && !isset($extranetUsers[$item['ID']]))
					{
						continue;
					}
					*/
				}

				$users[$item['ID']] = $item;
			}
		}

		return $users;
	}

	public static function getArtificialExternalAuthIds()
	{
		$result = array();

		if(Mail::isInstalled())
		{
			$result[] = Mail\User::getExternalCode();
		}
		if(Replica::isInstalled())
		{
			$result[] = Replica\User::getExternalCode();
		}
		if(IMBot::isInstalled())
		{
			$result[] = IMBot\User::getExternalCode();
		}
		if(IMConnector::isInstalled())
		{
			$result[] = IMConnector\User::getExternalCode();
		}

		return $result;
	}

	/**
	 * Extract user data suitable to publicise using json_encode() or CUtil::PhpToJsObject()
	 * @param array $user
	 * @return array
	 */
	public static function extractPublicData($user)
	{
		if(!is_array($user))
		{
			return array();
		}

		$keys = static::getPublicDataKeys();
		$keys[] = 'IS_EXTRANET_USER';
		$keys[] = 'IS_EMAIL_USER';
		$keys[] = 'IS_NETWORK_USER';
		$safe = array();

		foreach($keys as $key)
		{
			if(array_key_exists($key, $user))
			{
				$safe[$key] = $user[$key];
			}
		}

		if(intval($user['ID']))
		{
			$safe['ID'] = intval($user['ID']);
		}

		return $safe;
	}

	public static function getPublicDataKeys()
	{
		return array(
			'ID',
			'NAME',
			'LAST_NAME',
			'SECOND_NAME',
			'LOGIN',
			'WORK_POSITION',
			'PERSONAL_PHOTO',
			'PERSONAL_GENDER',
			//'EMAIL',
			//\Thurly\Tasks\Integration\Intranet\User::getDepartmentUFCode()
		);
	}

	public static function formatName($data, $siteId = false, $format = null)
	{
		if($format === null)
		{
			$format = \Thurly\Tasks\Util\Site::getUserNameFormat($siteId);
		}

		return \CUser::formatName($format, $data, true, false);
	}

	public static function getTimeZoneOffsetCurrentUser()
	{
		$userId = static::getId();
		if(!$userId)
		{
			return 0; // server time
		}

		return static::getTimeZoneOffset($userId);
	}

	public static function getTimeZoneOffset($userId = 0, $utc = false)
	{
		$userId = intval($userId);
		// DO NOT set $userId = static::getId() when $userId == 0 here, because some times
		// \CTimeZone::getOffset() returns different result when the first argument is null

		$disabled = !\CTimeZone::enabled();

		if($disabled)
		{
			\CTimeZone::enable();
		}

		$offset = static::getOffset($userId ? $userId : null) + ($utc ? \Thurly\Tasks\Util::getServerTimeZoneOffset() : 0);

		if($disabled)
		{
			\CTimeZone::disable();
		}

		return intval($offset);
	}

	public static function setOption($name, $value, $userId = 0)
	{
		$userId = intval($userId);
		if(!$userId)
		{
			$userId = static::getId();
		}

		return \CUserOptions::setOption('tasks', $name, $value, false, $userId ? $userId : false);
	}

	public static function unSetOption($name, $userId = 0)
	{
		$userId = intval($userId);
		if(!$userId)
		{
			$userId = static::getId();
		}

		return \CUserOptions::deleteOption('tasks', $name, false, $userId ? $userId : false);
	}

	public static function unSetOptionForAll($name)
	{
		return \CUserOptions::deleteOptionsByName('tasks', $name);
	}

	public static function getOption($name, $userId = 0, $default = '')
	{
		$userId = intval($userId);
		if(!$userId)
		{
			$userId = static::getId();
		}

		return \CUserOptions::getOption('tasks', $name, $default, $userId ? $userId : false);
	}

	/**
	 * @param int $userId
	 * @return int
	 */
	public static function getTime($userId = 0)
	{
		return time() + static::getTimeZoneOffset($userId);
	}

	//////////////////////////////
	// about access

	public static function checkAccessOperationInLevel($operationId, $levelId)
	{
		if(static::$accessLevel2Operation === null)
		{
			$relations = array();
			$res = TaskOperationTable::getList(array(
				'filter' => array(
					'=TASK.MODULE_ID' => 'tasks',
					'=OPERATION.MODULE_ID' => 'tasks',
				)
			));
			while($item = $res->fetch())
			{
				$relations[$item['TASK_ID']][$item['OPERATION_ID']] = true;
			}

			static::$accessLevel2Operation = $relations;
		}

		return static::$accessLevel2Operation[$levelId][$operationId];
	}

	/**
	 * Get access level by name
	 *
	 * @param $entityName
	 * @param $levelName
	 * @return null
	 */
	public static function getAccessLevel($entityName, $levelName)
	{
		$entityName = ToLower(trim((string) $entityName));
		$levelName = ToLower(trim((string) $levelName));

		$levels = static::getAccessLevelsForEntity($entityName);
		foreach($levels as $level)
		{
			if($level['NAME'] == 'tasks_'.$entityName.'_'.$levelName)
			{
				return $level;
			}
		}

		return null;
	}

	/**
	 * Get access operation IDs by given names
	 */
	public static function mapAccessOperationNames($entityName, array $names)
	{
		$names = array_flip($names);

		$ops = static::getAccessOperationsForEntity($entityName);
		$resultNames = array();
		foreach($ops as $op)
		{
			if(array_key_exists($op['NAME'], $names))
			{
				$resultNames[$op['NAME']] = $op['ID'];
			}
		}

		return $resultNames;
	}

	/**
	 * Get access operations for entity
	 *
	 * @param $entityName
	 * @return array
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function getAccessOperationsForEntity($entityName)
	{
		$entityName = trim((string) $entityName);
		if($entityName == '')
		{
			return array();
		}

		if(!array_key_exists($entityName, static::$accessOperations))
		{
			// todo: there could be php cache

			$res = OperationTable::getList(array(
				'filter' => array(
					'=MODULE_ID' => 'tasks',
					'=BINDING' => $entityName,
				)
			));
			$operations = array();
			while($item = $res->fetch())
			{
				$operations[$item['ID']] = $item;
			}

			static::$accessOperations[$entityName] = $operations;
		}

		return static::$accessOperations[$entityName];
	}

	/**
	 * Get info about access tasks for entity
	 *
	 * @param $entityName
	 * @return array
	 */
	public static function getAccessLevelsForEntity($entityName)
	{
		$entityName = trim((string) $entityName);
		if($entityName == '')
		{
			return array();
		}

		if(!array_key_exists($entityName, static::$accessLevels))
		{
			// todo: there could be php cache

			$res = \CAllTask::getList(array(), array(
				'MODULE_ID' => 'tasks',
				'BINDING' => $entityName,
			));
			$levels = array();
			while($item = $res->fetch())
			{
				$levels[$item['ID']] = $item;
			}

			static::$accessLevels[$entityName] = $levels;
		}

		return static::$accessLevels[$entityName];
	}

	private function getOffset($userId)
	{
		static $cache = array();

		$key = 'U'.$userId;
		if (!array_key_exists($key, $cache))
		{
			$cache[$key] = \CTimeZone::getOffset($userId);
		}
		return $cache[$key];
	}

	private static function getOccurAsIdKey()
	{
		static $key;

		if($key == null)
		{
			$key = 'occurAs_key:' . md5(mt_rand(1000, 999999) . '-' . mt_rand(1000, 999999));
		}

		return $key;
	}

	/**
	 * Check if user is extranet user
	 * @param integer $userID User ID
	 * @return boolean
	 */
	public static function isExternalUser($userID)
	{
		if(!ModuleManager::isModuleInstalled('extranet'))
		{
			return false;
		}

		$dbResult = \CUser::getList(
			$o = 'ID',
			$b = 'ASC',
			array('ID_EQUAL_EXACT' => $userID),
			array('FIELDS' => array('ID'), 'SELECT' => array('UF_DEPARTMENT'))
		);

		$user = $dbResult->Fetch();
		return !(is_array($user)
				 && isset($user['UF_DEPARTMENT'])
				 && isset($user['UF_DEPARTMENT'][0])
				 && $user['UF_DEPARTMENT'][0] > 0);
	}

	public static function isAbsence(array $userIds)
	{
		global $DB;
		$list = array();

		if (!\CModule::IncludeModule('intranet'))
		{
			return false;
		}

		$dt = ConvertTimeStamp(false, 'SHORT');

		$arAbsenceData = \CIntranetUtils::GetAbsenceData(
			array(
				'USERS' => $userIds,
				'DATE_START' => $dt,
				'DATE_FINISH' => $dt,
				'PER_USER' => false
			),
			$MODE = BX_INTRANET_ABSENCE_ALL
		);

		$curTs = MakeTimeStamp(ConvertTimeStamp(false, 'FULL'));

		if(!empty($arAbsenceData))
		{
			foreach($arAbsenceData as $item)
			{
				if (
					array_key_exists('DATE_ACTIVE_FROM', $item)
					&& array_key_exists('DATE_ACTIVE_TO', $item)
				)
				{
					$fromTs = MakeTimeStamp($item['DATE_ACTIVE_FROM']);
					$toTs = MakeTimeStamp($item['DATE_ACTIVE_TO']);
				}
				else
				{
					$fromTs = MakeTimeStamp($item['DATE_FROM']);
					$toTs = MakeTimeStamp($item['DATE_TO']);
				}

				if ($toTs > $curTs)
				{
					$from = FormatDate(
						$DB->DateFormatToPhp(\CSite::GetDateFormat(
							\CIntranetUtils::IsDateTime($fromTs) ? 'FULL' : 'SHORT'
						)),
						$fromTs
					);

					$to = FormatDate(
						$DB->DateFormatToPhp(\CSite::GetDateFormat(
							\CIntranetUtils::IsDateTime($toTs) ? 'FULL' : 'SHORT'
						)),
						$toTs
					);

					$list[]= GetMessageJS('TASKS_WARNING_RESPONSIBLE_IS_ABSENCE', array(
						'#FORMATTED_USER_NAME#'=>\Thurly\Tasks\Util\User::getUserName($item['PROPERTY_USER_VALUE']),
						'#DATE_FROM#'=>$from,
						'#DATE_TO#'=>$to,
						'#ABSCENCE_REASON#'=>$item['NAME']
					));
				}
			}
		}

		return $list;
	}

	public static function getUserName($userId, $siteId = null, $nameTemplate = null)
	{
		static $data = array();

		if(!array_key_exists($userId, $data))
		{
			$users = self::getData(array($userId));
			$data[$userId] = self::formatName($users[$userId], $nameTemplate);
		}

		return $data[$userId];
	}
}