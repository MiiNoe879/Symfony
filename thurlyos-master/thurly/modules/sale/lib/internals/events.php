<?php

namespace Thurly\Sale\Internals;

use Thurly\Main;
use Thurly\Sale;

class Events
{
	/**
	 * @internal
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleBasketItemEntitySaved(Main\Event $event)
	{
		return Sale\BasketComponentHelper::onSaleBasketItemEntitySaved($event);
	}

	/**
	 * @internal
	 * @param Main\Event $event
	 *
	 * @return Main\EventResult
	 */
	public static function onSaleBasketItemDeleted(Main\Event $event)
	{
		return Sale\BasketComponentHelper::onSaleBasketItemDeleted($event);
	}
}