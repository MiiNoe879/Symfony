<?php

namespace Thurly\Sale\Helpers\Admin\Blocks\Archive\View1;

use Thurly\Sale\Helpers\Admin\Blocks,
	Thurly\Sale\Helpers\Admin\Blocks\Archive\Template;

class OrderFinanceInfo extends Template
{
	protected $name = "financeinfo";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		return Blocks\OrderFinanceInfo::getView($this->order, false);
	}
}