<?php
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$arParams['WEBSERVICE_NAME'] = 'thurly.crm.contact.webservice';
$arParams['WEBSERVICE_CLASS'] = 'CCrmContactWS';
$arParams['WEBSERVICE_MODULE'] = 'crm';

$APPLICATION->IncludeComponent(
	"thurly:crm.contact.webservice",
	"",
	array()
);

?>