<?php
namespace Thurly\Voximplant;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

use Thurly\Main\Entity\Event;
use Thurly\Main\Entity\DeleteResult;
use Thurly\Main\Application;
Loc::loadMessages(__FILE__);

/**
 * Class PhoneTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> PHONE_NUMBER string(20) mandatory
 * <li> PHONE_MNEMONIC string(20)
 * </ul>
 *
 * @package Thurly\Voximplant
 **/

class PhoneTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_voximplant_phone';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('PHONE_ENTITY_ID_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,

				'title' => Loc::getMessage('PHONE_ENTITY_USER_ID_FIELD'),
			),
			'USER' => array(
				'data_type' => 'Thurly\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),
			'PHONE_NUMBER' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateString'),
				'title' => Loc::getMessage('PHONE_ENTITY_PHONE_NUMBER_FIELD'),
			),
			'PHONE_MNEMONIC' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateString'),
				'title' => Loc::getMessage('PHONE_ENTITY_PHONE_MNEMONIC_FIELD'),
			),
		);
	}

	public static function validateString()
	{
		return array(
			new Entity\Validator\Length(null, 20),
		);
	}

	public static function deleteByUser($userId)
	{
		$result = new DeleteResult();
		$entity = static::getEntity();

		$userId = intval($userId);
		if ($userId <= 0)
		{
			$result->addError(new Entity\FieldError($entity->getField('USER_ID'), 'UserID must be greater than zero'));
			return $result;
		}

		$event = new Event($entity, "OnBeforeDeleteByUser", array("USER_ID"=>$userId));
		$event->send();
		if($event->getErrors($result))
			return $result;

		$event = new Event($entity, "OnDeleteByUser", array("USER_ID"=>$userId));
		$event->send();

		$tableName = $entity->getDBTableName();
		$connection = Application::getConnection();
		$sql = "DELETE FROM ".$tableName." WHERE USER_ID = ".$userId;
		$connection->queryExecute($sql);

		$event = new Event($entity, "OnAfterDeleteByUser", array("USER_ID"=>$userId));
		$event->send();

		return $result;
	}


	public static function getByUserId($userId)
	{
		$phones = array();

		$result = self::getList(Array(
			'select' => Array('PHONE_NUMBER', 'PHONE_MNEMONIC'),
			'filter' => Array('=USER_ID'=> intval($userId))
		));
		while($ar = $result->fetch())
		{
			$phones[$ar['PHONE_MNEMONIC']] = $ar['PHONE_NUMBER'];
		}

		return $phones;
	}
}