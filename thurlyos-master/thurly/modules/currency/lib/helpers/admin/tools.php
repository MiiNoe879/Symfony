<?php
namespace Thurly\Currency\Helpers\Admin;

use Thurly\Main,
	Thurly\Main\Localization\Loc,
	Thurly\Currency;

Loc::loadMessages(__FILE__);

/**
 * Class Tools
 * Provides various useful methods for admin pages.
 *
 * @package Thurly\Currency\Helpers\Admin
 */
class Tools
{
	/**
	 * Return array with edit url for all currencies.
	 *
	 * @return array
	 */
	public static function getCurrencyLinkList()
	{
		global $APPLICATION;

		$result = array();
		/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
		$currencyLinkTitle = Main\Text\HtmlFilter::encode(
			($APPLICATION->getGroupRight('currency') < 'W')
			? Loc::getMessage('CURRENCY_HELPERS_ADMIN_TOOLS_MESS_CURRENCY_VIEW_TITLE')
			: Loc::getMessage('CURRENCY_HELPERS_ADMIN_TOOLS_MESS_CURRENCY_EDIT_TITLE')
		);

		$currencyList = Currency\CurrencyManager::getCurrencyList();
		foreach ($currencyList as $currency => $title)
		{
			$result[$currency] = '<a href="/thurly/admin/currency_edit.php?ID='.urlencode($currency).'&lang='.LANGUAGE_ID.
				'" title="'.$currencyLinkTitle.'">'.Main\Text\HtmlFilter::encode($title).'</a>';
		}
		unset($currency, $title, $currencyList);

		return $result;
	}
}