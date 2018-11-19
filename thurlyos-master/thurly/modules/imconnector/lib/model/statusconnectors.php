<?php
namespace Thurly\ImConnector\Model;

use \Thurly\Main\Entity\TextField,
	\Thurly\Main\Entity\StringField,
	\Thurly\Main\Entity\DataManager,
	\Thurly\Main\Entity\IntegerField,
	\Thurly\Main\Entity\BooleanField;

/**
 * Class StatusConnectorsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CONNECTOR string mandatory: ID connector
 * <li> LINE string optional: ID line
 * <li> ACTIVE bool optional default 'N': A sign of activity connector
 * <li> CONNECTION bool optional default 'N': The connection tested successfully
 * <li> REGISTER bool optional default 'N': Registration was successful
 * <li> ERROR bool optional default 'N': The signal errors in the process error
 * <li> DATA text
 * </ul>
 *
 * @package Thurly\ImConnector
 */
class StatusConnectorsTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_imconnectors_status';
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
			new StringField('CONNECTOR', array(
				'required' => true,
			)),
			new StringField('LINE'),
			new BooleanField('ACTIVE', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N'
			)),
			new BooleanField('CONNECTION', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N'
			)),
			new BooleanField('REGISTER', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N'
			)),
			new BooleanField('ERROR', array(
				'values' => array('N', 'Y'),
				'default_value' => 'N'
			)),
			new TextField('DATA', array(
				'serialized' => true
			)),
		);
	}
}