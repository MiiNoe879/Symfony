<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/about/company/contacts.php");

$APPLICATION->SetTitle(GetMessage("ABOUT_TITLE"));
?>
<?=GetMessage("ABOUT_INFO", array("#SITE#" => "/"))?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>