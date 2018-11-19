<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale\Location;

use Thurly\Main;
use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ExternalServiceTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_loc_ext_srv';
	}

	public static function getMap()
	{
		return array(

			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),

			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SALE_LOCATION_EXTERNAL_SERVICE_ENTITY_CODE_FIELD')
			),

			// virtual
			'EXTERNAL' => array(
				'data_type' => '\Thurly\Sale\Location\External',
				'reference' => array(
					'=this.ID' => 'ref.SERVICE_ID'
				)
			),
		);
	}
}
