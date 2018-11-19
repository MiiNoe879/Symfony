<?php

namespace Thurly\Tasks\Integration\Socialnetwork;

use Thurly\Main\Loader;
use Thurly\Socialnetwork\Livefeed\TasksTask;

/**
 * Class for content view event handlers
 *
 * Class ContentViewHandler
 * @package Thurly\Tasks\Integration\Socialnetwork
 */
final class ContentViewHandler
{
	final static function getContentTypeIdList()
	{
		$result = array();
		if (Loader::includeModule('socialnetwork'))
		{
			$result[] = TasksTask::CONTENT_TYPE_ID;
		}
		return $result;
	}

	/**
	 * Handles content view event, marking IM notifications as read
	 *
	 * @param \Thurly\Main\Event $event Event.
	 * @return int|false
	 */
	public static function onContentViewed(\Thurly\Main\Event $event)
	{
		$userId = intval($event->getParameter('userId'));
		$contentTypeId = $event->getParameter('typeId');
		$contentEntityId = intval($event->getParameter('entityId'));

		if (
			$userId <= 0
			|| !in_array($contentTypeId, self::getContentTypeIdList())
			|| $contentEntityId <= 0
			|| !Loader::includeModule('im')
		)
		{
			return false;
		}

		$subTagList = array();

		if (in_array($contentTypeId, self::getContentTypeIdList()))
		{
			$subTagList[] = "TASKS|TASK|".$contentEntityId.'|'.$userId.'|TASK_ADD';
		}

		if (!empty($subTagList))
		{
			$CIMNotify = new \CIMNotify();
			$CIMNotify->MarkNotifyReadBySubTag($subTagList);
		}

		return true;
	}
}