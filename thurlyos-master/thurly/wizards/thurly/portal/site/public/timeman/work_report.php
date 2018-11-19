<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php"); 
$APPLICATION->SetPageProperty("HIDE_SIDEBAR", "Y");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/timeman/work_report.php");

$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?> 
<?$APPLICATION->IncludeComponent(
	"thurly:timeman.report.weekly",
	"",
	Array(
	),
false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>