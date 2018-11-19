<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult['TEMPLATE_DATA']['RESTRICTED'] = !\Thurly\Tasks\Util\Restriction::checkCanCreateDependence();