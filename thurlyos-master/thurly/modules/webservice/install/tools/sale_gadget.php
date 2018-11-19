<?
define("NO_KEEP_STATISTIC", true);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");?>

<?$APPLICATION->IncludeComponent("thurly:webservice.sale", ".default", Array());?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>