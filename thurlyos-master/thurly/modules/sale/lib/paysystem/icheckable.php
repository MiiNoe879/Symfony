<?php

namespace Thurly\Sale\PaySystem;

use Thurly\Sale\Payment;

interface ICheckable
{
	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 */
	public function check(Payment $payment);
}
