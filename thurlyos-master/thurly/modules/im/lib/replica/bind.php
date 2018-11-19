<?php
namespace Thurly\Im\Replica;

class Bind
{
	/** @var \Thurly\Im\Replica\StatusHandler */
	protected static $statusHandler = null;

	/**
	 * Initializes replication process on im side.
	 *
	 * @return void
	 */
	public function start()
	{
		self::$statusHandler = new StatusHandler();
		\Thurly\Replica\Client\HandlersManager::register(self::$statusHandler);
		\Thurly\Replica\Client\HandlersManager::register(new ChatHandler());
		\Thurly\Replica\Client\HandlersManager::register(new RelationHandler());
		\Thurly\Replica\Client\HandlersManager::register(new MessageHandler());
		\Thurly\Replica\Client\HandlersManager::register(new MessageParamHandler());
		\Thurly\Replica\Client\HandlersManager::register(new StartWritingHandler());

		$eventManager = \Thurly\Main\EventManager::getInstance();

		//$eventManager->addEventHandler("main", "OnUserSetLastActivityDate", array(self::$statusHandler, "OnUserSetLastActivityDate"));
		\Thurly\Replica\Server\Event::registerOperation("im_status_update", array(self::$statusHandler, "handleStatusUpdateOperation"));

		$eventManager->addEventHandler("socialservices", "OnAfterRegisterUserByNetwork", array(self::$statusHandler, "OnStartUserReplication"), false, 200);
		\Thurly\Replica\Server\Event::registerOperation("im_status_bind", array(self::$statusHandler, "handleStatusBindOperation"));

		$eventManager->addEventHandler("im", "OnAfterRecentDelete", array(self::$statusHandler, "OnAfterRecentDelete"));
		\Thurly\Replica\Server\Event::registerOperation("im_status_unbind", array(self::$statusHandler, "handleStatusUnbindOperation"));

		$eventManager->addEventHandler("im", "OnAfterRecentAdd", array(self::$statusHandler, "OnAfterRecentAdd"));
		\Thurly\Replica\Server\Event::registerOperation("im_status_rebind", array(self::$statusHandler, "handleStatusRebindOperation"));
	}

}
