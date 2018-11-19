<?php

namespace Sale\Handlers\DiscountPreset;


use Thurly\Iblock\SectionTable;
use Thurly\Main;
use Thurly\Main\Error;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Basket;
use Thurly\Sale\Discount\Preset\ArrayHelper;
use Thurly\Sale\Discount\Preset\BasePreset;
use Thurly\Sale\Discount\Preset\HtmlHelper;
use Thurly\Sale\Discount\Preset\Manager;
use Thurly\Sale\Discount\Preset\State;
use Thurly\Sale\Helpers\Admin\OrderEdit;
use Thurly\Sale\Internals;
use Thurly\Sale\Helpers\Admin\Blocks;
use Thurly\Sale\Order;


Loc::loadMessages(__FILE__);

class FreeDelivery extends Delivery
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_FREEDELIVERY_NAME');
	}

	public function getDescription()
	{
		return '';
	}

	/**
	 * @return int
	 */
	public function getCategory()
	{
		return Manager::CATEGORY_DELIVERY;
	}

	protected function renderDiscountValue(State $state, $currency)
	{
		return '';
	}

	public function processSaveInputAmount(State $state)
	{
		$state['discount_type'] = 'Perc';
		$state['discount_value'] = '100';

		return parent::processSaveInputAmount($state);
	}
}