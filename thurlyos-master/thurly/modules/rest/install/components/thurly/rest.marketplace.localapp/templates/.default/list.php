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
 */

$APPLICATION->IncludeComponent(
	"thurly:rest.marketplace.localapp.toolbar",
	".default",
	array(
		"COMPONENT_PAGE" => $arParams["COMPONENT_PAGE"],
		"ADD_URL" => $arParams["ADD_URL"],
		"LIST_URL" => $arParams["LIST_URL"],
	),
	$component
);

$APPLICATION->IncludeComponent(
	"thurly:rest.marketplace.localapp.list",
	".default",
	array(
		"ADD_URL" => $arParams["ADD_URL"],
		"EDIT_URL_TPL" => $arParams["EDIT_URL_TPL"],
		"APPLICATION_URL" => $arParams["APPLICATION_URL"],
	),
	$component
);
