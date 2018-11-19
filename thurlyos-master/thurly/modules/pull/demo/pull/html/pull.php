<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("Push & Pull");
?>

<?
$APPLICATION->IncludeComponent("yourcompanyprefix:pull.test", '');
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>