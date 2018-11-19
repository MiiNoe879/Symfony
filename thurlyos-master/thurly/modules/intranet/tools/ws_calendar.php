<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php"); 

if (COption::GetOptionString("intranet", "calendar_2", "N") == "Y" && CModule::IncludeModule("calendar"))
{
	$APPLICATION->IncludeComponent(
		"thurly:webservice.server",
		"",
		array(
			'WEBSERVICE_NAME' => 'thurly.webservice.calendar',
			'WEBSERVICE_CLASS' => 'CCalendarWebService',
			'WEBSERVICE_MODULE' => 'calendar',
		),
		null, array('HIDE_ICONS' => 'Y')
	);
}
else
{
	$APPLICATION->IncludeComponent(
		"thurly:webservice.server",
		"",
		array(
			'WEBSERVICE_NAME' => 'thurly.webservice.intranet.calendar',
			'WEBSERVICE_CLASS' => 'CIntranetCalendarWS',
			'WEBSERVICE_MODULE' => 'intranet',
		),
		null, array('HIDE_ICONS' => 'Y')
	);
}
die();
?>