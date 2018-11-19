<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

if (IsModuleInstalled('tasks'))
{
	$APPLICATION->IncludeComponent(
		"thurly:webservice.server",
		"",
		array(
			'WEBSERVICE_NAME' => 'thurly.webservice.tasks',
			'WEBSERVICE_CLASS' => 'CTasksWebService',
			'WEBSERVICE_MODULE' => 'tasks',
		),
		null, array('HIDE_ICONS' => 'Y')
	);
}

die();
?>