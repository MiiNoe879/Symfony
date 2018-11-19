<?

CCurrencyLang::disableUseHideZero();

$orderId = (int)$GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];
if ($orderId > 0)
{
	/** @var \Thurly\Sale\Order $order */
	$order = \Thurly\Sale\Order::load($orderId);
	if ($order)
	{
		/** @var \Thurly\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $order->getPaymentCollection();
		/** @var \Thurly\Sale\Payment $payment */
		foreach ($paymentCollection as $payment)
		{
			if (!$payment->isInner())
				break;
		}
		if ($payment)
		{
			$context = \Thurly\Main\Application::getInstance()->getContext();
			$service = \Thurly\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
			if ($_REQUEST['pdf'] && $_REQUEST['GET_CONTENT'] == 'Y')
			{
				$result = $service->initiatePay($payment, $context->getRequest(), \Thurly\Sale\PaySystem\BaseServiceHandler::STRING);
				if ($result->isSuccess())
					return $result->getTemplate();
			}

			$result = $service->initiatePay($payment, $context->getRequest());
		}
		CCurrencyLang::enableUseHideZero();
	}
}
?>