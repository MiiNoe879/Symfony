<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
?>
<?$APPLICATION->IncludeComponent("thurly:bizproc.task.list", "mobile", array())?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>