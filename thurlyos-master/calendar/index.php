<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/about/calendar.php");
$APPLICATION->SetTitle(GetMessage("ABOUT_TITLE"));
?>

<?$APPLICATION->IncludeComponent(
	"thurly:calendar.grid",
	"",
	Array(
		"CALENDAR_TYPE" => "company_calendar",
		"ALLOW_SUPERPOSE" => "Y",
		"ALLOW_RES_MEETING" => "Y"
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
