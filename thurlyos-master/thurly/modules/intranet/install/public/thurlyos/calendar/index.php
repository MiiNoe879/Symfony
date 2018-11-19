<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
CModule::IncludeModule("intranet");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/calendar/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));

$APPLICATION->IncludeComponent(
	"thurly:calendar.grid",
	"",
	Array(
		"CALENDAR_TYPE" => "company_calendar",
		"ALLOW_SUPERPOSE" => "Y",
		"ALLOW_RES_MEETING" => "Y"
	)
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
