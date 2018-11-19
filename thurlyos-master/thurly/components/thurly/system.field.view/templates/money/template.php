<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(\Thurly\Main\Loader::includeModule('currency'))
{
	echo \Thurly\Currency\UserField\Money::GetPublicView($arParams['arUserField']);
}