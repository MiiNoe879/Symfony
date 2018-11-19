<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("WebRTC demo");
?>

<?
$APPLICATION->IncludeComponent("yourcompanyprefix:pull.webrtc", '');
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>