<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/openlines/statistics.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("OL_PAGE_STATISTICS_DETAIL_TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:imopenlines.statistics.detail", "", array("LIMIT" => "30"));?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
