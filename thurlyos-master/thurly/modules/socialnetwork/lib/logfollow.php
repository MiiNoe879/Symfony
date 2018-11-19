<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage socialnetwork
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Socialnetwork;

use Thurly\Main;
use Thurly\Main\Config\Option;
use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;
use Thurly\Main\NotImplementedException;

Loc::loadMessages(__FILE__);

class LogFollowTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_follow';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'CODE' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'TYPE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
		);

		return $fieldsMap;
	}

	public static function getDefaultValue($params = array())
	{
		global $USER;

		$siteId = (
			isset($params['SITE_ID'])
				? $params['SITE_ID']
				: SITE_ID
		);

		$defaultValue = Option::get("socialnetwork", "follow_default_type", "Y", $siteId);

		$userId = (
			isset($params['USER_ID'])
				? $params['USER_ID']
				: ($USER->isAuthorized() ? $USER->getId() : false)
		);

		if (intval($userId) <= 0)
		{
			return $defaultValue;
		}

		$res = self::getList(array(
			'filter' => array(
				"USER_ID" => $userId,
				"=CODE" => "**"
			),
			'select' => array('TYPE')
		)
		);
		if ($follow = $res->fetch())
		{
			$defaultValue = $follow['TYPE'];
		}

		return $defaultValue;
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use add() method of the class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use update() method of the class.");
	}
}
