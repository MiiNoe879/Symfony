<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent("thurly:sender.contact", ".default", array(
	'SEF_FOLDER' => '/marketing/contact/',
));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");