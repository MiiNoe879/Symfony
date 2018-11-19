<?
define('BX_SECURITY_SESSION_VIRTUAL', true);
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
$APPLICATION->IncludeComponent(
	"thurly:stssync.server",
	"",
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/stssync/calendar/",
		"REDIRECT_PATH" => "#PATH#/?EVENT_ID=#ID#",
		'WEBSERVICE_NAME' => 'thurly.webservice.calendar',
		'WEBSERVICE_CLASS' => 'CCalendarWebService',
		'WEBSERVICE_MODULE' => 'calendar',
	),
	null, array('HIDE_ICONS' => 'Y')
);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");
?>