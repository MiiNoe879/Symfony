<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/timeman/.left.menu_ext.php");

$licenseType = "";
if (\Thurly\Main\Loader::includeModule("thurlyos"))
{
	$licenseType = CThurlyOS::getLicenseType();
}

$isTimemanInstalled = IsModuleInstalled("timeman");

$aMenuLinks = array(
	array(
		GetMessage("MENU_ABSENCE"),
		"/timeman/",
		array(),
		array("menu_item_id" => "menu_absence"),
		""
	)
);

if (!(!$isTimemanInstalled && in_array($licenseType, array("company", "edu", "nfr"))))
{
	$aMenuLinks[] = array(
		GetMessage("MENU_TIMEMAN"),
		"/timeman/timeman.php",
		array(),
		array("menu_item_id" => "menu_timeman"),
		""
	);
}

if (IsModuleInstalled("faceid") && \Thurly\Main\Loader::includeModule('faceid') && \Thurly\FaceId\FaceId::isAvailable())
{
	$aMenuLinks[] = array(
		'ThurlyOS.Time',
		"/timeman/thurlyostime.php",
		array(),
		array("menu_item_id"=>"menu_thurlyostime"),
		""
	);
}

if (!(!$isTimemanInstalled && in_array($licenseType, array("company", "edu", "nfr"))))
{
	$aMenuLinks[] = array(
		GetMessage("MENU_WORK_REPORT"),
		"/timeman/work_report.php",
		array(),
		array("menu_item_id" => "menu_work_report"),
		""
	);
}

if (!(!IsModuleInstalled("meeting") && in_array($licenseType, array("company", "edu", "nfr"))))
{
	$aMenuLinks[] = array(
		GetMessage("MENU_MEETING"),
		"/timeman/meeting/",
		array(),
		array("menu_item_id" => "menu_meeting"),
		""
	);
}