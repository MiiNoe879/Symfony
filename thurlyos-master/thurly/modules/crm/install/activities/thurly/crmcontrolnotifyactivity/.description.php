<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("CRM_CTRNA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("CRM_CTRNA_DESCR_DESCR"),
	"TYPE" => array('activity', 'robot_activity'),
	"CLASS" => "CrmControlNotifyActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	'FILTER' => array(
		'INCLUDE' => array(
			array('crm', 'CCrmDocumentDeal'),
			array('crm', 'CCrmDocumentLead')
		),
	),
	'ROBOT_SETTINGS' => array(
		'CATEGORY' => 'employee',
		'RESPONSIBLE_PROPERTY' => 'ToUsers',
		'RESPONSIBLE_TO_HEAD' => 'ToHead',
	),
);