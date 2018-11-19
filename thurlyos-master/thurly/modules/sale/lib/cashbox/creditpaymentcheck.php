<?php

namespace Thurly\Sale\Cashbox;

use Thurly\Main;
use Thurly\Sale\Payment;
use Thurly\Sale\Shipment;
use Thurly\Sale\ShipmentItem;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class CreditPaymentCheck
 * @package Thurly\Sale\Cashbox
 */

class CreditPaymentCheck extends Check
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'creditpayment';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_CREDIT_PAYMENT_NAME');
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_INCOME;
	}

}