<?php
namespace Thurly\Forum\Replica;

class Bind
{
	/** @var \Thurly\Forum\Replica\TopicHandler */
	protected static $topicHandler = null;
	/** @var \Thurly\Forum\Replica\MessageHandler */
	protected static $messageHandler = null;

	/**
	 * Initializes replication process on forum side.
	 *
	 * @return void
	 */
	public function start()
	{
		$eventManager = \Thurly\Main\EventManager::getInstance();

		\Thurly\Replica\Client\HandlersManager::register(new ForumMessageAttachmentHandler);

		self::$topicHandler = new TopicHandler;
		\Thurly\Replica\Client\HandlersManager::register(self::$topicHandler);
		$eventManager->addEventHandler("forum", "onAfterTopicAdd", array(self::$topicHandler, "onAfterTopicAdd"));
		$eventManager->addEventHandler("forum", "onAfterTopicUpdate", array(self::$topicHandler, "onAfterTopicUpdate"));
		$eventManager->addEventHandler("forum", "onAfterTopicDelete", array(self::$topicHandler, "onAfterTopicDelete"));

		self::$messageHandler = new MessageHandler;
		\Thurly\Replica\Client\HandlersManager::register(self::$messageHandler);
		$eventManager->addEventHandler("forum", "onAfterMessageAdd", array(self::$messageHandler, "onAfterMessageAdd"));
		$eventManager->addEventHandler("forum", "onBeforeMessageUpdate", array(self::$messageHandler, "onBeforeMessageUpdate"));
		$eventManager->addEventHandler("forum", "onAfterMessageUpdate", array(self::$messageHandler, "onAfterMessageUpdate"));
		$eventManager->addEventHandler("forum", "onBeforeMessageDelete", array(self::$messageHandler, "onBeforeMessageDelete"));
		$eventManager->addEventHandler("forum", "onAfterMessageDelete", array(self::$messageHandler, "onAfterMessageDelete"));

		\Thurly\Replica\Client\HandlersManager::register(new MessageRatingVoteHandler);
	}
}
