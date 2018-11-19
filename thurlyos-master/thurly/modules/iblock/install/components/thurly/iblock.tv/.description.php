<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("THURLYTVBIG_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("THURLYTVBIG_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/thurly_tv.gif",
	"COMPLEX" => "N",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "media",
			"NAME" => GetMessage("THURLYTVBIG_COMPONENTS"),
		),
	),
);
?>