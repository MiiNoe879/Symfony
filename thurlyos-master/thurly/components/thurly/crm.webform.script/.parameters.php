<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('crm'))
	return false;  

$arComponentParameters = Array(
	'GROUPS' => array(
	
	),
	'PARAMETERS' => array(
		'FORM_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CRM_ELEMENT_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
		),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("CRM_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
		)
	)
);


?>