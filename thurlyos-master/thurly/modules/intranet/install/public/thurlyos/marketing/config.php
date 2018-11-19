<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent("thurly:sender.config.limits", ".default", array(
	'SEF_FOLDER' => '/marketing/config/',
));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");