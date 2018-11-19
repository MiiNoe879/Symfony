<?php

namespace Thurly\Sale\Helpers\Admin\Blocks\Archive\View1;

use Thurly\Sale\Helpers\Admin\Blocks,
	Thurly\Sale\Helpers\Admin\Blocks\Archive\Template;

class OrderBasket extends Template
{
	protected $name = "basket";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		$result = "";
		$tablePrefix = "sale_order_basket";
		$orderBasket = new Blocks\OrderBasket(
			$this->order,
			"BX.Sale.Admin.OrderBasketObj",
			$tablePrefix,
			true,
			Blocks\OrderBasket::VIEW_MODE
		);

		$result .= $orderBasket->getView();
		$result .= '<script>
						var row = BX("'.$tablePrefix.'sale-adm-order-basket-loading-row");
						if (row)
							row.style.display = "none";
					</script>';
		return $result;
	}
}