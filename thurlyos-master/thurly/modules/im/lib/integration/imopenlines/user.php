<?php
namespace Thurly\Im\Integration\Imopenlines;

class User
{
	public static function isOperator($userId = null)
	{
		if (!\Thurly\Main\Loader::includeModule('imopenlines'))
		{
			return false;
		}

		$userId = \Thurly\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$list = \Thurly\ImOpenLines\Config::getQueueList($userId);

		return !empty($list);
	}
}