<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/settings/configs/.left.menu_ext.php");

if ($GLOBALS['USER']->CanDoOperation('thurlyos_config'))
{
	$aMenuLinks[] = Array(
		GetMessage("MENU_CONFIGS"),
		"/settings/configs/",
		Array(),
		Array("menu_item_id"=>"menu_configs"),
		""
	);

	if (
		!IsModuleInstalled("thurlyos")
		&& \Thurly\Main\Loader::includeModule("scale")
		&& \Thurly\Scale\Helper::isScaleCanBeUsed()
		&& $_SERVER['THURLY_ENV_TYPE'] == "crm"
	)
	{
		$aMenuLinks[] = Array(
			GetMessage("MENU_VM"),
			"/settings/configs/vm.php",
			Array(),
			Array("menu_item_id"=>"menu_configs_vm"),
			""
		);
	}

	$aMenuLinks[] = Array(
		GetMessage("MENU_MAIL_MANAGE"),
		"/company/personal/mail/manage/",
		Array(),
		Array("menu_item_id"=>"menu_mail_manage"),
		""
	);
	if (
		\Thurly\Main\Loader::includeModule("thurlyos")
		&& (
			\CThurlyOS::IsLicensePaid()
			|| \CThurlyOS::IsNfrLicense()
			|| \CThurlyOS::IsDemoLicense()
		)
		|| !IsModuleInstalled("thurlyos")
	)
	{
		$aMenuLinks[] = Array(
			GetMessage("MENU_EVENT_LOG"),
			"/settings/configs/event_log.php",
			Array(),
			Array("menu_item_id"=>"menu_event_log"),
			""
		);
	}
}
?>