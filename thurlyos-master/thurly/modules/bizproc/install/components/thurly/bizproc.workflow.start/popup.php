<?
define("STOP_STATISTICS", true);

$SITE_ID = '';
if (isset($_REQUEST["site_id"]) && is_string($_REQUEST["site_id"]))
	$SITE_ID = substr(preg_replace("/[^a-z0-9_]/i", "", $_REQUEST["site_id"]), 0, 2);

if ($SITE_ID != '')
	define("SITE_ID", $SITE_ID);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();

$APPLICATION->ShowAjaxHead();
$APPLICATION->IncludeComponent("thurly:bizproc.workflow.start",
	'modern',
	array(
		"MODULE_ID" => $request->getPost('module_id'),
		"ENTITY" => $request->getPost('entity'),
		"DOCUMENT_TYPE" => $request->getPost('document_type'),
		"DOCUMENT_ID" => $request->getPost('document_id'),
		"TEMPLATE_ID" => $request->getPost('template_id'),
		"AUTO_EXECUTE_TYPE" => $request->getPost('auto_execute_type')
	)
);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');
die();