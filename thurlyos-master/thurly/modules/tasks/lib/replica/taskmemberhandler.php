<?php
namespace Thurly\Tasks\Replica;

class TaskMemberHandler extends \Thurly\Replica\Client\BaseHandler
{
	protected $tableName = "b_tasks_member";
	protected $moduleId = "tasks";
	protected $className = "\\Thurly\\Tasks\\Internals\\Task\\MemberTable";

	protected $primary = array(
		"TASK_ID" => "b_tasks.ID",
		"USER_ID" => "b_user.ID",
		"TYPE" => "string",
	);
	protected $predicates = array(
		"TASK_ID" => "b_tasks.ID",
		"USER_ID" => "b_user.ID",
	);
	protected $translation = array(
		"TASK_ID" => "b_tasks.ID",
		"USER_ID" => "b_user.ID",
	);
}
