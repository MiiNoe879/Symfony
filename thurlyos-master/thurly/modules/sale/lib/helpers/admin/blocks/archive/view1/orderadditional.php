<?php

namespace Thurly\Sale\Helpers\Admin\Blocks\Archive\View1;

use Thurly\Sale\Helpers\Admin\Blocks,
	Thurly\Sale\Helpers\Admin\Blocks\Archive\Template;

class OrderAdditional extends Template
{
	protected $name = "additional";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		return Blocks\OrderAdditional::getView($this->order, "archive");
	}
}