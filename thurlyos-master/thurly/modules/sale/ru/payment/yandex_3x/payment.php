<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

$entityId = CSalePaySystemAction::GetParamValue("ORDER_PAYMENT_ID");
list($orderId, $paymentId) = \Thurly\Sale\PaySystem\Manager::getIdsByPayment($entityId);

/** @var \Thurly\Sale\Order $order */
$order = \Thurly\Sale\Order::load($orderId);

/** @var \Thurly\Sale\PaymentCollection $paymentCollection */
$paymentCollection = $order->getPaymentCollection();

/** @var \Thurly\Sale\Payment $payment */
$payment = $paymentCollection->getItemById($paymentId);

$data = \Thurly\Sale\PaySystem\Manager::getById($payment->getPaymentSystemId());

$service = new \Thurly\Sale\PaySystem\Service($data);
$service->initiatePay($payment);
