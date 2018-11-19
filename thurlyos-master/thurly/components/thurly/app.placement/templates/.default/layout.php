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
 * @var CThurlyComponentTemplate $this
 * @global CMain $APPLICATION
 */

$c = \Thurly\Main\Text\Converter::getHtmlConverter();

$appId = 0;
$appTitle = '';

foreach($arResult['APPLICATION_LIST'] as $app)
{
	if($app['ID'] == $arResult['APPLICATION_CURRENT'])
	{
		$appId = $app['APP_ID'];
		break;
	}
}

if($appId > 0)
{
	$APPLICATION->IncludeComponent(
		'thurly:app.layout',
		'',
		array(
			'ID' => $appId,
			'PLACEMENT' => $arResult['PLACEMENT'],
			'PLACEMENT_ID' => $arResult['APPLICATION_CURRENT'],
			"PLACEMENT_OPTIONS" => $arResult['PLACEMENT_OPTIONS'],
			'PARAM' => $arParams['PARAM']
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
}

