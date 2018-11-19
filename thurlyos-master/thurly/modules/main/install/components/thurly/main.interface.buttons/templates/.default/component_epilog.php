<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (SITE_TEMPLATE_ID === "thurlyos")
{
	$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
	$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."top-menu-mode");
}