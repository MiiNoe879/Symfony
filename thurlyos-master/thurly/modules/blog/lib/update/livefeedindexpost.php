<?
namespace Thurly\Blog\Update;

use \Thurly\Main\Update\Stepper;
use \Thurly\Main\Localization\Loc;
use \Thurly\Blog\Integration;
use \Thurly\Socialnetwork\Item\LogIndex;
use \Thurly\Socialnetwork\LogTable;
use \Thurly\Socialnetwork\LogIndexTable;
use \Thurly\Main\Config\Option;
use \Thurly\Main\Loader;

Loc::loadMessages(__FILE__);

final class LivefeedIndexPost extends Stepper
{
	protected static $moduleId = "blog";

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule("blog")
			&& Loader::includeModule("socialnetwork")
			&& Option::get('blog', 'needLivefeedIndexPost', 'Y') == 'Y'
		))
		{
			return false;
		}

		$return = false;

		$params = Option::get("blog", "livefeedindexpost", "");
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

		if ($params["count"] > 0)
		{
			$result["title"] = Loc::getMessage("FUPD_LF_BLOG_POST_INDEX_TITLE");
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

			$found = false;
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
				Option::set("blog", "livefeedindexpost", serialize($params));
				$return = true;
			}

			$result["progress"] = intval($params["number"] * 100/ $params["count"]);
			$result["steps"] = $params["number"];

			if ($found === false)
			{
				Option::delete("blog", array("name" => "livefeedindexpost"));
				Option::set('blog', 'needLivefeedIndexPost', 'N');
			}
		}
		return $return;
	}
}
?>