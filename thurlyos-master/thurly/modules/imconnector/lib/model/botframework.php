<?php
namespace Thurly\ImConnector\Model;

use \Thurly\Main\Entity\TextField,
	\Thurly\Main\Entity\StringField,
	\Thurly\Main\Entity\DataManager,
	\Thurly\Main\Entity\IntegerField;

/**
 * Class BotFrameworkTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ID_CHAT string mandatory
 * <li> ID_MESSAGE string
 * <li> VIRTUAL_CONNECTOR string mandatory
 * <li> DATA text
 * </ul>
 *
 * @package Thurly\ImConnector
 */
class BotFrameworkTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_imconnectors_botframework';
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
			)),
			new StringField('ID_CHAT', array(
				'required' => true,
			)),
			new StringField('ID_MESSAGE'),
			new StringField('VIRTUAL_CONNECTOR', array(
				'required' => true,
			)),
			new TextField('DATA', array(
				'serialized' => true
			)),
		);
	}
}