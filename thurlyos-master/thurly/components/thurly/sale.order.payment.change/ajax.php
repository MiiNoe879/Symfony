<?php

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

use Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$request = Thurly\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Thurly\Main\Web\PostDecodeFilter);

if (!check_thurly_sessid() && !$request->isPost())
{
	die();
}

$orderData = $request->getPostList()->toArray();

$params['ACCOUNT_NUMBER'] = $orderData['accountNumber'];
$params['AJAX_DISPLAY'] = "Y";
$params['PAYMENT_NUMBER'] = $orderData['paymentNumber'];
$params['NEW_PAY_SYSTEM_ID'] = $orderData['paySystemId'];
$params['ALLOW_INNER'] = $orderData['inner'];
$params['ONLY_INNER_FULL'] = $orderData['onlyInnerFull'];
$params['PATH_TO_PAYMENT'] = strlen($orderData['pathToPayment']) > 0 ? htmlspecialcharsbx($orderData['pathToPayment']) : "";
$params['REFRESH_PRICES'] = ($orderData['refreshPrices'] === 'Y') ? 'Y' : 'N';
if ((float)$orderData['paymentSum'] > 0)
{
	$params['INNER_PAYMENT_SUM'] = (float)$orderData['paymentSum'];
}

CThurlyComponent::includeComponentClass("thurly:sale.order.payment.change");

$orderPayment = new SaleOrderPaymentChange();
$orderPayment->initComponent('thurly:sale.order.payment.change');
$orderPayment->includeComponent("", $params, null);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');
?>