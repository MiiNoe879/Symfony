<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/about/company/style.php");
$APPLICATION->SetTitle(GetMessage("ABOUT_TITLE"));
?>
<?=GetMessage("ABOUT_INFO1")?>

<?=GetMessage("ABOUT_INFO2", array("#SITE#" => "/"))?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>