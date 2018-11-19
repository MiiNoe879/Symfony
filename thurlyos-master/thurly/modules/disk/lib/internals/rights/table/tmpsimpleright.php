<?php
namespace Thurly\Disk\Internals\Rights\Table;

use Thurly\Disk\Internals\DataManager;
use Thurly\Disk\Internals\ObjectPathTable;
use Thurly\Main\Application;
use Thurly\Main\Entity\Validator\Length;

final class TmpSimpleRightTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_disk_tmp_simple_right';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'OBJECT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'ACCESS_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateAccessCode'),
			),
			'SESSION_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
		);
	}
	/**
	 * Returns validators for ACCESS_CODE field.
	 *
	 * @return array
	 */
	public static function validateAccessCode()
	{
		return array(
			new Length(null, 50),
		);
	}

	/**
	 * Adds rows to table.
	 * @param array $items Items.
	 * @internal
	 */
	public static function insertBatchBySessionId(array $items, $sessionId)
	{
		foreach ($items as &$item)
		{
			$item['SESSION_ID'] = $sessionId;
		}

		parent::insertBatch($items);
	}

	/**
	 * Fills descendants simple rights by simple rights of object
	 * @internal
	 * @param int $objectId Id of object.
	 */
	public static function fillDescendants($objectId, $sessionId)
	{
		$pathTableName = ObjectPathTable::getTableName();
		$connection = Application::getConnection();
		$sessionId = (int)$sessionId;

		$objectId = (int)$objectId;
		$connection->queryExecute("
			INSERT INTO b_disk_tmp_simple_right (OBJECT_ID, ACCESS_CODE, SESSION_ID)
			SELECT path.OBJECT_ID, sright.ACCESS_CODE, {$sessionId} FROM {$pathTableName} path
				INNER JOIN b_disk_tmp_simple_right sright ON sright.OBJECT_ID = path.PARENT_ID
			WHERE path.PARENT_ID = {$objectId} AND sright.SESSION_ID = {$sessionId}
		");
	}

	public static function moveToOriginalSimpleRights($sessionId)
	{
		$connection = Application::getConnection();

		$connection->queryExecute("
			INSERT INTO b_disk_simple_right (OBJECT_ID, ACCESS_CODE)
			SELECT tmp_right.OBJECT_ID, tmp_right.ACCESS_CODE FROM b_disk_tmp_simple_right tmp_right
			WHERE tmp_right.SESSION_ID = {$sessionId}
		");
	}

	public static function deleteBySessionId($sessionId)
	{
		$sessionId = (int)$sessionId;
		$connection = Application::getConnection();

		$connection->queryExecute("
			DELETE FROM b_disk_tmp_simple_right WHERE SESSION_ID = {$sessionId}
		");
	}
}