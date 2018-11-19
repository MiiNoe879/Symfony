<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$APPLICATION->ShowAjaxHead();
$APPLICATION->IncludeComponent("thurly:bizproc.workflow.info",
	'.default',
	array(
		'WORKFLOW_ID' => isset($_REQUEST['WORKFLOW_ID'])? $_REQUEST['WORKFLOW_ID'] : 0,
		'POPUP' => 'Y'
	)
);