<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
?>
<?$APPLICATION->IncludeComponent("thurly:mobile.invite.user", "",
	array(),
	false,
	Array("HIDE_ICONS" => "Y")
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>