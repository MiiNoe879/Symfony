<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2014 Thurly
 */

namespace Thurly\Sale;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BasketPropertiesCollection
 * @package Thurly\Sale
 */
class BasketPropertiesCollection extends BasketPropertiesCollectionBase
{
	/**
	 * @return BasketPropertiesCollection
	 */
	protected static function createBasketPropertiesCollectionObject()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$basketPropertiesCollectionClassName = $registry->getBasketPropertiesCollectionClassName();

		return new $basketPropertiesCollectionClassName();
	}

	/**
	 * Load basket item properties.
	 *
	 * @param array $parameters	orm getList parameters.
	 * @return \Thurly\Main\DB\Result
	 */
	public static function getList(array $parameters = array())
	{
		return Internals\BasketPropertyTable::getList($parameters);
	}

	/**
	 * Delete basket item properties.
	 *
	 * @param $primary
	 * @return Entity\DeleteResult
	 */
	protected static function delete($primary)
	{
		return Internals\BasketPropertyTable::delete($primary);
	}

	/**
	 * @return string
	 */
	protected function getBasketPropertiesCollectionElementClassName()
	{
		$registry  = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);

		return $registry->getBasketPropertyItemClassName();
	}

}