<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if(!$GLOBALS['USER']->IsAdmin())
	$APPLICATION->AuthForm("");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/updates/index.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("LICENSE_TITLE"));

$APPLICATION->IncludeComponent("thurly:intranet.updates.license", "", array());
?>
<? require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php"); ?>