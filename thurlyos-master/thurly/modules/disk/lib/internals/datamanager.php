<?php

namespace Thurly\Disk\Internals;

use Thurly\Disk\Internals\Entity\Query;
use Thurly\Main\Application;
use Thurly\Main\ArgumentNullException;
use Thurly\Main\DB\MssqlConnection;
use Thurly\Main\DB\MysqlCommonConnection;
use Thurly\Main\DB\OracleConnection;

abstract class DataManager extends \Thurly\Main\Entity\DataManager
{
	const MAX_LENGTH_BATCH_MYSQL_QUERY = 2048;

	/**
	 * @return string the fully qualified name of this class.
	 */
	public static function className()
	{
		return get_called_class();
	}

	/**
	 * Creates and returns the Query object for the entity
	 *
	 * @return Query
	 */
	public static function query()
	{
		return new Query(static::getEntity());
	}

	/**
	 * Deletes rows by filter.
	 * @param array $filter Filter
	 * @throws \Thurly\Main\ArgumentNullException
	 * @return bool
	 */
	public static function deleteByFilter(array $filter)
	{
		if (!$filter)
		{
			throw new ArgumentNullException('filter');
		}

		$result = static::getList(array(
			'select' => array('ID'),
			'filter' => $filter,
		));
		while($row = $result->fetch())
		{
			if(!empty($row['ID']))
			{
				$resultDelete = static::delete($row['ID']);
				if(!$resultDelete->isSuccess())
				{
					return false;
				}
			}
		}
		//todo? Return new DbResult with lists of deleted object?
		return true;
	}

	/**
	 * Adds rows to table.
	 * @param array $items Items.
	 * @internal
	 */
	protected static function insertBatch(array $items)
	{
		if (!$items)
		{
			return;
		}

		$tableName = static::getTableName();
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$query = $prefix = '';
		if($connection instanceof MysqlCommonConnection)
		{
			foreach ($items as $item)
			{
				list($prefix, $values) = $sqlHelper->prepareInsert($tableName, $item);

				$query .= ($query? ', ' : ' ') . '(' . $values . ')';
				if(strlen($query) > self::MAX_LENGTH_BATCH_MYSQL_QUERY)
				{
					$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) VALUES {$query}");
					$query = '';
				}
			}
			unset($item);

			if($query && $prefix)
			{
				$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) VALUES {$query}");
			}
		}
		elseif($connection instanceof MssqlConnection)
		{
			$valueData = array();
			foreach ($items as $item)
			{
				list($prefix, $values) = $sqlHelper->prepareInsert($tableName, $item);
				$valueData[] = "SELECT {$values}";
			}
			unset($item);

			$valuesSql = implode(' UNION ALL ', $valueData);
			if($valuesSql && $prefix)
			{
				$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) $valuesSql");
			}
		}
		elseif($connection instanceof OracleConnection)
		{
			$valueData = array();
			foreach ($items as $item)
			{
				list($prefix, $values) = $sqlHelper->prepareInsert($tableName, $item);
				$valueData[] = "SELECT {$values} FROM dual";
			}
			unset($item);

			$valuesSql = implode(' UNION ALL ', $valueData);
			if($valuesSql && $prefix)
			{
				$connection->queryExecute("INSERT INTO {$tableName} ({$prefix}) $valuesSql");
			}
		}
	}

	/**
	 * Deletes rows by filter.
	 * @param array $filter Filter does not look like filter in getList. It depends by current implementation.
	 * @internal
	 */
	protected static function deleteBatch(array $filter)
	{
	}

	/**
	 * Updates rows by filter (simple format).
	 * @param array $fields Fields.
	 * @param array $filter Filter.
	 */
	protected static function updateBatch(array $fields, array $filter)
	{
		$tableName = static::getTableName();
		$connection = Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();

		$update = $sqlHelper->prepareUpdate($tableName, $fields);

		$query = new Query(static::getEntity());
		$query->setFilter($filter);
		$query->getQuery();

		$alias = $sqlHelper->quote($query->getInitAlias()) . '.';
		$where = str_replace($alias, '', $query->getWhere());

		$sql = 'UPDATE ' . $tableName . ' SET ' . $update[0] . ' WHERE ' . $where;
		$connection->queryExecute($sql, $update[1]);
	}
}