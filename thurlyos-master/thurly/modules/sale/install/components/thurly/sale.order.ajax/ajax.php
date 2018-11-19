<?php
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

$request = Thurly\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Thurly\Main\Web\PostDecodeFilter);

if (!Thurly\Main\Loader::includeModule('sale'))
	return;

Thurly\Main\Localization\Loc::loadMessages(dirname(__FILE__).'/class.php');

$signer = new \Thurly\Main\Security\Sign\Signer;
try
{
	$params = $signer->unsign($request->get('signedParamsString'), 'sale.order.ajax');
	$params = unserialize(base64_decode($params));
}
catch (\Thurly\Main\Security\Sign\BadSignatureException $e)
{
	die();
}

$action = $request->get($params['ACTION_VARIABLE']);
if (empty($action))
	return;

CThurlyComponent::includeComponentClass('thurly:sale.order.ajax');

$component = new SaleOrderAjax();
$component->arParams = $component->onPrepareComponentParams($params);
$component->executeComponent();