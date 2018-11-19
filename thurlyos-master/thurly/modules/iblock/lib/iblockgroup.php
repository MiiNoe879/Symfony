<?php
namespace Thurly\Iblock;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class IblockGroupTable
 *
 * Fields:
 * <ul>
 * <li> IBLOCK_ID int mandatory
 * <li> GROUP_ID int mandatory
 * <li> PERMISSION string(1) mandatory
 * <li> GROUP reference to {@link \Thurly\Main\GroupTable}
 * <li> IBLOCK reference to {@link \Thurly\Iblock\IblockTable}
 * </ul>
 *
 * @package Thurly\Iblock
 **/

class IblockGroupTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_group';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_GROUP_ENTITY_IBLOCK_ID_FIELD'),
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('IBLOCK_GROUP_ENTITY_GROUP_ID_FIELD'),
			),
			'PERMISSION' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validatePermission'),
				'title' => Loc::getMessage('IBLOCK_GROUP_ENTITY_PERMISSION_FIELD'),
			),
			'GROUP' => array(
				'data_type' => 'Thurly\Main\Group',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'IBLOCK' => array(
				'data_type' => 'Thurly\Iblock\Iblock',
				'reference' => array('=this.IBLOCK_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns validators for PERMISSION field.
	 *
	 * @return array
	 */
	public static function validatePermission()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}
}