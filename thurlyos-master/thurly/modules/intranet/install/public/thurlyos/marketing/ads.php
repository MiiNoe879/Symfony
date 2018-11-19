<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent("thurly:sender.ads", ".default", array(
	'SEF_FOLDER' => '/marketing/ads/',
	'PATH_TO_SEGMENT_ADD' => '/marketing/segment/edit/0/',
	'PATH_TO_SEGMENT_EDIT' => '/marketing/segment/edit/#id#/',
	'IS_ADS' => 'Y',
));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");