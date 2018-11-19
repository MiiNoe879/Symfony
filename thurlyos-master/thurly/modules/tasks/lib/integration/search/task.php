<?
/**
 * Class implements all further interactions with "search" module considering "task item" entity
 *
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */

namespace Thurly\Tasks\Integration\Search;

final class Task extends \Thurly\Tasks\Integration
{
	const MODULE_NAME = 'search';

	public static function index($task)
	{
		if(!static::includeModule())
		{
			return;
		}

		$tags = '';
		if(is_array($task['SE_TAG']))
		{
			$tags = implode(",", $task['SE_TAG']);
		}
		elseif(is_string($task['SE_TAG']))
		{
			$tags = $task['SE_TAG'];
		}
		elseif(\Thurly\Tasks\Item\Task\Collection\Tag::isA($task['SE_TAG']))
		{
			/** @var \Thurly\Tasks\Item\Task\Collection\Tag $tagCollection */
			$tagCollection = $task['SE_TAG'];
			$tags = $tagCollection->joinNames(',');
		}

		if ($task["GROUP_ID"] > 0)
		{
			// todo: use special socnet helper to get path here
			$path = str_replace("#group_id#", $task["GROUP_ID"], \COption::GetOptionString("tasks", "paths_task_group_entry", "/workgroups/group/#group_id#/tasks/task/view/#task_id#/", $task["SITE_ID"]));
		}
		else
		{
			// todo: use special socnet helper to get path here
			$path = str_replace("#user_id#", $task["RESPONSIBLE_ID"], \COption::GetOptionString("tasks", "paths_task_user_entry", "/company/personal/user/#user_id#/tasks/task/view/#task_id#/", $task["SITE_ID"]));
		}
		$path = str_replace("#task_id#", $task["ID"], $path);

		$arSearchIndex = array(
			"LAST_MODIFIED" => $task["CHANGED_DATE"] ? $task["CHANGED_DATE"] : $task["CREATED_DATE"],
			"TITLE" => $task["TITLE"],
			"BODY" => strip_tags($task["DESCRIPTION"]) ? strip_tags($task["DESCRIPTION"]) : $task["TITLE"],
			"TAGS" => $tags,
			"URL" => $path,
			"SITE_ID" => $task["SITE_ID"],
			"PERMISSIONS" => \CTasks::__GetSearchPermissions($task), // todo: move this method here

			// to make ilike feature work
			"ENTITY_TYPE_ID" => "TASK",
			"ENTITY_ID" => $task["ID"]
		);

		$entity_type	= ($task["GROUP_ID"] != 0) ? "G" : "U";
		$entity_name	= ($entity_type == "G") ? "socnet_group" : "socnet_user";
		$entity_id		= ($entity_type == "G") ? $task["GROUP_ID"] : $task["RESPONSIBLE_ID"];
		$feature		= ($entity_type == "G") ? "view": "view_all";

		$arSearchIndex["PARAMS"] = array(
			"feature_id" => "S".$entity_type."_".$entity_id."_tasks_".$feature,
			$entity_name => $entity_id,
		);

		\CSearch::Index("tasks", $task["ID"], $arSearchIndex, true);
	}
}