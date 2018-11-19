<?php
/**
 * This class contains ui helper for task/tag entity
 *
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Tasks\UI\Task;

use Thurly\Tasks\Util\Type;

final class Tag
{
	public static function formatTagString($tags)
	{
		if(Type::isIterable($tags) && count($tags))
		{
			$formatted = array();

			foreach ($tags as $tag)
			{
				if(Type::isIterable($tags) && count($tag['NAME']))
				{
					$formatted[] = (string) $tag['NAME'];
				}
				elseif($tag !== '')
				{
					$formatted[] = (string) $tag;
				}
			}

			return implode(', ', $formatted);
		}

		return '';
	}
}