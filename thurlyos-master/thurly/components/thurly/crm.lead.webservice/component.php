<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule('crm'))
	return;

if(!CModule::IncludeModule('webservice'))
	return;

$arParams['WEBSERVICE_NAME'] = 'thurly.crm.lead.webservice';
$arParams['WEBSERVICE_CLASS'] = 'CCrmLeadWS';
$arParams['WEBSERVICE_MODULE'] = '';

$APPLICATION->IncludeComponent(
	'thurly:webservice.server',
	'',
	$arParams
);

die();
?>