<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Thurly\Sale\Order;

function liqpay_parseTag($rs, $tag)
{
	$rs = str_replace("\n", "", str_replace("\r", "", $rs));
	$tags = '<'.$tag.'>';
	$tage = '</'.$tag;
	$start = strpos($rs, $tags)+strlen($tags);
	$end = strpos($rs, $tage);

	return substr($rs, $start, ($end-$start));
}

if ($_POST['signature']=="" || $_POST['operation_xml']=="")
	die();

$insig = $_POST['signature'];
$resp = base64_decode($_POST['operation_xml']);
$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();

$entityId = str_replace("PAYMENT_", "", liqpay_parseTag($resp, "order_id"));

list($orderId, $paymentId) = \Thurly\Sale\PaySystem\Manager::getIdsByPayment($entityId);

if ($orderId > 0)
{
	/** @var \Thurly\Sale\Order $order */
	$order = \Thurly\Sale\Order::load($orderId);
	if ($order)
	{
		/** @var \Thurly\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $order->getPaymentCollection();
		if ($paymentCollection && $paymentId > 0)
		{
			/** @var \Thurly\Sale\Payment $payment */
			$payment = $paymentCollection->getItemById($paymentId);
			if ($payment)
			{
				$service = \Thurly\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
				if ($service)
					$service->processRequest($request);
			}
		}
	}
}
