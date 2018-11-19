<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
?><?$APPLICATION->IncludeComponent("thurly:mobile.file.list", "", array(
		"THUMBNAIL_RESIZE_METHOD" => "EXACT",
		"THUMBNAIL_SIZE" => 100
	),
	false,
	Array("HIDE_ICONS" => "Y")
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php")?>