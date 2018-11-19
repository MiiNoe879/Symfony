<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/telephony/.left.menu_ext.php");

$licensePrefix = "";
if (CModule::IncludeModule("thurlyos"))
{
	$licensePrefix = CThurlyOS::getLicensePrefix();
}
if (CModule::IncludeModule('voximplant') && \Thurly\Voximplant\Security\Helper::isMainMenuEnabled() && $licensePrefix != "by")
{
	if(\Thurly\Voximplant\Security\Helper::isLinesMenuEnabled())
	{
		$aMenuLinks[] = Array(
			GetMessage("MENU_TELEPHONY_CONNECT"),
			"/telephony/lines.php",
			Array("/telephony/edit.php"),
			Array("menu_item_id"=>"menu_telephony_lines"),
			""
		);
	}
	if(\Thurly\Voximplant\Security\Helper::isBalanceMenuEnabled())
	{
		$aMenuLinks[] = Array(
			GetMessage("MENU_TELEPHONY_BALANCE"),
			"/telephony/",
			Array("/telephony/detail.php"),
			Array("menu_item_id"=>"menu_telephony_balance"),
			""
		);
	}
	if(\Thurly\Voximplant\Security\Helper::isUsersMenuEnabled())
	{
		$aMenuLinks[] = Array(
			GetMessage("MENU_TELEPHONY_USERS"),
			"/telephony/users.php",
			Array(),
			Array("menu_item_id"=>"menu_telephony_users"),
			""
		);
	}
	if(\Thurly\Voximplant\Security\Helper::isSettingsMenuEnabled())
	{
		$aMenuLinks[] = Array(
			GetMessage("MENU_TELEPHONY_GROUPS"),
			"/telephony/groups.php",
			Array("/telephony/editgroup.php"),
			Array("menu_item_id"=>"menu_telephony_groups"),
			""
		);
	}
	$aMenuLinks[] = Array(
		GetMessage("MENU_TELEPHONY_PHONES"),
		"/telephony/phones.php",
		Array(),
		Array("menu_item_id"=>"menu_telephony_phones"),
		""
	);
	if(\Thurly\Voximplant\Security\Helper::isSettingsMenuEnabled())
	{
		$aMenuLinks[] = Array(
			GetMessage("MENU_TELEPHONY_PERMISSIONS"),
			"/telephony/permissions.php",
			Array("/telephony/editrole.php"),
			Array("menu_item_id"=>"menu_telephony_permissions"),
			""
		);
		$aMenuLinks[] = Array(
			GetMessage("MENU_TELEPHONY_IVR"),
			"/telephony/ivr.php",
			Array("/telephony/editivr.php"),
			Array("menu_item_id"=>"menu_telephony_ivr"),
			""
		);
		$aMenuLinks[] = Array(
			GetMessage("MENU_TELEPHONY"),
			"/telephony/configs.php",
			Array(),
			Array("menu_item_id"=>"menu_telephony_configs"),
			""
		);
	}
}
?>