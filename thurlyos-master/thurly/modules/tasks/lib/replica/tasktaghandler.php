<?php
namespace Thurly\Tasks\Replica;

class TaskTagHandler extends \Thurly\Replica\Client\BaseHandler
{
	protected $tableName = "b_tasks_tag";
	protected $moduleId = "tasks";
	protected $className = "\\Thurly\\Tasks\\Internals\\Task\\TagTable";

	protected $primary = array(
		"TASK_ID" => "b_tasks.ID",
		"USER_ID" => "b_user.ID",
		"NAME" => "string",
	);
	protected $predicates = array(
		"TASK_ID" => "b_tasks.ID",
		"USER_ID" => "b_user.ID",
	);
	protected $translation = array(
		"TASK_ID" => "b_tasks.ID",
		"USER_ID" => "b_user.ID",
	);

	/**
	 * Registers event handler for update a record.
	 * Due to primary key update we have to make two operations: delete and add.
	 *
	 * @return void
	 */
	public function initDataManagerUpdate()
	{
		$this->initDataManagerEvent(\Thurly\Main\Entity\DataManager::EVENT_ON_BEFORE_UPDATE, "deleteEventHandler");
		$this->initDataManagerEvent(\Thurly\Main\Entity\DataManager::EVENT_ON_AFTER_UPDATE, "afterUpdateEventHandler");
	}

	/**
	 * Update event handler.
	 *
	 * @param \Thurly\Main\Entity\Event $event D7 event.
	 *
	 * @return void
	 */
	public function afterUpdateEventHandler(\Thurly\Main\Entity\Event $event)
	{
		$entity = $event->getEntity();
		$tableName = $entity->getDBTableName();
		$primaryField = $entity->getPrimary();
		$primaryValue = $event->getParameter("primary");
		$data = $event->getParameter("fields");

		$newPrimary = $primaryValue;
		foreach ($data as $key => $value)
		{
			if (isset($primaryValue[$key]))
				$newPrimary[$key] = $value;
		}

		\Thurly\Replica\Db\Operation::writeInsert($tableName, $primaryField, $newPrimary);
	}
}
