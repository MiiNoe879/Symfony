<?
/** @global \CMain $APPLICATION */

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
	$template = $signer->unsign($request->get('template'), 'sale.products.gift.section');
	$parameters = $signer->unsign($request->get('parameters'), 'sale.products.gift.section');
}
catch (\Thurly\Main\Security\Sign\BadSignatureException $e)
{
	die();
}

$APPLICATION->IncludeComponent(
	'thurly:sale.products.gift.section',
	$template,
	unserialize(base64_decode($parameters)),
	false
);