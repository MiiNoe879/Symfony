<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/settings/configs/event_log.php");
$APPLICATION->SetTitle(GetMessage("EVENT_LOG_TITLE"));

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
	$APPLICATION->IncludeComponent(
		"thurly:event_list",
		"grid",
		Array(
			"COMPOSITE_FRAME_MODE" => "A",
			"COMPOSITE_FRAME_TYPE" => "AUTO",
			"FILTER" => array("USERS"),
			"PAGE_NUM" => "30",
			"USER_PATH" => "/company/personal/user/#user_id#/"
		)
	);
}
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>