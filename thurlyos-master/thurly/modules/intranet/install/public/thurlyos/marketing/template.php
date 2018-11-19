<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent("thurly:sender.template", ".default", array(
	'SEF_FOLDER' => '/marketing/template/',
));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>