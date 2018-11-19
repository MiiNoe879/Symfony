<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("lists"))
	return;

if (!IsModuleInstalled("thurlyos"))
{
	$moduleId = "lists";
	if (!class_exists($moduleId))
		return false;

	$module = new $moduleId;
	$module->installDemoData();
}
?>