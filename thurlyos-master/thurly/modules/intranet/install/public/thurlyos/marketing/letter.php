<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent("thurly:sender.letter", ".default", array(
	'SEF_FOLDER' => '/marketing/letter/',
	'PATH_TO_SEGMENT_ADD' => '/marketing/segment/edit/0/',
	'PATH_TO_SEGMENT_EDIT' => '/marketing/segment/edit/#id#/',
));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");