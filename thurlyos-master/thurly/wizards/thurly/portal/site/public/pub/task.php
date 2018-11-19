<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent(
	"thurly:tasks.iframe.popup",
	"public",
	array(),
	null,
	array("HIDE_ICONS" => "Y")
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");