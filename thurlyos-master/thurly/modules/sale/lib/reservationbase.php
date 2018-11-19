<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2012 Thurly
 */
namespace Thurly\Sale;

use Thurly\Main\Localization\Loc;
use Thurly\Sale\Basket;


Loc::loadMessages(__FILE__);


abstract class ReservationBase extends Attributes
{

	protected function __construct()
	{

	}

	/**
	 * @return bool
	 */
	public function getEnableReservation()
	{
		//default_use_store_control = Y
		return (COption::GetOptionString("catalog", "enable_reservation") == "Y"
//			&& COption::GetOptionString("sale", "product_reserve_condition", "O") != "S"
			&& COption::GetOptionString('catalog','default_use_store_control') == "Y");
	}

	/**
	 * @param Basket $basketCollection
	 * @param array $productList
	 * @throws \Exception
	 */


	/**
	 * @param Basket $basketCollection
	 * @param array $productList
	 * @return array
	 */
	public static function getProductList(Basket $basketCollection, array $productList = array())
	{
		throw new \Exception("Method 'ReservationBase::getProduct' is not overridden");
	}

} 