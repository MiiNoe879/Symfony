<?php
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NOT_CHECK_PERMISSIONS", true);
define("DisableEventsCheck", true);
define("NO_AGENT_CHECK", true);

global $APPLICATION;

/* PROVIDER -> CONTROLLER -> PORTAL */

require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if (!\Thurly\Main\Loader::includeModule("imconnector"))
{
	echo \Thurly\Main\Web\Json::encode(Array(
		'OK' => false,
		'ERROR' => Array(
			'CODE' => 'MODULE_NOT_INSTALLED',
			'MESSAGE' => "Module ImConnector isn't installed"
		)
	));
}
else
{
	if (is_object($APPLICATION))
		$APPLICATION->RestartBuffer();

	$request = Thurly\Main\HttpApplication::getInstance()->getContext()->getRequest();
	$post = $request->getPostList()->toArray();
	$portal = new \Thurly\ImConnector\Input($post);

	$result = $portal->reception();

	/*if($result->isSuccess())
		echo \Thurly\Main\Web\Json::encode(\Thurly\ImConnector\Converter::convertObjectArray($result));
	else
		echo "You don't have access to this page.";*/
	if(is_object($result))
		echo \Thurly\Main\Web\Json::encode(\Thurly\ImConnector\Converter::convertObjectArray($result));
	else
		echo "You don't have access to this page.";
}

CMain::FinalActions();
die();