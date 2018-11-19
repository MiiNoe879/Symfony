<?php
namespace Thurly\Tasks\Replica;

class TaskElapsedTimeHandler extends \Thurly\Replica\Client\BaseHandler
{
	protected $tableName = "b_tasks_elapsed_time";
	protected $moduleId = "tasks";
	protected $className = "\\Thurly\\Tasks\\Internals\\Task\\ElapsedTimeTable";

	protected $primary = array(
		"ID" => "auto_increment",
	);
	protected $predicates = array(
		"TASK_ID" => "b_tasks.ID",
		"USER_ID" => "b_user.ID",
	);
	protected $translation = array(
		"ID" => "b_tasks_elapsed_time.ID",
		"TASK_ID" => "b_tasks.ID",
		"USER_ID" => "b_user.ID",
	);
}
