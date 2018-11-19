<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent("thurly:sender.segment", ".default", array(
	'SEF_FOLDER' => '/marketing/segment/',
	'PATH_TO_CONTACT_LIST' => '/marketing/contact/list/',
	'PATH_TO_CONTACT_IMPORT' => '/marketing/contact/import/',
	'ONLY_CONNECTOR_FILTERS' => true,
));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>