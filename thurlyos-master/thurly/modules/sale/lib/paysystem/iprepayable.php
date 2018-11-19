<?php

namespace Thurly\Sale\PaySystem;

use Thurly\Main\Request;
use Thurly\Sale\Payment;

interface IPrePayable
{
	public function initPrePayment(Payment $payment = null, Request $request);

	public function getProps();

	public function payOrder($orderData = array());

	public function setOrderConfig($orderData = array());

	public function basketButtonAction($orderData);
}
