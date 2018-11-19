<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$result = $GLOBALS["APPLICATION"]->IncludeComponent(
	'thurly:crm.control_panel',
	'',
	array(
		"MENU_MODE" => "Y"
	)
);

$aMenuLinks = $result["ITEMS"];