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
 * @var CThurlyComponent $component
 * @var CThurlyComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$APPLICATION->IncludeComponent(
	'thurly:rest.hook.toolbar',
	'',
	array(
		"COMPONENT_PAGE" => $arParams["COMPONENT_PAGE"],
		"LIST_URL" => $arParams["LIST_URL"],
		"EVENT_ADD_URL" => $arParams["EVENT_ADD_URL"],
		"EVENT_EDIT_URL_TPL" => $arParams["EVENT_EDIT_URL_TPL"],
		"AP_ADD_URL" => $arParams["AP_ADD_URL"],
		"AP_EDIT_URL_TPL" => $arParams["AP_EDIT_URL_TPL"],
	),
	$component
);

$APPLICATION->IncludeComponent(
	'thurly:rest.hook.event.list',
	'',
	array(
		'EDIT_URL_TPL' => $arParams['EVENT_EDIT_URL_TPL'],
		'PAGE_SIZE' => 10,
	),
	$component
);
