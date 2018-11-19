<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/services/help/novice.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?>
<?=GetMessage("SERVICES_INFO", array("#SITE#" => "/"))?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>