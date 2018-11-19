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
 * @global CMain $APPLICATION
 */

if(count($arResult['APPLICATION_LIST']) > 0)
{
	\Thurly\Rest\HandlerHelper::storeApplicationList($arResult['PLACEMENT'], $arResult['APPLICATION_LIST']);
}