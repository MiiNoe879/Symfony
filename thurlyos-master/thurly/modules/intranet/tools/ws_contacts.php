<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php"); 

$APPLICATION->IncludeComponent(
	"thurly:webservice.server",
	"",
	array(
		'WEBSERVICE_NAME' => 'thurly.webservice.intranet.contacts',
		'WEBSERVICE_CLASS' => 'CIntranetContactsWS',
		'WEBSERVICE_MODULE' => 'intranet',
	),
	null, array('HIDE_ICONS' => 'Y')
);

die();
?>