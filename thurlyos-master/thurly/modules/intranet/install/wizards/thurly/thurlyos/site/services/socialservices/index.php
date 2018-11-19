<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(\Thurly\Main\ModuleManager::isModuleInstalled("thurlyos") || !\Thurly\Main\Loader::includeModule("socialservices"))
	return;

if(\Thurly\Main\Config\Option::get('socialservices', 'thurlyosnet_id', '') === '')
{
	$request = \Thurly\Main\Context::getCurrent()->getRequest();
	$host = ($request->isHttps() ? 'https://' : 'http://').$request->getHttpHost();

	$registerResult = \CSocServThurlyOSNet::registerSite($host);

	if(is_array($registerResult) && isset($registerResult["client_id"]) && isset($registerResult["client_secret"]))
	{
		\Thurly\Main\Config\Option::set('socialservices', 'thurlyosnet_domain', $host);
		\Thurly\Main\Config\Option::set('socialservices', 'thurlyosnet_id', $registerResult["client_id"]);
		\Thurly\Main\Config\Option::set('socialservices', 'thurlyosnet_secret', $registerResult["client_secret"]);
	}
}

if (\Thurly\Main\Config\Option::get('socialservices', 'thurlyosnet_id', '') !== '')
{
	COption::SetOptionString("socialservices", "auth_services".$suffix, serialize(array('ThurlyOSNet' => 'Y')));
}
?>