<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale\Location;

class GroupLocationTable extends Connector
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_location2location_group';
	}

	public function getLinkField()
	{
		return 'LOCATION_GROUP_ID';
	}

	public function getTargetEntityName()
	{
		return 'Thurly\Sale\Location\Group';
	}

	public static function getUseGroups()
	{
		return false;
	}

	public static function getMap()
	{
		return array(

			'LOCATION_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'required' => true
			),

			'LOCATION_GROUP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'required' => true
			),

			// alias
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'expression' => array(
					'%u',
					'LOCATION_GROUP_ID'
				)
			),

			// virtual
			'LOCATION' => array(
				'data_type' => '\Thurly\Sale\Location\Location',
				'reference' => array(
					'=this.LOCATION_ID' => 'ref.ID'
				)
			),

			// alias
			'GROUP' => array(
				'data_type' => '\Thurly\Sale\Location\Group',
				'reference' => array(
					'=this.LOCATION_GROUP_ID' => 'ref.ID'
				)
			),
		);
	}

	public static function deleteByGroupId($groupId)
	{
		if(intval($groupId) <= 0)
			return;

		$con = \Thurly\Main\Application::getConnection();
		$con->queryExecute("DELETE FROM ".self::getTableName()." WHERE LOCATION_GROUP_ID=".intval($groupId));
	}
}