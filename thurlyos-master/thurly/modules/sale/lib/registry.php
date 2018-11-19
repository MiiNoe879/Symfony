<?php
namespace Thurly\Sale;

use Thurly\Main\ArgumentException;

/**
 * Class Registry
 * @package Thurly\Sale
 */
final class Registry
{
	const REGISTRY_TYPE_ORDER = 'ORDER';
	const REGISTRY_TYPE_ARCHIVE_ORDER = 'ARCHIVE_ORDER';

	const ENTITY_SHIPMENT = 'SHIPMENT';
	const ENTITY_ORDER = 'ORDER';
	const ENTITY_PAYMENT = 'PAYMENT';
	const ENTITY_PAYMENT_COLLECTION = 'PAYMENT_COLLECTION';
	const ENTITY_SHIPMENT_COLLECTION = 'SHIPMENT_COLLECTION';
	const ENTITY_PROPERTY_VALUE = 'PROPERTY_VALUE';
	const ENTITY_BUNDLE_COLLECTION = 'BUNDLE_COLLECTION';
	const ENTITY_BASKET = 'BASKET';
	const ENTITY_TAX = 'TAX';
	const ENTITY_BASKET_ITEM = 'BASKET_ITEM';
	const ENTITY_BASKET_PROPERTIES_COLLECTION = 'BASKET_PROPERTIES_COLLECTION';
	const ENTITY_BASKET_PROPERTY_ITEM = 'BASKET_PROPERTY_ITEM';
	const ENTITY_SHIPMENT_ITEM = 'SHIPMENT_ITEM';
	const ENTITY_SHIPMENT_ITEM_COLLECTION = 'SHIPMENT_ITEM_COLLECTION';
	const ENTITY_SHIPMENT_ITEM_STORE = 'SHIPMENT_ITEM_STORE';
	const ENTITY_SHIPMENT_ITEM_STORE_COLLECTION = 'SHIPMENT_ITEM_STORE_COLLECTION';
	const ENTITY_PROPERTY_VALUE_COLLECTION = 'PROPERTY_VALUE_COLLECTION';
	const ENTITY_OPTIONS = 'CONFIG_OPTION';
	const ENTITY_DISCOUNT = 'DISCOUNT';
	const ENTITY_PERSON_TYPE = 'PERSON_TYPE';

	private static $registryMap = array();
	private static $registryObjects = array();

	private $type = '';

	/**
	 * @return void
	 */
	private static function initRegistry()
	{
		static::$registryMap = array(
			static::REGISTRY_TYPE_ORDER => array(
				Registry::ENTITY_ORDER => '\Thurly\Sale\Order',
				Registry::ENTITY_PAYMENT => '\Thurly\Sale\Payment',
				Registry::ENTITY_PAYMENT_COLLECTION => '\Thurly\Sale\PaymentCollection',
				Registry::ENTITY_SHIPMENT => '\Thurly\Sale\Shipment',
				Registry::ENTITY_SHIPMENT_COLLECTION => '\Thurly\Sale\ShipmentCollection',
				Registry::ENTITY_SHIPMENT_ITEM => '\Thurly\Sale\ShipmentItem',
				Registry::ENTITY_SHIPMENT_ITEM_COLLECTION => '\Thurly\Sale\ShipmentItemCollection',
				Registry::ENTITY_SHIPMENT_ITEM_STORE => '\Thurly\Sale\ShipmentItemStore',
				Registry::ENTITY_SHIPMENT_ITEM_STORE_COLLECTION => '\Thurly\Sale\ShipmentItemStoreCollection',
				Registry::ENTITY_PROPERTY_VALUE_COLLECTION => '\Thurly\Sale\PropertyValueCollection',
				Registry::ENTITY_PROPERTY_VALUE => '\Thurly\Sale\PropertyValue',
				Registry::ENTITY_TAX => '\Thurly\Sale\Tax',
				Registry::ENTITY_BASKET_PROPERTY_ITEM => '\Thurly\Sale\BasketPropertyItem',
				Registry::ENTITY_BUNDLE_COLLECTION => '\Thurly\Sale\BundleCollection',
				Registry::ENTITY_BASKET => '\Thurly\Sale\Basket',
				Registry::ENTITY_BASKET_ITEM => '\Thurly\Sale\BasketItem',
				Registry::ENTITY_BASKET_PROPERTIES_COLLECTION => '\Thurly\Sale\BasketPropertiesCollection',
				Registry::ENTITY_DISCOUNT => '\Thurly\Sale\Discount',
				Registry::ENTITY_OPTIONS => 'Thurly\Main\Config\Option',
				Registry::ENTITY_PERSON_TYPE => 'Thurly\Sale\PersonType',
			),
			static::REGISTRY_TYPE_ARCHIVE_ORDER => array(
				Registry::ENTITY_ORDER => '\Thurly\Sale\Archive\Order',
				Registry::ENTITY_PAYMENT => '\Thurly\Sale\Payment',
				Registry::ENTITY_PAYMENT_COLLECTION => '\Thurly\Sale\PaymentCollection',
				Registry::ENTITY_SHIPMENT => '\Thurly\Sale\Shipment',
				Registry::ENTITY_SHIPMENT_COLLECTION => '\Thurly\Sale\ShipmentCollection',
				Registry::ENTITY_SHIPMENT_ITEM => '\Thurly\Sale\ShipmentItem',
				Registry::ENTITY_SHIPMENT_ITEM_COLLECTION => '\Thurly\Sale\ShipmentItemCollection',
				Registry::ENTITY_SHIPMENT_ITEM_STORE => '\Thurly\Sale\ShipmentItemStore',
				Registry::ENTITY_SHIPMENT_ITEM_STORE_COLLECTION => '\Thurly\Sale\ShipmentItemStoreCollection',
				Registry::ENTITY_PROPERTY_VALUE_COLLECTION => '\Thurly\Sale\PropertyValueCollection',
				Registry::ENTITY_PROPERTY_VALUE => '\Thurly\Sale\PropertyValue',
				Registry::ENTITY_TAX => '\Thurly\Sale\Tax',
				Registry::ENTITY_BASKET_PROPERTY_ITEM => '\Thurly\Sale\BasketPropertyItem',
				Registry::ENTITY_BUNDLE_COLLECTION => '\Thurly\Sale\BundleCollection',
				Registry::ENTITY_BASKET => '\Thurly\Sale\Basket',
				Registry::ENTITY_BASKET_ITEM => '\Thurly\Sale\BasketItem',
				Registry::ENTITY_BASKET_PROPERTIES_COLLECTION => '\Thurly\Sale\BasketPropertiesCollection',
				Registry::ENTITY_DISCOUNT => '\Thurly\Sale\Discount',
				Registry::ENTITY_OPTIONS => 'Thurly\Main\Config\Option',
				Registry::ENTITY_PERSON_TYPE => 'Thurly\Sale\PersonType',
			),
		);
	}

	/**
	 * @param $type
	 * @return Registry
	 * @throws ArgumentException
	 */
	public static function getInstance($type)
	{
		if (!static::$registryMap)
			static::initRegistry();

		if (!isset(static::$registryObjects[$type]))
		{
			if (isset(static::$registryMap[$type]))
				static::$registryObjects[$type] = new static($type);
			else
				throw new ArgumentException();
		}

		return static::$registryObjects[$type];
	}

	/**
	 * @param $code
	 * @param $registryItem
	 * @return void
	 */
	public static function setRegistry($code, $registryItem)
	{
		if (!static::$registryMap)
			static::initRegistry();

		static::$registryMap[$code] = $registryItem;
	}

	/**
	 * Registry constructor.
	 * @param $type
	 */
	private function __construct($type)
	{
		$this->type = $type;
	}

	/**
	 * @param $code
	 * @param $className
	 */
	public function set($code, $className)
	{
		static::$registryMap[$this->type][$code] = $className;
	}

	/**
	 * @param $code
	 * @return mixed
	 * @throws ArgumentException
	 */
	public function get($code)
	{
		if (isset(static::$registryMap[$this->type][$code]))
			return static::$registryMap[$this->type][$code];

		throw new ArgumentException();
	}

	/**
	 * @return string
	 */
	public function getOrderClassName()
	{
		return $this->get(static::ENTITY_ORDER);
	}

	/**
	 * @return string
	 */
	public function getPaymentClassName()
	{
		return $this->get(static::ENTITY_PAYMENT);
	}

	/**
	 * @return string
	 */
	public function getShipmentClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT);
	}

	/**
	 * @return string
	 */
	public function getShipmentItemCollectionClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_ITEM_COLLECTION);
	}

	/**
	 * @return string
	 */
	public function getShipmentItemClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_ITEM);
	}

	/**
	 * @return string
	 */
	public function getShipmentItemStoreClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_ITEM_STORE);
	}

	/**
	 * @return string
	 */
	public function getShipmentItemStoreCollectionClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_ITEM_STORE_COLLECTION);
	}

	/**
	 * @return string
	 */
	public function getBasketItemClassName()
	{
		return $this->get(static::ENTITY_BASKET_ITEM);
	}

	/**
	 * @return string
	 */
	public function getShipmentCollectionClassName()
	{
		return $this->get(static::ENTITY_SHIPMENT_COLLECTION);
	}

	/**
	 * @return string
	 */
	public function getPaymentCollectionClassName()
	{
		return $this->get(static::ENTITY_PAYMENT_COLLECTION);
	}

	/**
	 * @return string
	 */
	public function getPropertyValueCollectionClassName()
	{
		return $this->get(static::ENTITY_PROPERTY_VALUE_COLLECTION);
	}

	/**
	 * @return string
	 */
	public function getPropertyValueClassName()
	{
		return $this->get(static::ENTITY_PROPERTY_VALUE);
	}

	/**
	 * @return string
	 */
	public function getBasketClassName()
	{
		return $this->get(static::ENTITY_BASKET);
	}

	/**
	 * @return string
	 */
	public function getBundleCollectionClassName()
	{
		return $this->get(static::ENTITY_BUNDLE_COLLECTION);
	}

	/**
	 * @return string
	 */
	public function getDiscountClassName()
	{
		return $this->get(static::ENTITY_DISCOUNT);
	}

	/**
	 * @return string
	 */
	public function getTaxClassName()
	{
		return $this->get(static::ENTITY_TAX);
	}

	/**
	 * @return string
	 */
	public function getBasketPropertiesCollectionClassName()
	{
		return $this->get(static::ENTITY_BASKET_PROPERTIES_COLLECTION);
	}

	/**
	 * @return string
	 */
	public function getBasketPropertyItemClassName()
	{
		return $this->get(static::ENTITY_BASKET_PROPERTY_ITEM);
	}

	/**
	 * @return string
	 */
	public function getPersonTypeClassName()
	{
		return $this->get(static::ENTITY_PERSON_TYPE);
	}
}