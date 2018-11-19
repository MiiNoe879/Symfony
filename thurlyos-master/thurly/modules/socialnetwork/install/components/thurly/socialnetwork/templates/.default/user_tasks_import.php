<?php
use Thurly\Disk\Desktop;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "tasks"))
{
	$APPLICATION->IncludeComponent('thurly:tasks.import', '', array_merge( array(
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
	), $arResult ), $component);
}