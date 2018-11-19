<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('crm'))
	return false;

$arComponentParameters = Array(
	'PARAMETERS' => array(	
		'FORM_ID' => array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CRM_WEBFORM_EDIT_FORM_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '20'
		),
	)	
);
?>