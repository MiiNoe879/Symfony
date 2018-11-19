<?
namespace Thurly\Tasks\Update;

use \Thurly\Main\Update\Stepper;
use \Thurly\Main\Localization\Loc;
use \Thurly\Tasks\Integration;
use \Thurly\Socialnetwork\Item\LogIndex;
use \Thurly\Socialnetwork\LogTable;
use \Thurly\Socialnetwork\LogIndexTable;
use \Thurly\Main\Config\Option;
use \Thurly\Main\Loader;

Loc::loadMessages(__FILE__);

final class LivefeedIndexTask extends Stepper
{
	protected static $moduleId = "tasks";

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule("tasks")
			&& Loader::includeModule("socialnetwork")
			&& Option::get('tasks', 'needLivefeedIndex', 'Y') == 'Y'
		))
		{
			return false;
		}

		$return = false;

		$params = Option::get("tasks", "livefeedindextask", "");
		$params = ($params !== "" ? @unserialize($params) : array());
		$params = (is_array($params) ? $params : array());
		if (empty($params))
		{
			$params = array(
				"lastId" => 0,
				"number" => 0,
				"count" => LogTable::getCount(
					array(
						'@EVENT_ID' => Integration\Socialnetwork\Log::getEventIdList(),
						'!SOURCE_ID' => false
					)
				)
			);
		}

		$found = false;

		if ($params["count"] > 0)
		{
			$result["title"] = Loc::getMessage("FUPD_LF_TASKS_TASK_INDEX_TITLE");
			$result["progress"] = 1;
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$res = LogTable::getList(array(
				'order' => array('ID' => 'ASC'),
				'filter' => array(
					'>ID' => $params["lastId"],
					'@EVENT_ID' => Integration\Socialnetwork\Log::getEventIdList(),
					'!SOURCE_ID' => false
				),
				'select' => array('ID', 'EVENT_ID', 'SOURCE_ID'),
				'offset' => 0,
				'limit' => 100
			));

			while ($record = $res->fetch())
			{
				LogIndex::setIndex(array(
					'itemType' => LogIndexTable::ITEM_TYPE_LOG,
					'itemId' => $record['ID'],
					'fields' => $record
				));

				$params["lastId"] = $record['ID'];
				$params["number"]++;
				$found = true;
			}

			if ($found)
			{
				Option::set("tasks", "livefeedindextask", serialize($params));
				$return = true;
			}

			$result["progress"] = intval($params["number"] * 100/ $params["count"]);
			$result["steps"] = $params["number"];
		}

		if ($found === false)
		{
			Option::delete("tasks", array("name" => "livefeedindextask"));
			Option::set('tasks', 'needLivefeedIndex', 'N');
		}

		return $return;
	}
}
?>