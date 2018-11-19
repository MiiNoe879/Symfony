<?php

namespace Sale\Handlers\PaySystem;

use Thurly\Main\Localization\Loc;
use Thurly\Main\Request;
use Thurly\Sale\PaySystem;
use Thurly\Sale\Payment;

Loc::loadMessages(__FILE__);

/**
 * Class CashOnDeliveryHandler
 */
class CashOnDeliveryHandler extends PaySystem\BaseServiceHandler
{
	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		return new PaySystem\ServiceResult();
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array();
	}

}