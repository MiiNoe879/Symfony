<?php

namespace Thurly\Sale\Helpers\Admin\Blocks\Archive\View1;

use Thurly\Sale\Helpers\Admin\Blocks,
	Thurly\Sale\Helpers\Admin\Blocks\Archive\Template;

class OrderBuyer extends Template
{
	protected $name = "buyer";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		return Blocks\OrderBuyer::getView($this->order);
	}
}