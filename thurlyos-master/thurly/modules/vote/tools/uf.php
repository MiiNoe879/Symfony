<?php
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID'])? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if(!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/thurly/modules/main/include/prolog_before.php");

if(!\Thurly\Main\Loader::includeModule('vote'))
{
	die;
}
$ufController = new Thurly\Vote\Attachment\Controller();
$ufController->setActionName($_GET['action'])->exec();