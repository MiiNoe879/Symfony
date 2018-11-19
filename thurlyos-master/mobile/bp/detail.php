<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
?>
<?$APPLICATION->IncludeComponent("thurly:bizproc.task", "mobile", array(
	"TASK_ID" => $_GET["task_id"]
))?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>