<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage vote
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Vote;

use \Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class DBResult extends \CDBResult
{
	/**
	 * @return array|bool|false|mixed|null
	 */
	function fetch()
	{
		if ($res = parent::fetch())
		{
			$prefix = null;
			foreach ($res as $k => $v)
			{
				if (strpos($k, "LAMP") !== false)
				{
					$prefix = substr($k, 0, strpos($k, "LAMP"));
					break;
				}
			}
			if ($prefix !== null && $res[$prefix."LAMP"] == "yellow" && !empty($res[$prefix."CHANNEL_ID"]))
			{
				$res[$prefix."LAMP"] = ($res[$prefix."ID"] == \CVote::getActiveVoteId($res[$prefix."CHANNEL_ID"]) ? "green" : "red");
			}
		}
		return $res;
	}
}