<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule('crm'))
	return;

$arComponentParameters = array(
	'GROUPS' => array(
	),
	'PARAMETERS' => array(
		'VARIABLE_ALIASES' => Array(
			'mode' => Array('NAME' => GetMessage('CRM_PRESET_MODE')),
			'entity_type' => Array('NAME' => GetMessage('CRM_PRESET_ENTITY_TYPE_ID')),
			'preset_id' => Array('NAME' => GetMessage('CRM_PRESET_ID'))
		),
		'SEF_MODE' => Array(
			'PRESET_LIST_URL' => array(
				'NAME' => GetMessage('CRM_PRESET_LIST'),
				'DEFAULT' => '#entity_type#/',
				'VARIABLES' => array('entity_type')
			),
			'PRESET_EDIT_URL' => array(
				'NAME' => GetMessage('CRM_PRESET_EDIT'),
				'DEFAULT' => '#entity_type#/edit/#preset_id#/',
				'VARIABLES' => array('entity_type', 'preset_id')
			)
		)
	)
);
?>
