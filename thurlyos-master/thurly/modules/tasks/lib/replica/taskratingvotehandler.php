<?php
namespace Thurly\Tasks\Replica;

class TaskRatingVoteHandler extends \Thurly\Replica\Client\RatingVoteHandler
{
	protected $entityTypeId = "TASK";
	protected $entityIdTranslation = "b_tasks.ID";
}
