<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	'NAME' => GetMessage('CRM_CREATE_CALL_NAME'),
	'DESCRIPTION' => GetMessage('CRM_CREATE_CALL_DESC'),
	'TYPE' => array('activity', 'robot_activity'),
	'CLASS' => 'CrmCreateCallActivity',
	'JSCLASS' => 'BizProcActivity',
	'CATEGORY' => array(
		'ID' => 'interaction',
		"OWN_ID" => 'crm',
		"OWN_NAME" => 'CRM',
	),
	'RETURN' => array(
		'Id' => array(
			'NAME' => GetMessage('CRM_CREATE_CALL_ID'),
			'TYPE' => 'int',
		),
	),
	'FILTER' => array(
		'INCLUDE' => array(
			array('crm')
		),
	),
	'ROBOT_SETTINGS' => array(
		'CATEGORY' => 'employee',
		'RESPONSIBLE_PROPERTY' => 'Responsible'
	),
);