<?php

namespace Thurly\Main;

use Thurly\Main\Entity;

class TaskOperationTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_task_operation';
	}

	public static function getMap()
	{
		return array(
			'TASK_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'OPERATION_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'OPERATION' => array(
				'data_type' => 'Thurly\Main\OperationTable',
				'reference' => array('=this.OPERATION_ID' => 'ref.ID'),
			),
			'TASK' => array(
				'data_type' => 'Thurly\Main\TaskTable',
				'reference' => array('=this.TASK_ID' => 'ref.ID'),
			),
		);
	}
}