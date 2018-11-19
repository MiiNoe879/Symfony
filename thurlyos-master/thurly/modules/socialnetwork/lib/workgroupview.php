<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage socialnetwork
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Socialnetwork;

use Thurly\Main;
use Thurly\Main\Entity;
use Thurly\Main\NotImplementedException;

class WorkgroupViewTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_view';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'USER' => array(
				'data_type' => '\Thurly\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'GROUP' => array(
				'data_type' => '\Thurly\Socialnetwork\Workgroup',
				'reference' => array('=this.GROUP_ID' => 'ref.ID')
			),
			'DATE_VIEW' => array(
				'data_type' => 'datetime'
			),
		);

		return $fieldsMap;
	}

	public static function set($params = array())
	{
		global $USER, $CACHE_MANAGER;

		if (
			!is_array($params)
			|| !isset($params['GROUP_ID'])
			|| intval($params['GROUP_ID']) <= 0
		)
		{
			throw new Main\SystemException("Empty groupId.");
		}

		$groupId = intval($params['GROUP_ID']);

		$userId = (
			isset($params['USER_ID'])
			&& intval($params['USER_ID']) > 0
				? intval($params['USER_ID'])
				: $USER->getId()
		);

		if (intval($userId) <= 0)
		{
			throw new Main\SystemException("Empty userId.");
		}

		$connection = \Thurly\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$insertFields = array(
			"USER_ID" => $userId,
			"GROUP_ID" => $groupId,
			"DATE_VIEW" => new \Thurly\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
		);

		$updateFields = array(
			"DATE_VIEW" => new \Thurly\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
		);

		$merge = $helper->prepareMerge(
			static::getTableName(),
			array("USER_ID", "GROUP_ID"),
			$insertFields,
			$updateFields
		);

		if ($merge[0] != "")
		{
			$connection->query($merge[0]);
		}

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->ClearByTag("sonet_group_view_U".$userId);
		}
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use set() method of the class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use set() method of the class.");
	}
}
