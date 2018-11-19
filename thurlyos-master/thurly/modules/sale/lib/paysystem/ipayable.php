<?php

namespace Thurly\Sale\PaySystem;

use Thurly\Sale\Payment;

interface IPayable
{
	public function getPrice(Payment $payment);
}
