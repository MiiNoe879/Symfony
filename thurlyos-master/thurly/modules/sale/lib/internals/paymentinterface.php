<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2014 Thurly
 */

interface IPaymentOrder
{
	public function getPaymentCollection();
	public function loadPaymentCollection();
}