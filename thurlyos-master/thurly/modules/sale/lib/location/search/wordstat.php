<?php
/**
 * Thurly Framework
 * @package Thurly\Sale\Location
 * @subpackage sale
 * @copyright 2001-2014 Thurly
 */
namespace Thurly\Sale\Location\Search;

use Thurly\Main;
use Thurly\Main\DB;
use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Location;

Loc::loadMessages(__FILE__);

final class WordStatTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_loc_word_stat';
	}

	public static function cleanUp()
	{
		Main\HttpApplication::getConnection()->query('truncate table '.static::getTableName());
	}

	const STEP_SIZE = 100;

	// tmp
	protected static $blackList = array(
		'ÐÀÉÎÍ' => true,
		'ÎÁËÀÑÒÜ' => true,
		'ÓËÈÖÀ' => true,
		'ÒÓÏÈÊ' => true,
		'ÃÅÍÅÐÀËÀ' => true,
		'ÏÅÐÅÓËÎÊ' => true,
		'ÏÎÑ¨ËÎÊ' => true,
		'ÑÅËÎ' => true,
		'ÃÎÐÎÄÑÊÎÃÎ' => true,
		'ÒÈÏÀ' => true,
		'ÃÎÐÎÄÎÊ' => true,
		'ÑÍÒ' => true,
		'ÍÀÑÅË¨ÍÍÛÉ' => true,
		'ÏÓÍÊÒ' => true,
		'ÄÅÐÅÂÍß' => true,
		'ÄÀ×ÍÛÉ' => true,
		'ÄÍÏ' => true,
		'ÄÍÒ' => true,
		'ÏËÎÙÀÄÜ' => true,
		'ÏÐÎÅÇÄ' => true,
		'ÀËËÅß' => true
	);

	public static function parseQuery($query)
	{
		$words = explode(' ', $query);

		$result = array();
		foreach($words as $k => &$word)
		{
			$word = ToUpper(trim($word));

			if(strlen($word) < 2 || isset(static::$blackList[$word]))
				continue;

			$result[] = $word;
		}

		$result = array_unique($result);

		//natsort($result);

		return $result;
	}

	public static function reInitData()
	{
		static::cleanUp();

		$totalCnt = 0;
		$offset = 0;
		$stat = array();

		while(true)
		{
			$res = Location\Name\LocationTable::getList(array(
				'select' => array(
					'NAME',
					'LOCATION_ID',
					'TID' => 'LOCATION.TYPE_ID'
				),
				'filter' => array(
					'=LOCATION.TYPE.CODE' => array('CITY', 'VILLAGE', 'STREET'),
					'=LANGUAGE_ID' => 'ru'
				),
				'limit' => self::STEP_SIZE,
				'offset' => $offset
			));

			$cnt = 0;
			while($item = $res->fetch())
			{
				if(strlen($item['NAME']))
				{
					$words = static::parseQuery($item['NAME']);

					foreach($words as $k => &$word)
					{
						try
						{

							static::add(array(
								'WORD' => $word,
								'TYPE_ID' => $item['TID'],
								'LOCATION_ID' => $item['LOCATION_ID']
							));

						}
						catch(\Thurly\Main\DB\SqlQueryException $e)
						{
							// duplicate or smth
						}
					}

					$stat['W_'.count($words)] += 1;

					//_print_r($words);
				}

				$cnt++;
				$totalCnt++;
			}

			if(!$cnt)
				break;

			$offset += self::STEP_SIZE;
		}

		_print_r('Total: '.$totalCnt);
		_print_r($stat);
	}

	public static function getMap()
	{
		return array(

			'WORD' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'TYPE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'LOCATION_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
		);
	}
}

