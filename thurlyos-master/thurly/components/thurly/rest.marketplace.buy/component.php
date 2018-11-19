<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if(!\Thurly\Main\Loader::includeModule('rest'))
{
	return;
}

$arResult['ADMIN'] = \CRestUtil::isAdmin();

if(!$arResult['ADMIN'] || !\Thurly\Rest\OAuthService::getEngine()->isRegistered())
{
	return;
}

$arCodes = array();

\Thurly\Rest\AppTable::updateAppStatusInfo();

$dbApps = \Thurly\Rest\AppTable::getList(array(
	'filter' => array(
		'!=STATUS' => \Thurly\Rest\AppTable::STATUS_LOCAL,
	),
	'select' => array(
		'*', 'MENU_NAME' => 'LANG.MENU_NAME',
	)
));
$arAppsFromDB = array();
while ($arApp = $dbApps->Fetch())
{
	$arCodes[] = $arApp["CODE"];
	$arApp['APP_STATUS'] = \Thurly\Rest\AppTable::getAppStatusInfo($arApp, str_replace(
		array("#app#"),
		array(urlencode($arApp['CODE'])),
		$arParams['DETAIL_URL_TPL']
	));
	if(isset($arApp['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#']))
	{
		$arApp['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#']++;
		$arApp['APP_STATUS']['MESSAGE_REPLACE']['#DAYS#'] = FormatDate("ddiff", time(), time() + 24 * 60 * 60 * $arApp['APP_STATUS']['MESSAGE_REPLACE']["#DAYS#"]);
	}

	$arAppsFromDB[] = $arApp;
}

if (!empty($arCodes))
{
	$arAppsBuy = \Thurly\Rest\Marketplace\Client::getBuy($arCodes);
	if($arAppsBuy)
	{
		$arAppsBuy = $arAppsBuy["ITEMS"];
	}
	$arResult["ITEMS"] = $arAppsBuy;

	foreach($arResult['ITEMS'] as $key => $item)
	{
		if(is_array($item["PRICE"]) && !empty($item["PRICE"]) && $arResult['ADMIN'])
		{
			$item['BUY'] = array();
			foreach($item["PRICE"] as $num => $price)
			{
				if($num > 1)
				{
					$item['BUY'][] = array(
						'LINK' => \Thurly\Rest\Marketplace\Client::getBuyLink($num, $item['CODE']),
						'TEXT' => GetMessage("RMP_APP_TIME_LIMIT_".$num).' - '.$price
					);
				}
			}

			$arResult['ITEMS'][$key] = $item;
		}
	}

}

$arResult["ITEMS_DB"] = $arAppsFromDB;

CJSCore::Init(array('marketplace'));

$APPLICATION->SetTitle(GetMessage("MARKETPLACE_BUYS"));

$this->IncludeComponentTemplate();
?>