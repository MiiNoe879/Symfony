<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale\Location;

class SiteLocationTable extends Connector
{
	const ALL_SITES = '*';

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_loc_2site';
	}

	public function getLinkField()
	{
		return 'SITE_ID';
	}

	public function getTargetEntityName()
	{
		return 'Thurly\Main\Site';
	}

	public static function getUseLinkTracking()
	{
		return true;
	}

	public static function getTargetEntityPrimaryField()
	{
		return 'LID';
	}

	public static function onAfterModifiy()
	{
		// todo: re-generate index here later

		Search\Finder::setIndexInvalid();
	}

	public static function getMap()
	{
		return array(

			'SITE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'primary' => true
			),
			'LOCATION_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true
			),
			'LOCATION_TYPE' => array(
				'data_type' => 'string',
				'default_value' => self::DB_LOCATION_FLAG,
				'required' => true,
				'primary' => true
			),

			// virtual
			'LOCATION' => array(
				'data_type' => '\Thurly\Sale\Location\Location',
				'reference' => array(
					'=this.LOCATION_ID' => 'ref.ID',
					'=this.LOCATION_TYPE' => array('?', self::DB_LOCATION_FLAG)
				),
				'join_type' => 'inner'
			),
			'GROUP' => array(
				'data_type' => '\Thurly\Sale\Location\Group',
				'reference' => array(
					'=this.LOCATION_ID' => 'ref.ID',
					'=this.LOCATION_TYPE' => array('?', self::DB_GROUP_FLAG)
				)
			),

			'SITE' => array(
				'data_type' => '\Thurly\Main\Site',
				'reference' => array(
					'=this.SITE_ID' => 'ref.LID'
				)
			),

		);
	}
}

