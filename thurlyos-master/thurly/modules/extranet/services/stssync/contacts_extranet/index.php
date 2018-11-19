<?
define('BX_SECURITY_SESSION_VIRTUAL', true);
define("NOT_CHECK_PERMISSIONS", true);
define("STOP_STATISTICS", true);
define("IS_EXTRANET", true);
define("EXTRANET_NO_REDIRECT", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");

$siteId = \CSite::GetDefSite();
if(\Thurly\Main\Loader::includeModule('extranet'))
{
	$extrSiteId = \CExtranet::GetExtranetSiteID();
	if($extrSiteId)
	{
		$siteId = $extrSiteId;
	}
}

$redirectPath = COption::GetOptionString("main", "TOOLTIP_PATH_TO_USER", '/company/personal/user/#user_id#/', $siteId);

$APPLICATION->IncludeComponent(
	"thurly:stssync.server",
	"",
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/stssync/contacts_extranet/",
		"REDIRECT_PATH" => str_replace('#user_id#', '#ID#', $redirectPath),
		'WEBSERVICE_NAME' => 'thurly.webservice.intranet.contacts',
		'WEBSERVICE_CLASS' => 'CIntranetContactsWS',
		'WEBSERVICE_MODULE' => 'intranet',
	),
	null, array('HIDE_ICONS' => 'Y')
);

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_after.php");
?>