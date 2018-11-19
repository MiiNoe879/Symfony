<?php

namespace Thurly\Sale\PaySystem;

use Thurly\Sale\Payment;

interface IRefund
{
	public function refund(Payment $payment, $refundableSum);
}
