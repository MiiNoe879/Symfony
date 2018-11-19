<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale\Internals;

use Thurly\Main;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FuserTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_fuser';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		global $DB;

		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime'
			),
			'DATE_INS' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->DatetimeToDateFunction('%s'), 'DATE_INSERT'
				)
			),
			'DATE_UPDATE' => array(
				'data_type' => 'datetime'
			),
			'DATE_UPD' => array(
				'data_type' => 'datetime',
				'expression' => array(
					$DB->DatetimeToDateFunction('%s'), 'DATE_UPDATE'
				)
			),
			new Main\Entity\IntegerField(
				"USER_ID"
			),

			'USER' => array(
				'data_type' => 'Thurly\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID')
			),

			new Main\Entity\StringField(
				'CODE', 
				array(
					'size' => 32
				)),
		);
	}

	/**
	 * Return user id for fuser id from database
	 *
	 * @param int $id			Fuser id.
	 * @return bool|int
	 * @throws Main\ArgumentException
	 */
	public static function getUserById($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			return false;
		$fuserIterator = self::getList(array(
			'select' => array('USER_ID'),
			'filter' => array('=ID' => $id)
		));
		if ($fuser = $fuserIterator->fetch())
			return (int)$fuser['USER_ID'];
		return false;
	}
}