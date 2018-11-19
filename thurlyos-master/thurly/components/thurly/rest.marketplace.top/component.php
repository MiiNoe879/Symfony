<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
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


if (!\Thurly\Main\Loader::includeModule("rest"))
{
	return;
}

$arResult = \Thurly\Rest\Marketplace\Client::getTop($arParams['ACTION']);

$arResult['ITEMS_INSTALLED'] = array();
if(count($arResult['ITEMS']) > 0)
{
	$listAppCode = array();
	foreach($arResult['ITEMS'] as $catagory)
	{
		if(is_array($catagory))
		{
			foreach($catagory as $item)
			{
				$listAppCode[] = $item['CODE'];
			}
		}
	}

	if(count($listAppCode) > 0)
	{
		$dbRes = \Thurly\Rest\AppTable::getList(array(
			'filter' => array(
				'@CODE' => $listAppCode,
				'=ACTIVE' => \Thurly\Rest\AppTable::ACTIVE
			),
			'select' => array('CODE')
		));
		while($installedApp = $dbRes->fetch())
		{
			$arResult['ITEMS_INSTALLED'][] = $installedApp['CODE'];
		}
	}
}

$this->IncludeComponentTemplate();
