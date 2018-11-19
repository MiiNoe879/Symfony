<?php

namespace Thurly\Sale\Basket;

use Thurly\Sale\BasketBase;

class FullRefreshStrategy extends RefreshStrategy
{
	protected function getProductData(BasketBase $basket)
	{
		$itemsToRefresh = $this->getBasketItemsToRefresh($basket);

		return $this->getProviderResult($basket, $itemsToRefresh);
	}
}