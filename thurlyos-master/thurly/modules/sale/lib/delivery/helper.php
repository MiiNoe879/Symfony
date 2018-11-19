<?php

namespace Thurly\Sale\Delivery;

use Thurly\Main\SystemException;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Internals\Input;
use Thurly\Currency;

Loc::loadMessages(__FILE__);

class Helper
{
	/**
	 * Return currencies list.
	 *
	 * @return array Currencies list.
	 * @throws SystemException
	 * @throws \Thurly\Main\LoaderException
	 */
	public static function getCurrenciesList()
	{
		static $currencies = null;

		if($currencies === null)
		{
			$currencies = array();

			if (!\Thurly\Main\Loader::includeModule('currency'))
				throw new SystemException("Can't include module \"Currency\"!");

			$currencies = Currency\CurrencyManager::getCurrencyList();
		}

		return $currencies;
	}

	/**
	 * Return tree groups.
	 *
	 * @param array $flatGroups				Group list.
	 * @return array
	 */
	protected static function createTreeFromGroups($flatGroups)
	{
		$result = array();

		foreach($flatGroups as $groupId => $groupParams)
		{
			if(intval($groupParams["PARENT_ID"]) <= 0)
			{
				$groupParams["LEVEL"] = 1;
				$groupParams["NAME"] = " . ".$groupParams["NAME"];
				$result[$groupId] = $groupParams;
			}
			else
			{
				$groupParams["LEVEL"] = $result[$groupParams["PARENT_ID"]]["LEVEL"]+1;
				$groupParams["NAME"] = str_repeat(" . ", $groupParams["LEVEL"]+1).$groupParams["NAME"];
				$result[$groupId] = $groupParams;
			}
		}
		return $result;
	}

	/**
	 * Return html for choose group control.
	 *
	 * @param int|string $selectedGroupId			Selected group.
	 * @param string $name							Group name.
	 * @param string $addParams						Additional params for select tag.
	 * @param bool $anyGroup						Allowed select any group.
	 * @return string
	 */
	public static function getGroupChooseControl($selectedGroupId, $name, $addParams = "", $anyGroup = false)
	{
		$groups = array();

		$dbRes = \Thurly\Sale\Delivery\Services\Table::getList(array(
			"filter" => array(
				"=CLASS_NAME" => '\Thurly\Sale\Delivery\Services\Group'
			),
			"select" => array(
				"ID", "NAME", "PARENT_ID"
			),
			"order" => array(
				"PARENT_ID" => "ASC",
				"NAME" => "ASC"
			)
		));

		while($group = $dbRes->fetch())
			$groups[$group["ID"]] = $group;

		//$groups = self::createTreeFromGroups($groups);
		$result = '<select name='.$name.' id="sale_delivery_group_choose"'.$addParams.'>';

		if($anyGroup)
			$result .= '<option value="-1"'.($selectedGroupId == "-1" ? ' selected' : '').'>'.Loc::getMessage('SALE_DELIVERY_HELPER_ANY_LEVEL').'</option>';

		$result .= '<option value="0"'.($selectedGroupId == 0 ? ' selected' : '').'>'.Loc::getMessage('SALE_DELIVERY_HELPER_UPPER_LEVELL').'</option>';

		foreach($groups as $groupId => $group)
			$result .= '<option value="'.$groupId.'"'.($selectedGroupId == $groupId ? ' selected' : '').'>'.htmlspecialcharsbx($group["NAME"]).'</option>';

		$result .= '</select>';

		return $result;
	}

	public static function getDefaultSiteId()
	{
		static $result = null;

		if($result === null)
		{
			$res = \Thurly\Main\SiteTable::getList(array(
				'filter' => array('DEF' => 'Y'),
				'select' => array('LID')
			));

			if($item = $res->fetch())
				$result = $item['LID'];
		}

		return $result;
	}

	public static function additionalHandlerCacheClean()
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/sale/handlers/delivery/additional/cache.php");
		\Sale\Handlers\Delivery\Additional\CacheManager::cleanAll();
	}
}