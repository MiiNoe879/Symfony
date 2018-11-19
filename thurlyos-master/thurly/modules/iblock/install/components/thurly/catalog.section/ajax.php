<?
/** @global \CMain $APPLICATION */
define('STOP_STATISTICS', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Thurly\Main\Web\PostDecodeFilter);

if (!\Thurly\Main\Loader::includeModule('iblock'))
	return;

$signer = new \Thurly\Main\Security\Sign\Signer;
try
{
	$template = $signer->unsign($request->get('template'), 'catalog.section');
	$paramString = $signer->unsign($request->get('parameters'), 'catalog.section');
}
catch (\Thurly\Main\Security\Sign\BadSignatureException $e)
{
	die();
}

$parameters = unserialize(base64_decode($paramString));
if (isset($parameters['PARENT_NAME']))
{
	$parent = new CThurlyComponent();
	$parent->InitComponent($parameters['PARENT_NAME'], $parameters['PARENT_TEMPLATE_NAME']);
	$parent->InitComponentTemplate($parameters['PARENT_TEMPLATE_PAGE']);
}
else
{
	$parent = false;
}

$APPLICATION->IncludeComponent(
	'thurly:catalog.section',
	$template,
	$parameters,
	$parent
);