<?php
namespace Thurly\Im\Model;

use \Thurly\Main\Type\DateTime,
	\Thurly\Main\Localization\Loc,
	\Thurly\Main\Entity\DataManager,
	\Thurly\Main\Entity\IntegerField,
	\Thurly\Main\Entity\DatetimeField;
Loc::loadMessages(__FILE__);

/**
 * Class NoRelationPermissionDiskTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHAT_ID int optional
 * <li> USER_ID int optional
 * </ul>
 *
 * @package Thurly\Im
 **/

class NoRelationPermissionDiskTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_no_relation_permission_disk';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('NO_RELATION_PERMISSION_DISK_ENTITY_ID_FIELD'),
			)),
			new IntegerField('CHAT_ID', array(
				'required' => true,
				'title' => Loc::getMessage('NO_RELATION_PERMISSION_DISK_ENTITY_CHAT_ID_FIELD'),
			)),
			new IntegerField('USER_ID', array(
				'required' => true,
				'title' => Loc::getMessage('NO_RELATION_PERMISSION_DISK_ENTITY_USER_ID_FIELD'),
			)),
			new DatetimeField('ACTIVE_TO', array(
				'required' => true,
				'default_value' => new DateTime,
				'title' => Loc::getMessage('NO_RELATION_PERMISSION_DISK_ENTITY_ACTIVE_TO_FIELD'),
			)),
		);
	}
}