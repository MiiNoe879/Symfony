<?php

namespace Thurly\Sale\PaySystem;

use Thurly\Sale\Payment;

interface IHold
{
	public function cancel(Payment $payment);

	public function confirm(Payment $payment);
}
