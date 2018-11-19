<?
require($_SERVER['DOCUMENT_ROOT'].'/thurly/header.php');
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/services/help/absence_help.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?><script type='text/javascript' src='/thurly/templates/learning/js/imgshw.js'></script>

<?=GetMessage("SERVICES_INFO", array("#SITE#" => "/"))?>
<?require($_SERVER['DOCUMENT_ROOT'].'/thurly/footer.php');?>