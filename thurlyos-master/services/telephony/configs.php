<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/services/telephony/configs.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("VI_PAGE_CONFIGS_TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:voximplant.settings", "", array());?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
