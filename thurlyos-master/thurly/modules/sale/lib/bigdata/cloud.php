<?php

namespace Thurly\Sale\Bigdata;

use Thurly\Main\Analytics\Counter;

class Cloud
{
	static protected $url = 'https://analytics.thurly.info/crecoms/v1_0/recoms.php';

	public static function getPersonalRecommendation($userId, $count = 10)
	{
		$params = array(
			'aid' => Counter::getAccountId(),
			'op' => 'recommend',
			'local_uid' => $userId,
			'count' => $count+10
		);

		$result = static::makeQuery($params);

		return $result;
	}

	public static function getFollowUpProducts($productIds)
	{
		$params = array(
			'aid' => Counter::getAccountId(),
			'op' => 'postcross',
			'eids' => join(',', $productIds),
			'count' => 20
		);

		$result = static::makeQuery($params);

		return $result;
	}

	public static function getPotentialConsumers($productId)
	{
		$params = array(
			'aid' => Counter::getAccountId(),
			'op' => 'consumers',
			'eid' => $productId
		);

		$result = static::makeQuery($params);

		return $result;
	}

	protected static function makeQuery($params)
	{
		// aid is always required
		$params['aid'] = \Thurly\Main\Analytics\Counter::getAccountId();

		// send http query
		$http = new \Thurly\Main\Web\HttpClient();
		$http->setHeader("User-Agent", "X-Thurly-Sale");

		$httpResult = $http->get(static::$url.'?'.http_build_query($params));

		if ($http->getStatus() == 200)
		{
			return json_decode($httpResult, true);
		}
	}
}