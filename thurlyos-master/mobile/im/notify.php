<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

CModule::IncludeModule('im');

$APPLICATION->IncludeComponent("thurly:mobile.im.notify", ".default", array(), false, Array("HIDE_ICONS" => "Y"));
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>