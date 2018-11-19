<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if(!$GLOBALS['USER']->IsAdmin())
	$APPLICATION->AuthForm("");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/updates/updates.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("UPDATES_TITLE"));
?>
<?$APPLICATION->IncludeComponent("thurly:intranet.updates", "", array());?>
<? require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php"); ?>