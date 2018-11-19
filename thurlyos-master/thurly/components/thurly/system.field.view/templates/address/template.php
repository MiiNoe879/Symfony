<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(\Thurly\Main\Loader::includeModule('fileman'))
{
	echo \Thurly\Fileman\UserField\Address::GetPublicView($arParams['arUserField'], array('printable' => $arParams['printable']));
}