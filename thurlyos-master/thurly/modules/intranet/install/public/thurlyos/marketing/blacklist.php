<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent("thurly:sender.blacklist", ".default", array(
	'SEF_FOLDER' => '/marketing/blacklist/',
));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");