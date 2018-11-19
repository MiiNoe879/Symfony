<?
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

CModule::IncludeModule("fileman");

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

if (check_thurly_sessid())
{
	CHTMLEditor::RequestAction($action);
}

require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");
?>
