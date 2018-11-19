<?php

namespace Thurly\Sale\PaySystem;

/**
 * Interface IRefundExtended
 * @package Thurly\Sale\PaySystem
 */
interface IRefundExtended extends IRefund
{
	/**
	 * @return bool
	 */
	public function isRefundableExtended();
}
