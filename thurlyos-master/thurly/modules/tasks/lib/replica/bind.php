<?php
namespace Thurly\Tasks\Replica;

class Bind
{
	/** @var \Thurly\Tasks\Replica\TaskHandler */
	protected static $taskHandler = null;

	/**
	 * Initializes replication process on tasks side.
	 *
	 * @return void
	 */
	public function start()
	{
		self::$taskHandler = new TaskHandler();
		\Thurly\Replica\Client\HandlersManager::register(self::$taskHandler);
		\Thurly\Replica\Client\HandlersManager::register(new TaskMemberHandler);
		\Thurly\Replica\Client\HandlersManager::register(new TaskTagHandler);
		\Thurly\Replica\Client\HandlersManager::register(new TaskLogHandler);
		\Thurly\Replica\Client\HandlersManager::register(new TaskElapsedTimeHandler);
		\Thurly\Replica\Client\HandlersManager::register(new TaskViewedHandler);
		\Thurly\Replica\Client\HandlersManager::register(new TaskReminderHandler);
		\Thurly\Replica\Client\HandlersManager::register(new TaskChecklistItemHandler);
		\Thurly\Replica\Client\HandlersManager::register(new TaskRatingVoteHandler);

		$eventManager = \Thurly\Main\EventManager::getInstance();
		$eventManager->addEventHandler("tasks", "OnTaskAdd", array(self::$taskHandler, "onTaskAdd"));
		$eventManager->addEventHandler("tasks", "OnBeforeTaskUpdate", array(self::$taskHandler, "onBeforeTaskUpdate"));
		$eventManager->addEventHandler("tasks", "OnTaskUpdate", array(self::$taskHandler, "onTaskUpdate"));
		$eventManager->addEventHandler("tasks", "OnTaskDelete", array(self::$taskHandler, "onTaskDelete"));
		$eventManager->addEventHandler("tasks", "OnBeforeTaskZombieDelete", array(self::$taskHandler, "onBeforeTaskZombieDelete"));
		$eventManager->addEventHandler("tasks", "OnTaskZombieDelete", array(self::$taskHandler, "onTaskZombieDelete"));
	}
}
