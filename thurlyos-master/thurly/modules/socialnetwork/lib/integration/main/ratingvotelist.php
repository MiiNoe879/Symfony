<?php
/**
* Thurly Framework
* @package thurly
* @subpackage socialnetwork
* @copyright 2001-2017 Thurly
*/
namespace Thurly\Socialnetwork\Integration\Main;

use Thurly\Socialnetwork\Livefeed\Provider;
use Thurly\Main\Event;
use Thurly\Main\EventResult;

class RatingVoteList
{
	public static function onViewed(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$entityTypeId = $event->getParameter('entityTypeId');
		$entityId = $event->getParameter('entityId');
		$userId = $event->getParameter('userId');

		if (
			empty($entityTypeId)
			|| intval($entityId) <= 0
			|| intval($userId) <= 0
		)
		{
			return $result;
		}

		if ($liveFeedEntity = Provider::init(array(
			'ENTITY_TYPE' => Provider::DATA_ENTITY_TYPE_RATING_LIST,
			'ENTITY_ID' => $entityTypeId.'|'.$entityId
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