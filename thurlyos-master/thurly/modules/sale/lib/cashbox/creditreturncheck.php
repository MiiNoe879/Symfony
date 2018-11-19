<?php

namespace Thurly\Sale\Cashbox;

use Thurly\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class CreditReturnCheck
 * @package Thurly\Sale\Cashbox
 */

class CreditReturnCheck extends CreditCheck
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'creditreturn';
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_CREDIT_RETURN_NAME');
	}

	/**
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_CONSUMPTION;
	}
}