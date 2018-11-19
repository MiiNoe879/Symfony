<?php
namespace Thurly\Sale\Archive\Recovery;

use Thurly\Main,
	Thurly\Sale,
	Thurly\Sale\Archive,
	Thurly\Sale\Internals;

/**
 * Contain realization of Archive\Order object creation from archive.
 * Value of archive version is "1".
 * 
 * @package Thurly\Sale\Archive\Recovery
 */
class Version1 extends Base
{
	/**
	 * Manage loading order from archive.
	 * 
	 * @param $archivedOrder
	 *
	 * @return Sale\Order
	 */
	protected function loadOrder($archivedOrder)
	{
		$this->order = Archive\Order::create($archivedOrder['ORDER']['LID'], $archivedOrder['ORDER']['USER_ID'], $archivedOrder['ORDER']['CURRENCY']);
		$this->order->setPersonTypeId($archivedOrder['ORDER']['PERSON_TYPE_ID']);
		$basket = Sale\Basket::create($archivedOrder['ORDER']['LID']);
		$this->order->setBasket($basket);
		$basketItemsMap = $this->riseBasket($archivedOrder);
		$this->risePayment($archivedOrder);
		$this->riseShipment($archivedOrder, $basketItemsMap);
		$this->riseOrder($archivedOrder);
		$this->riseDiscount($archivedOrder);
		return $this->order;
	}

	/**
	 * Load order with properties from archive.
	 * 
	 * @param array $archivedOrder
	 *
	 * @return mixed
	 */
	protected function riseOrder($archivedOrder)
	{
		$oldOrderFields = $archivedOrder['ORDER'];
		$this->order->setFieldsNoDemand($oldOrderFields);
		$propertyCollection = $this->order->getPropertyCollection();
		$propertyCollectionArchived = $archivedOrder['PROPERTIES'];
		if (is_array($propertyCollectionArchived))
		{
			foreach ($propertyCollectionArchived as $propertyArchived)
			{
				$property = $propertyCollection->getItemByOrderPropertyId($propertyArchived['ORDER_PROPS_ID']);
				if ($property)
				{
					$property->setField('VALUE', $propertyArchived['VALUE']);
				}
			}
		}
		return;
	}

	/**
	 * Load basket from archive.
	 *
	 * @param array $archivedOrder
	 *
	 * @return array $basketItemsMap
	 */
	protected function riseBasket($archivedOrder)
	{
		$basketItemsMap = array();
		$basket = $this->order->getBasket();
		foreach ($archivedOrder['BASKET_ITEMS'] as &$archivedItem)
		{
			if (empty($archivedItem['SET_PARENT_ID']))
			{
				$item = $basket->createItem($archivedItem['MODULE'], $archivedItem['PRODUCT_ID']);
				$this->riseBasketItem($item, $archivedItem);
				$basketItemsMap[$archivedItem['ID']] = $item;
				$type = $archivedItem['TYPE'];
				unset($archivedItem);

				if ($type == Sale\BasketItem::TYPE_SET)
				{
					$bundleCollection = $item->getBundleCollection();
					foreach ($archivedOrder['BASKET_ITEMS'] as &$bundle)
					{
						if ($item->getId() !== (int)$bundle['SET_PARENT_ID'])
							continue;

						$itemBundle = $bundleCollection->createItem($bundle['MODULE'], $bundle['PRODUCT_ID']);
						$this->riseBasketItem($itemBundle, $bundle);
						$basketItemsMap[$bundle['ID']] = $itemBundle;
						unset($bundle);
					}
				}
			}
		}

		return $basketItemsMap;
	}

	/**
	 * Load basket items with properties from archive.
	 *
	 * @param Sale\BasketItem $item
	 * @param array $data
	 *
	 *@return array $basketItemsMap
	 */
	protected function riseBasketItem(&$item, $data = array())
	{
		$basketItemProperties = $data["PROPERTY_ITEMS"];
		unset($data["PROPERTY_ITEMS"], $data["SHIPMENT_BARCODE_ITEMS"]);
		$item->setFieldsNoDemand($data);
		$newPropertyCollection = $item->getPropertyCollection();
		if (is_array($basketItemProperties))
		{
			foreach ($basketItemProperties as $oldPropertyFields)
			{
				$propertyItem = $newPropertyCollection->createItem();
				unset($oldPropertyFields['ID'], $oldPropertyFields['BASKET_ID']);

				/** @var Sale\BasketPropertyItem $propertyItem*/
				$propertyItem->setFieldsNoDemand($oldPropertyFields);
			}
		}
	}

	/**
	 * Load payments from archive.
	 * 
	 * @param array $archivedOrder
	 *
	 * @throws Main\ObjectNotFoundException
	 */
	protected function risePayment($archivedOrder)
	{
		$paymentCollection = $this->order->getPaymentCollection();
		$paymentCollectionArchived = $archivedOrder['PAYMENT'];

		if (empty($paymentCollectionArchived))
			return;

		foreach ($paymentCollectionArchived as $oldPayment)
		{
			/** @var Sale\Payment $newPaymentItem */
			$newPaymentItem = $paymentCollection->createItem();
			$newPaymentItem->setFieldsNoDemand($oldPayment);
		}
		return;
	}

	/**
	 * Load shipments with items from archive.
	 * 
	 * @param array $archivedOrder
	 * @param array $basketItemsMap
	 *
	 * @throws Main\NotSupportedException
	 */
	protected function riseShipment($archivedOrder, $basketItemsMap)
	{
		/** @var Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $this->order->getShipmentCollection();
		$shipmentCollectionArchived = $archivedOrder['SHIPMENT'];

		if (empty($shipmentCollectionArchived))
			return;

		foreach ($shipmentCollectionArchived as $oldShipment)
		{
			$oldShipmentCollections = $oldShipment['SHIPMENT_ITEM'];
			unset($oldShipment['SHIPMENT_ITEM']);
			/** @var Sale\Shipment $newShipmentItem */
			$newShipmentItem = $shipmentCollection->createItem();
			$newShipmentItemCollection = $newShipmentItem->getShipmentItemCollection();
			if (is_array($oldShipmentCollections))
			{
				foreach ($oldShipmentCollections as $oldItemStore)
				{
					$basketItemId = $oldItemStore['BASKET_ID'];

					if (empty($basketItemsMap[$basketItemId]))
						continue;

					/** @var Sale\ShipmentItem $shipmentItem */
					$shipmentItem = $newShipmentItemCollection->createItem($basketItemsMap[$basketItemId]);
					$shipmentItem->setFieldsNoDemand($oldItemStore);
					$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
					/** @var Sale\ShipmentItemStore $itemStore */
					$itemStore = $shipmentItemStoreCollection->createItem($basketItemsMap[$basketItemId]);
					$oldBasketBarcodeData = $archivedOrder['BASKET_ITEMS'][$basketItemId]['SHIPMENT_BARCODE_ITEMS'][$oldItemStore['ID']];
					if (count($oldBasketBarcodeData))
					{
						$itemStore->setFieldsNoDemand($oldBasketBarcodeData);
					}
				}
			}

			$newShipmentItem->setFieldsNoDemand($oldShipment);
		}
	}

	/**
	 * Return discount's array from archive.
	 * The array includes information about discounts of base order.
	 * 
	 * @param array $archivedOrder
	 *
	 * @return mixed
	 */
	protected function riseDiscount($archivedOrder)
	{
		$discountDataRow = $archivedOrder['DISCOUNT'];
		$discountResultList = array();

		$resultData = array(
			'BASKET' => array(),
			'ORDER' => array(),
			'APPLY_BLOCKS' => array(),
			'DISCOUNT_LIST' => array(),
			'DISCOUNT_MODULES' => array(),
			'COUPON_LIST' => array(),
			'SHIPMENTS_ID' => array(),
			'DATA' => array()
		);

		$resultData['DATA'] = $this->prepareDiscountOrderData($discountDataRow);

		$orderDiscountData = $resultData['DATA']['ORDER'];

		$resultData['COUPON_LIST'] = $discountDataRow['COUPON_LIST'];

		foreach ($discountDataRow['RULES_DATA'] as $rule)
		{
			$discountList[] = $rule["DISCOUNT_DATA"];
			$rule['COUPON_ID'] = $resultData['COUPON_LIST'][$rule['ORDER_COUPON_ID']];

			if ($rule['APPLY_BLOCK_COUNTER'] < 0)
				continue;

			$blockCounter = $rule['APPLY_BLOCK_COUNTER'];

			if (!isset($orderDiscountIndex[$blockCounter]))
				$orderDiscountIndex[$blockCounter] = 0;

			if (!isset($resultData['APPLY_BLOCKS'][$blockCounter]))
			{
				$resultData['APPLY_BLOCKS'][$blockCounter] = array(
					'BASKET' => array(),
					'BASKET_ROUND' => array(),
					'ORDER' => array(),
					'ORDER_ROUND' => array()
				);
			}

			if ($rule['MODULE_ID'] == 'sale')
			{
				$discountId = (int)$rule['ORDER_DISCOUNT_ID'];
				if (!isset($orderDiscountLink[$discountId]))
				{
					$resultData['APPLY_BLOCKS'][$blockCounter]['ORDER'][$orderDiscountIndex[$blockCounter]] = array(
						'ORDER_ID' => $rule['ORDER_ID'],
						'DISCOUNT_ID' => $rule['ORDER_DISCOUNT_ID'],
						'ORDER_COUPON_ID' => $rule['ORDER_COUPON_ID'],
						'COUPON_ID' => ($rule['ORDER_COUPON_ID'] > 0 ? $rule['COUPON_ID'] : ''),
						'RESULT' => array(),
						'ACTION_BLOCK_LIST' => true
					);
					$orderDiscountLink[$discountId] = &$resultData['APPLY_BLOCKS'][$blockCounter]['ORDER'][$orderDiscountIndex[$blockCounter]];
					$orderDiscountIndex[$blockCounter]++;
				}

				$ruleItem = array(
					'RULE_ID' => $rule['ID'],
					'APPLY' => $rule['APPLY'],
					'RULE_DESCR_ID' => $rule['RULE_DESCR_ID'],
					'ACTION_BLOCK_LIST' => (!empty($rule['ACTION_BLOCK_LIST']) && is_array($rule['ACTION_BLOCK_LIST']) ? $rule['ACTION_BLOCK_LIST'] : null)
				);
				
				if (!empty($rule['RULE_DESCR']) && is_array($rule['RULE_DESCR']))
				{
					$ruleItem['DESCR_DATA'] = $rule['RULE_DESCR'];
					$ruleItem['DESCR'] = Sale\OrderDiscountManager::formatArrayDescription($rule['RULE_DESCR']);
					$ruleItem['DESCR_ID'] = $rule['RULE_DESCR_ID'];
				}

				switch ($rule['ENTITY_TYPE'])
				{
					case Internals\OrderRulesTable::ENTITY_TYPE_BASKET:
						$ruleItem['BASKET_ID'] = $rule['ENTITY_ID'];
						$index = $rule['ENTITY_ID'];
						if (!isset($orderDiscountLink[$discountId]['RESULT']['BASKET']))
							$orderDiscountLink[$discountId]['RESULT']['BASKET'] = array();

						$orderDiscountLink[$discountId]['RESULT']['BASKET'][$index] = $ruleItem;
						if ($ruleItem['ACTION_BLOCK_LIST'] === null)
							$orderDiscountLink[$discountId]['ACTION_BLOCK_LIST'] = false;

						$discountResultList['BASKET'][$ruleItem['BASKET_ID']][] = array(
							'DISCOUNT_ID' => $orderDiscountLink[$discountId]['DISCOUNT_ID'],
							'COUPON_ID' => $orderDiscountLink[$discountId]['COUPON_ID'],
							'APPLY' => $ruleItem['APPLY'],
							'DESCR' => $ruleItem['DESCR']
						);
						break;

					case Internals\OrderRulesTable::ENTITY_TYPE_DELIVERY:
						if (!isset($orderDiscountLink[$discountId]['RESULT']['DELIVERY']))
							$orderDiscountLink[$discountId]['RESULT']['DELIVERY'] = array();

						$ruleItem['DELIVERY_ID'] = ($rule['ENTITY_ID'] > 0 ? $rule['ENTITY_ID'] : (string)$rule['ENTITY_VALUE']);
						$orderDiscountLink[$discountId]['RESULT']['DELIVERY'] = $ruleItem;

						$discountResultList['DELIVERY'][] = array(
							'DISCOUNT_ID' => $orderDiscountLink[$discountId]['DISCOUNT_ID'],
							'COUPON_ID' => $orderDiscountLink[$discountId]['COUPON_ID'],
							'APPLY' => $orderDiscountLink[$discountId]['RESULT']['DELIVERY']['APPLY'],
							'DESCR' => $orderDiscountLink[$discountId]['RESULT']['DELIVERY']['DESCR'][0],
							'DELIVERY_ID' => $orderDiscountLink[$discountId]['RESULT']['DELIVERY']['DELIVERY_ID']
						);

						foreach ($orderDiscountData as $data)
						{
							if ((int)$data['DELIVERY_ID'] == (int)$orderDiscountLink[$discountId]['RESULT']['DELIVERY']['DELIVERY_ID'])
								$resultData['SHIPMENTS_ID'][] = (int)$data['SHIPMENT_ID'];
						}
						break;
				}
				unset($ruleItem, $discountId);
			}
			else
			{
				if ($rule['ENTITY_ID'] <= 0 || $rule['ENTITY_TYPE'] != Internals\OrderRulesTable::ENTITY_TYPE_BASKET)
					continue;

				$index = $rule['ENTITY_ID'];

				$ruleResult = array(
					'BASKET_ID' => $rule['ENTITY_ID'],
					'RULE_ID' => $rule['ID'],
					'ORDER_ID' => $rule['ORDER_ID'],
					'DISCOUNT_ID' => $rule['ORDER_DISCOUNT_ID'],
					'ORDER_COUPON_ID' => $rule['ORDER_COUPON_ID'],
					'COUPON_ID' => ($rule['ORDER_COUPON_ID'] > 0 ? $rule['COUPON_ID'] : ''),
					'RESULT' => array(
						'APPLY' => $rule['APPLY']
					),
					'RULE_DESCR_ID' => $rule['RULE_DESCR_ID'],
					'ACTION_BLOCK_LIST' => (isset($rule['ACTION_BLOCK_LIST']) ? $rule['ACTION_BLOCK_LIST'] : null)
				);

				if (!empty($rule['RULE_DESCR']) && is_array($rule['RULE_DESCR']))
				{
					$ruleResult['RESULT']['DESCR_DATA'] = $rule['RULE_DESCR'];
					$ruleResult['RESULT']['DESCR'] = Sale\OrderDiscountManager::formatArrayDescription($rule['RULE_DESCR']);
					$ruleResult['DESCR_ID'] = $rule['RULE_DESCR_ID'];
				}

				if (!isset($resultData['APPLY_BLOCKS'][$blockCounter]['BASKET'][$index]))
					$resultData['APPLY_BLOCKS'][$blockCounter]['BASKET'][$index] = array();
				$resultData['APPLY_BLOCKS'][$blockCounter]['BASKET'][$index][] = $ruleResult;

				$discountResultList['BASKET'][$index][] = array(
					'DISCOUNT_ID' => $ruleResult['DISCOUNT_ID'],
					'COUPON_ID' => $ruleResult['COUPON_ID'],
					'APPLY' => $ruleResult['RESULT']['APPLY'],
					'DESCR' => $ruleResult['RESULT']['DESCR']
				);

				unset($ruleResult);
			}
		}

		if (!empty($discountList))
		{
			$resultData['DISCOUNT_LIST'] = $this->prepareDiscountList($discountList);
		}

		$resultData['PRICES'] = $this->prepareDiscountPrices();
		$resultData['RESULT'] = $this->prepareDiscountResult($discountResultList);

		$this->order->setDiscountData($resultData);
		return;
	}

	/**
	 * Prepare discount price's array from restored entities.
	 *
	 * @return mixed
	 */
	protected function prepareDiscountPrices()
	{
		$resultData = array();
		$basket = $this->order->getBasket();
		$basketItems = $basket->getBasketItems();

		/** @var Sale\BasketItem $item */
		foreach ($basketItems as $item)
		{
			$resultData['BASKET'][$item->getId()] = array(
				'BASE_PRICE' => $item->getBasePrice(),
				'PRICE' => $item->getPrice(),
				'DISCOUNT' => $item->getDiscountPrice(),
			);
		}

		$shipmentCollection = $this->order->getShipmentCollection();

		/** @var Sale\Shipment $shipment */
		foreach ($shipmentCollection as $shipment)
		{
			if ($shipment->isSystem())
				continue;

			$resultData['DELIVERY'][$shipment->getId()] = array(
				'BASE_PRICE' => $shipment->getField("BASE_PRICE_DELIVERY"),
				'PRICE' => $shipment->getPrice(),
				'DISCOUNT' => $shipment->getField("BASE_PRICE_DELIVERY") - $shipment->getPrice(),
			);
		}

		return $resultData;
	}

	/**
	 * Prepare discount result's array from restored entities.
	 *
	 * @param $discountData
	 *
	 * @return mixed
	 */
	protected function prepareDiscountResult($discountData)
	{
		$resultData = array();
		$basket = $this->order->getBasket();
		$basketItems = $basket->getBasketItems();

		/** @var Sale\BasketItem $item */
		foreach ($basketItems as $item)
		{
			if (is_array($discountData['BASKET'][$item->getId()]))
			{
				$resultData['BASKET'][$item->getId()] = $discountData['BASKET'][$item->getId()];
			}
		}
		$resultData['DELIVERY'] = $discountData['DELIVERY'];

		return $resultData;
	}

	/**
	 * Prepare discount data
	 *
	 * @param $dataRow
	 *
	 * @return array
	 */
	protected function prepareDiscountOrderData($dataRow)
	{
		$resultData = array();

		foreach ($dataRow['ORDER_DATA'] as $data)
		{
			if (
				$data['ENTITY_TYPE'] = Internals\OrderDiscountDataTable::ENTITY_TYPE_ORDER
				&& $data['ENTITY_ID'] = $this->order->getId()
				&& isset($data['ENTITY_DATA']['DELIVERY']['SHIPMENT_ID'])
			)
			{
				$resultData['ORDER'][$data['ENTITY_DATA']['DELIVERY']['SHIPMENT_ID']] = $data['ENTITY_DATA']['DELIVERY'];
			}

			if ($data['ENTITY_TYPE'] == Internals\OrderDiscountDataTable::ENTITY_TYPE_BASKET)
			{
				if (!isset($resultData['DATA']['BASKET']))
					$resultData['BASKET'] = array();
				$data['ENTITY_ID'] = (int)$data['ENTITY_ID'];
				$resultData['BASKET'][$data['ENTITY_ID']] = $data['ENTITY_DATA'];
			}
		}
		return $resultData;
	}

	/**
	 * Prepare discount description array
	 *
	 * @param $discounts
	 *
	 * @return mixed
	 */
	protected function prepareDiscountList($discounts)
	{
		$resultData = array();

		foreach ($discounts as $discount)
		{
			$discount['ID'] = (int)$discount['ID'];
			$discount['ORDER_DISCOUNT_ID'] = $discount['ID'];
			$discount['DISCOUNT_ID'] = $discount['ID'];
			$discount['SIMPLE_ACTION'] = true;
			if (is_array($discount['ACTIONS_DESCR']['BASKET']))
			{
				foreach ($discount['ACTIONS_DESCR']['BASKET'] as &$description)
				{
					$description = Sale\OrderDiscountManager::formatDescription($description);
				}
			}

			if ($discount['MODULE_ID'] == 'sale')
			{
				$discount['EDIT_PAGE_URL'] = Sale\OrderDiscountManager::getEditUrl(array('ID' => $discount['DISCOUNT_ID']));
			}
			$resultData[$discount['ID']] = $discount;
		}

		return $resultData;
	}
}	