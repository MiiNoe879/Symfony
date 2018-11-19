<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('CRM_WEB_FORM_COMP_NAME'),
	'DESCRIPTION' => GetMessage('CRM_WEB_FORM_COMP_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'SORT' => 10,
	'COMPLEX' => 'Y',
	'PATH' => array(
		'ID' => 'crm',
		'NAME' => GetMessage('CRM_NAME'),
		'CHILD' => array(
			'ID' => 'webform',
			'NAME' => GetMessage('CRM_WEBFORM_NAME')
		)
	),
	'CACHE_PATH' => 'Y'	
);
?>