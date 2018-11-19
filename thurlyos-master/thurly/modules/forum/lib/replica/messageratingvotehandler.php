<?php
namespace Thurly\Forum\Replica;

class MessageRatingVoteHandler extends \Thurly\Replica\Client\RatingVoteHandler
{
	protected $entityTypeId = "FORUM_POST";
	protected $entityIdTranslation = "b_forum_message.ID";
}
