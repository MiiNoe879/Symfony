<?php
/**
* Thurly Framework
* @package thurly
* @subpackage socialnetwork
* @copyright 2001-2017 Thurly
*/
namespace Thurly\Socialnetwork\Integration\Tasks;

use Thurly\Socialnetwork\Livefeed\Provider;
use Thurly\Main\Event;
use Thurly\Main\EventResult;

class Task
{
	public static function onTaskUpdateViewed(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$taskId = $event->getParameter('taskId');
		$userId = $event->getParameter('userId');

		if (
			intval($taskId) <= 0
			|| intval($userId) <= 0
		)
		{
			return $result;
		}

		if ($liveFeedEntity = Provider::init(array(
			'ENTITY_TYPE' => Provider::DATA_ENTITY_TYPE_TASKS_TASK,
			'ENTITY_ID' => $taskId
		)))
		{
			$liveFeedEntity->setContentView(array(
				"userId" => $userId
			));
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(),
			'socialnetwork'
		);

		return $result;
	}
}
?>