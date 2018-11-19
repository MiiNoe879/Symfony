<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

/** @var CMain $APPLICATION */
$APPLICATION->SetTitle('ThurlyOS.Time');
$APPLICATION->IncludeComponent("thurly:faceid.timeman.start", ".default", array());

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");