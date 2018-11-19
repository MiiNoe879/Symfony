<?php
/**
 * Thurly Framework
 * @package Thurly\Sale\Location
 * @subpackage sale
 * @copyright 2001-2014 Thurly
 */
namespace Thurly\Sale\Location\Search;

use Thurly\Main;
use Thurly\Main\DB;
use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

use Thurly\Sale\Location;
use Thurly\Sale\Location\DB\Helper;

Loc::loadMessages(__FILE__);

final class SiteLinkTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_loc_search_sitlnk';
	}

	public static function checkTableExists()
	{
		static $tableExists;

		if($tableExists === null)
			$tableExists = Main\HttpApplication::getConnection()->isTableExists(static::getTableName());

		return $tableExists;
	}

	public static function cleanUpData()
	{
		Helper::dropTable(static::getTableName());

		// ORACLE: OK, MSSQL: OK
		$sql = "create table ".static::getTableName()." 
			(
				LOCATION_ID ".Helper::getSqlForDataType('int').",
				SITE_ID ".Helper::getSqlForDataType('char', 2).",

				primary key (LOCATION_ID, SITE_ID)
			)";

		Main\HttpApplication::getConnection()->query($sql);
	}

	public static function initializeData()
	{
		$locationTable = Location\LocationTable::getTableName();
		$groupLocationTable = Location\GroupLocationTable::getTableName();
		$siteLocationTable = Location\SiteLocationTable::getTableName();

		// ORACLE: OK, MSSQL: OK
		$sql = "
			insert into ".static::getTableName()." 
				(LOCATION_ID, SITE_ID) 
			select LC.ID, LS.SITE_ID
				from ".$siteLocationTable." LS
					inner join ".$locationTable." L on LS.LOCATION_ID = L.ID and LS.LOCATION_TYPE = 'L'
					inner join ".$locationTable." LC on LC.LEFT_MARGIN >= L.LEFT_MARGIN and LC.RIGHT_MARGIN <= L.RIGHT_MARGIN
			union 
			select LC.ID, LS.SITE_ID
				from ".$siteLocationTable." LS
					inner join ".$groupLocationTable." LG on LS.LOCATION_ID = LG.LOCATION_GROUP_ID and LS.LOCATION_TYPE = 'G'
					inner join ".$locationTable." L on LG.LOCATION_ID = L.ID
					inner join ".$locationTable." LC on LC.LEFT_MARGIN >= L.LEFT_MARGIN and LC.RIGHT_MARGIN <= L.RIGHT_MARGIN
		";

		Main\HttpApplication::getConnection()->query($sql);
	}

	public static function createIndex()
	{
		Helper::createIndex(static::getTableName(), 'S', array('SITE_ID'));
	}

	public static function getMap()
	{
		return array(

			'LOCATION_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true
			),
			'SITE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true
			),
		);
	}
}

