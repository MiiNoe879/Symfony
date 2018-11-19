<?php
/**
* Thurly Framework
* @package thurly
* @subpackage socialnetwork
* @copyright 2001-2017 Thurly
*/
namespace Thurly\Socialnetwork\Integration\Mobile;

use Thurly\Socialnetwork\Livefeed\Provider;
use Thurly\Main\Event;
use Thurly\Main\EventResult;

class LogEntry
{
	public static function onSetContentView(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$logEventFields = $event->getParameter('logEventFields');

		if (!is_array($logEventFields))
		{
			return $result;
		}

		if ($contentId = Provider::getContentId($logEventFields))
		{
			if ($liveFeedEntity = Provider::init(array(
				'ENTITY_TYPE' => $contentId['ENTITY_TYPE'],
				'ENTITY_ID' => $contentId['ENTITY_ID'],
				'LOG_ID' => $logEventFields["ID"]
			)))
			{
				if ($liveFeedEntity->setContentView())
				{
					$result = new EventResult(
						EventResult::SUCCESS,
						array(),
						'socialnetwork'
					);
				}
			}
		}

		return $result;
	}

	public static function onGetContentId(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$logEventFields = $event->getParameter('logEventFields');

		if (!is_array($logEventFields))
		{
			return $result;
		}

		if ($contentId = Provider::getContentId($logEventFields))
		{
			$result = new EventResult(
				EventResult::SUCCESS,
				array(
					'contentId' => $contentId
				),
				'socialnetwork'
			);
		}

		return $result;
	}
}
?>