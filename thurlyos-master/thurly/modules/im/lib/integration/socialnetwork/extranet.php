<?php
namespace Thurly\Im\Integration\Socialnetwork;

use Thurly\Main\Localization\Loc;

class Extranet
{
	const CACHE_TOKEN_TTL = 2592000; // 1 month

	public static function checkModules()
	{
		return \Thurly\Main\Loader::includeModule('extranet') && \Thurly\Main\Loader::includeModule("socialnetwork");
	}

	public static function getGroup($params)
	{
		if (!self::checkModules())
			return false;

		$params = is_array($params)? $params: Array();

		if (!isset($params['CURRENT_USER']) && is_object($GLOBALS['USER']))
		{
			$params['CURRENT_USER'] = $GLOBALS['USER']->GetID();
		}

		$userId = intval($params['CURRENT_USER']);
		if ($userId <= 0)
		{
			return false;
		}

		$cacheId = 'im_sonet_extranet_v2_'.$userId;
		$cachePath = '/bx/imc/sonet/extranet'.\Thurly\Im\Common::getCacheUserPostfix($userId);

		$cache = \Thurly\Main\Application::getInstance()->getCache();
		$taggedCache = \Thurly\Main\Application::getInstance()->getTaggedCache();

		if($cache->initCache(self::CACHE_TOKEN_TTL, $cacheId, $cachePath))
		{
			return $cache->getVars();
		}

		$taggedCache->startTagCache($cachePath);

		$db = \CSocNetUserToGroup::GetList(
			array(),
			array(
				"USER_ID" => $userId,
				"<=ROLE" => SONET_ROLES_USER,
				"GROUP_SITE_ID" => \CExtranet::GetExtranetSiteID(),
				"GROUP_ACTIVE" => "Y",
				"GROUP_CLOSED" => "N"
			),
			false,
			false,
			array("ID", "GROUP_ID", "GROUP_NAME")
		);

		$groups = Array();
		$groupIds = Array();
		while ($row = $db->GetNext(true, false))
		{
			$groupIds[] = $row["GROUP_ID"];
			$groups['SG'.$row['GROUP_ID']] = array(
				'ID' => 'SG'.$row['GROUP_ID'],
				'NAME' => Loc::getMessage('IM_INT_SN_GROUP_EXTRANET', Array('#GROUP_NAME#' => $row['GROUP_NAME'])),
				'USERS' => Array()
			);

			$taggedCache->registerTag('sonet_group_'.$row['GROUP_ID']);
			$taggedCache->registerTag('sonet_user2group_G'.$row['GROUP_ID']);
		}

		if (count($groups) <= 0)
		{
			return false;
		}

		$taggedCache->endTagCache();

		$db = \CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" => $groupIds,
				"<=ROLE" => SONET_ROLES_USER,
				"USER_ACTIVE" => "Y",
				"USER_CONFIRM_CODE" => false
			),
			false,
			false,
			array("ID", "USER_ID", "GROUP_ID")
		);
		while ($ar = $db->GetNext(true, false))
		{
			if($ar["USER_ID"] == $userId)
				continue;

			$groups['SG'.$row['GROUP_ID']]['USERS'][] = $ar["USER_ID"];
		}

		$cache->startDataCache();
		$cache->endDataCache($groups);

		return $groups;
	}
}



