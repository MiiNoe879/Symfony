<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
?>
<?$APPLICATION->IncludeComponent(
	"thurly:crm.lead.rest", "", Array()
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>