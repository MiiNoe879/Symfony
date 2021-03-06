<?php
namespace Thurly\Disk\Internals\Rights\Table;

use Thurly\Disk\Internals\DataManager;
use Thurly\Main\Application;
use Thurly\Main\Type\DateTime;

final class RightSetupSessionTable extends DataManager
{
	const STATUS_STARTED  = 2;
	const STATUS_FINISHED = 3;
	const STATUS_FORKED   = 4;
	const STATUS_BAD      = 5;

	/**
	 * In 5 minutes we decide to restart setup session and try again.
	 */
	const LIFETIME_SECONDS = 300;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_disk_right_setup_session';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$deathTime = $sqlHelper->addSecondsToDateTime(self::LIFETIME_SECONDS, 'CREATE_TIME');
		$now = $sqlHelper->getCurrentDateTimeFunction();

		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
			),
			'PARENT' => array(
				'data_type' => 'Thurly\Disk\Internals\Rights\Table\RightSetupSessionTable',
				'reference' => array(
					'=this.PARENT_ID' => 'ref.ID'
				),
			),
			'OBJECT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'OBJECT' => array(
				'data_type' => 'Thurly\Disk\Internals\ObjectTable',
				'reference' => array(
					'=this.OBJECT_ID' => 'ref.ID'
				),
				'join_type' => 'INNER',
			),
			'STATUS' => array(
				'data_type' => 'integer',
				'default_value' => self::STATUS_STARTED,
			),
			'CREATE_TIME' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
				'required' => true,
			),
			'IS_EXPIRED' => array(
				'data_type' => 'boolean',
				'expression' => array(
					"CASE WHEN ({$now} > {$deathTime}) THEN 1 ELSE 0 END"
				),
				'values' => array(0, 1),
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
			),
		);
	}

	/**
	 * Marks as bad sessions which were calculated without success many times and
	 * deletes tmp simple rights by these sessions.
	 *
	 * @return void
	 */
	public static function markAsBad()
	{
		$badStatus = self::STATUS_BAD;
		$startedStatus = self::STATUS_STARTED;

		$connection = Application::getConnection();
		$connection->queryExecute("
			UPDATE b_disk_right_setup_session s
				INNER JOIN b_disk_right_setup_session s1 ON s1.PARENT_ID=s.ID
				INNER JOIN b_disk_right_setup_session s2 ON s2.PARENT_ID=s1.ID
			SET s2.STATUS = {$badStatus}
			WHERE s2.STATUS = {$startedStatus} 		
		");

		if ($connection->getAffectedRowsCount() > 0)
		{
			$connection->queryExecute("
				DELETE tmp_sright FROM b_disk_tmp_simple_right tmp_sright
					INNER JOIN b_disk_right_setup_session s ON s.ID = tmp_sright.SESSION_ID
				WHERE s.STATUS = {$badStatus}
			");
		}
	}
}