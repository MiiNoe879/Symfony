<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

?>
<?$APPLICATION->IncludeComponent("thurly:mobile.help", ".default", array(
	),
	false,
	Array("HIDE_ICONS" => "Y")
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>