<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/openlines/permissions.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("OL_PAGE_PERMISSIONS_TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:imopenlines.settings.perms", "", array());?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
