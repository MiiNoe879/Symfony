<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage socialnetwork
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Socialnetwork;

use Thurly\Main\Entity;
use Thurly\Main\NotImplementedException;
use Thurly\Main\SystemException;
use Thurly\Main\DB\SqlExpression;
use Thurly\Main\DB\SqlQueryException;

/**
 * Class UserContentViewTable
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> USER reference to {@link \Thurly\Main\UserTable}
 * <li> RATING_TYPE_ID varchar mandatory
 * <li> RATING_ENTITY_ID int mandatory
 * <li> DATE_VIEW datetime
 * </ul>
 *
 * @package Thurly\Socialnetwork
 */
class UserContentViewTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_user_content_view';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'USER' => array(
				'data_type' => 'Thurly\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			'RATING_TYPE_ID' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'RATING_ENTITY_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'CONTENT_ID' => array(
				'data_type' => 'string'
			),
			'DATE_VIEW' => array(
				'data_type' => 'datetime'
			),
		);

		return $fieldsMap;
	}

	public static function set($params = array())
	{
		$userId = (isset($params['userId']) ? intval($params['userId']) : 0);
		$typeId = (isset($params['typeId']) ? trim($params['typeId']) : false);
		$entityId = (isset($params['entityId']) ? intval($params['entityId']) : 0);
		$save = (isset($params['save']) ? !!$params['save'] : false);

		if (
			$userId <= 0
			|| empty($typeId)
			|| $entityId <= 0
		)
		{
			throw new SystemException("Invalid input data.");
		}

		$saved = false;

		try
		{
			if ($save)
			{
				$connection = \Thurly\Main\Application::getConnection();
				$helper = $connection->getSqlHelper();

				$nowDate = new SqlExpression($helper->getCurrentDateTimeFunction());

				$insertFields = array(
					"USER_ID" => $userId,
					"RATING_TYPE_ID" => $typeId,
					"RATING_ENTITY_ID" => $entityId,
					"CONTENT_ID" => $typeId."-".$entityId,
					"DATE_VIEW" => $nowDate
				);

				$tableName = static::getTableName();
				list($prefix, $values) = $helper->prepareInsert($tableName, $insertFields);

				$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) VALUES ({$values})");

				$saved = true;
			}
		}
		catch(SqlQueryException $exception)
		{
		}

		return array(
			'success' => true,
			'savedInDB' => $saved
		);
	}

	public static function add(array $data)
	{
		throw new NotImplementedException("Use set() method of the class.");
	}

	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use set() method of the class.");
	}
}
