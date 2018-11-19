<?php

namespace Sale\Handlers\PaySystem;

use Thurly\Main\Request;
use Thurly\Sale;
use Thurly\Sale\PaySystem;

class SberbankHandler extends PaySystem\BaseServiceHandler
{
	/**
	 * @param Sale\Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Sale\Payment $payment, Request $request = null)
	{
		return $this->showTemplate($payment, "template");
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB');
	}
}