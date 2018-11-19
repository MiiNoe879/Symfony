<?
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');
?>

<?
$APPLICATION->IncludeComponent("thurly:mobile.crm.convert.sync", "", array());
?>

<?require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');?>
