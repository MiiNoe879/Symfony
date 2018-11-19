<?php

namespace Thurly\ImOpenLines\Model;

use Thurly\Main\ArgumentException;
use Thurly\Main\Application;
use Thurly\Main\Entity;

class RoleAccessTable extends Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_imopenlines_role_access';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return array(
			'ID' => new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			'ROLE_ID' => new Entity\IntegerField('ROLE_ID', array(
				'required' => true,
			)),
			'ACCESS_CODE' => new Entity\StringField('ACCESS_CODE', array(
				'required' => true,
			)),
			'ROLE' => new Entity\ReferenceField(
				'ROLE',
				'Thurly\ImOpenLines\Model\Role',
				array('=this.ROLE_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			)
		);
	}

	/**
	 * Deletes all records from the table
	 * @return Entity\DeleteResult
	 */
	public static function truncate()
	{
		$connection = Application::getConnection();
		$entity = self::getEntity();

		$sql = "TRUNCATE TABLE ".$entity->getDBTableName();
		$connection->queryExecute($sql);

		$result = new Entity\DeleteResult();
		return $result;
	}

	/**
	 * Deletes all access codes associated with the specified role.
	 * @param int $roleId Id of the role.
	 * @return Entity\DeleteResult
	 * @throws ArgumentException
	 */
	public static function deleteByRoleId($roleId)
	{
		$roleId = (int)$roleId;
		if($roleId <= 0)
			throw new ArgumentException('Role id should be greater than zero', 'roleId');

		$connection = Application::getConnection();
		$entity = self::getEntity();

		$sql = "DELETE FROM ".$entity->getDBTableName()." WHERE ROLE_ID = ".$roleId;
		$connection->queryExecute($sql);

		$result = new Entity\DeleteResult();
		return $result;
	}
}