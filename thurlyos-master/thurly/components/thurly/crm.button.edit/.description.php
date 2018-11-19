<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	'NAME' => GetMessage('SITE_BUTTON_EDIT_NAME'),
	'DESCRIPTION' => GetMessage('SITE_BUTTON_EDIT_DESCRIPTION'),
	'ICON' => '/images/icon.gif',
	'SORT' => 20,
	'PATH' => array(
		'ID' => 'crm',
		'NAME' => GetMessage('CRM_NAME'),
		'CHILD' => array(
			'ID' => 'webform',
			'NAME' => GetMessage('SITE_BUTTON_NAME')
		)
	),
	'CACHE_PATH' => 'Y'
);
?>