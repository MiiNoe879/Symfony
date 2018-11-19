<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use \Thurly\Sale\Order;

$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();

$entityId = $request->get("ordernumber");
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
