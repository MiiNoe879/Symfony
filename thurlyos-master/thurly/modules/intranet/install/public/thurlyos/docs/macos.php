<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent(
	"thurly:intranet.promo.page",
	"disk.app",
	array("PAGE" => "MACOS")
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");