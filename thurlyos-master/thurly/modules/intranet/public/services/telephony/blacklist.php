<?
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
if (!$GLOBALS['USER']->IsAdmin())
	$APPLICATION->AuthForm("");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/services/telephony/blacklist.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_after.php");

$APPLICATION->SetTitle(GetMessage("VI_PAGE_BLACKLIST_TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:voximplant.blacklist", "", array());?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>
