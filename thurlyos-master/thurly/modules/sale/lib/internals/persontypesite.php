<?php
namespace Thurly\Sale\Internals;

use Thurly\Main,
	Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class PersonTypeSiteTable
 *
 * Fields:
 * <ul>
 * <li> PERSON_TYPE_ID int mandatory
 * <li> SITE_ID string(2) mandatory
 * </ul>
 *
 * @package Thurly\Sale
 **/

class PersonTypeSiteTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_person_type_site';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'PERSON_TYPE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateSiteId'),
			),
		);
	}

}