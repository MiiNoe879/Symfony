<?php
namespace Thurly\Crm\Search;

use Thurly\Main;

class SearchEnvironment
{
	public static function prepareToken($str)
	{
		return str_rot13($str);
	}

	public static function prepareEntityFilter($entityTypeID, array $params)
	{
		$builder = SearchContentBuilderFactory::create($entityTypeID);
		return $builder->prepareEntityFilter($params);
	}

	public static function convertEntityFilterValues($entityTypeID, array &$fields)
	{
		$builder = SearchContentBuilderFactory::create($entityTypeID);
		$builder->convertEntityFilterValues($fields);
	}

	public static function isFullTextSearchEnabled($entityTypeID)
	{
		$builder = SearchContentBuilderFactory::create($entityTypeID);
		return $builder->isFullTextSearchEnabled();
	}

	public static function prepareSearchContent($str)
	{
		$len = strlen($str);
		if($len < 3 || $len > 15 || preg_match('/^[0-9\(\)\+\-\#\*\s]+$/', $str) !== 1)
		{
			return $str;
		}
		return \NormalizePhone($str, 3);
	}
}