<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/openlines/list/edit.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("OL_PAGE_LINES_EDIT_TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:imopenlines.lines.edit", "", array());?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
