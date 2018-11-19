<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPTA2_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPTA2_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "Task2Activity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	"RETURN" => array(
		"TaskId" => array(
			"NAME" => GetMessage("BPTA2_DESCR_TASKID"),
			"TYPE" => "int",
		),
		"ClosedDate" => array(
			"NAME" => GetMessage("BPTA2_DESCR_CLOSEDDATE"),
			"TYPE" => "datetime",
		),
		"ClosedBy" => array(
			"NAME" => GetMessage("BPTA2_DESCR_CLOSEDBY"),
			"TYPE" => "user",
		),
	),
	'ROBOT_SETTINGS' => array(
		'CATEGORY' => 'employee',
		'IS_AUTO' => true
	)
);