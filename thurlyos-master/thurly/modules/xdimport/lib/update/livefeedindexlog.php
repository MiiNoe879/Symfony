<?
namespace Thurly\XDImport\Update;

use \Thurly\Main\Update\Stepper;
use \Thurly\Main\Localization\Loc;
use \Thurly\XDImport\Integration;
use \Thurly\Socialnetwork\Item\LogIndex;
use \Thurly\Socialnetwork\LogTable;
use \Thurly\Socialnetwork\LogIndexTable;
use \Thurly\Main\Config\Option;
use \Thurly\Main\Loader;

Loc::loadMessages(__FILE__);

final class LivefeedIndexLog extends Stepper
{
	protected static $moduleId = "xdimport";

	public function execute(array &$result)
	{
		if (!(
			Loader::includeModule("xdimport")
			&& Loader::includeModule("socialnetwork")
			&& Option::get('xdimport', 'needLivefeedIndexLog', 'Y') == 'Y'
		))
		{
			return false;
		}

		$return = false;

		$params = Option::get("xdimport", "livefeedindexlog", "");
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
					)
				)
			);
		}

		if ($params["count"] > 0)
		{
			$result["title"] = Loc::getMessage("FUPD_LF_XDIMPORT_LOG_INDEX_TITLE");
			$result["progress"] = 1;
			$result["steps"] = "";
			$result["count"] = $params["count"];

			$res = LogTable::getList(array(
				'order' => array('ID' => 'ASC'),
				'filter' => array(
					'>ID' => $params["lastId"],
					'@EVENT_ID' => Integration\Socialnetwork\Log::getEventIdList(),
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
				Option::set("xdimport", "livefeedindexlog", serialize($params));
				$return = true;
			}

			$result["progress"] = intval($params["number"] * 100/ $params["count"]);
			$result["steps"] = $params["number"];

			if ($found === false)
			{
				Option::delete("xdimport", array("name" => "livefeedindexlog"));
				Option::set('xdimport', 'needLivefeedIndexLog', 'N');
			}
		}
		return $return;
	}
}
?>