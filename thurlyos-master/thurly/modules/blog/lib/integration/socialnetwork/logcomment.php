<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage blog
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Blog\Integration\Socialnetwork;

use Thurly\Main\Event;
use Thurly\Main\EventResult;
use Thurly\Blog\Item\Comment;
use Thurly\Main\Loader;
use Thurly\Socialnetwork\CommentAux;
use Thurly\Socialnetwork\Item\LogIndex;

class LogComment
{
	const EVENT_ID_COMMENT = 'blog_comment';

	public static function getEventIdList()
	{
		return array(
			self::EVENT_ID_COMMENT
		);
	}

	/**
	 * Return content for LogIndex.
	 *
	 * @param Event $event Event from LogIndex::setIndex().
	 * @return EventResult
	 */
	public static function onIndexGetContent(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'blog'
		);

		$eventId = $event->getParameter('eventId');
		$sourceId = $event->getParameter('sourceId');

		if (!in_array($eventId, self::getEventIdList()))
		{
			return $result;
		}

		$content = "";
		$comment = false;

		if (intval($sourceId) > 0)
		{
			$comment = Comment::getById($sourceId);
		}

		if ($comment)
		{
			$commentFieldList = $comment->getFields();

			if (!($commentAuxProvider = CommentAux\Base::findProvider($commentFieldList)))
			{
				$content .= LogIndex::getUserName($commentFieldList["AUTHOR_ID"])." ";
				$content .= \blogTextParser::killAllTags($commentFieldList["POST_TEXT"]);
			}

			if (!empty($commentFieldList['UF_BLOG_COMMENT_FILE']))
			{
				$fileNameList = LogIndex::getDiskUFFileNameList($commentFieldList['UF_BLOG_COMMENT_FILE']);
				if (!empty($fileNameList))
				{
					$content .= ' '.join(' ', $fileNameList);
				}
			}

			if (!empty($commentFieldList['UF_BLOG_COMM_URL_PRV']))
			{
				$metadata = \Thurly\Main\UrlPreview\UrlMetadataTable::getRowById($commentFieldList['UF_BLOG_COMM_URL_PRV']);
				if (
					$metadata
					&& isset($metadata['TITLE'])
					&& strlen($metadata['TITLE']) > 0
				)
				{
					$content .= ' '.$metadata['TITLE'];
				}
			}
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(
				'content' => $content,
			),
			'blog'
		);

		return $result;
	}
}

