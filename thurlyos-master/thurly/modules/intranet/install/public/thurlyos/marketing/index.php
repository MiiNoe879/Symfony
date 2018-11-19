<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->IncludeComponent("thurly:sender.start", ".default", array(
	'PATH_TO_LETTER_ADD' => '/marketing/letter/edit/0/',
));

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");