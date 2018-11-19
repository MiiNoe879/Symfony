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

if(!CModule::IncludeModule("rest"))
{
	return;
}

$arResult["ITEMS"] = array();

$arCodes = array();
$arStatuses = array();
$dbApps = \Thurly\Rest\AppTable::getList(array(
	'filter' => array(
		"=ACTIVE" => \Thurly\Rest\AppTable::ACTIVE,
		"!=STATUS" => \Thurly\Rest\AppTable::STATUS_LOCAL,
	),
	'select' => array(
		'*', 'MENU_NAME' => 'LANG.MENU_NAME',
	)
));
while ($arApp = $dbApps->Fetch())
{
	$arCodes[$arApp["CODE"]] = $arApp["VERSION"];
	$arStatuses[$arApp["CODE"]] = $arApp["STATUS"];
}

if (!empty($arCodes))
{
	$curNumUpdates = \Thurly\Rest\Marketplace\Client::getAvailableUpdateNum();

	$arUpdates = \Thurly\Rest\Marketplace\Client::getUpdates($arCodes);
	if(is_array($arUpdates) && !empty($arUpdates))
	{
		$arUpdates = $arUpdates["ITEMS"];

		$newNumUpdates = \Thurly\Rest\Marketplace\Client::getAvailableUpdateNum();
		if ($curNumUpdates != $newNumUpdates)
		{
			$arResult["NEW_NUM_UPDATES"] = $newNumUpdates;
		}

		foreach ($arUpdates as $key => $arApp)
		{
			$arUpdates[$key]["STATUS"] = $arStatuses[$arApp["CODE"]];
		}
	}

	$arResult["ITEMS"] = $arUpdates;
	$arResult["ITEMS_CODES"] = $arCodes;
}

$arResult["ADMIN"] = \CRestUtil::isAdmin();

$APPLICATION->SetTitle(GetMessage("MARKETPLACE_UPDATES"));

\CJSCore::Init(array('marketplace'));

$this->IncludeComponentTemplate();
?>