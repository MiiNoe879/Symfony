<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/telephony/detail.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("VI_PAGE_STAT_DETAIL"));
?>

<?$APPLICATION->IncludeComponent("thurly:voximplant.statistic.detail", "", array("LIMIT" => "30"));?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
