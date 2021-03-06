<?php
namespace Thurly\Sale\Archive\Recovery;

use Thurly\Main,
	Thurly\Sale;

abstract class Base
{
	/** @var Sale\Archive\Order $order */
	protected $order;

	/**
	 * Recovery\Base constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @param array $archivedOrder
	 * 
	 * @return Sale\Archive\Order
	 * 	 
	 * @throws Main\ArgumentNullException
	 */
	public function restoreOrder($archivedOrder = array())
	{
		if (!empty($archivedOrder))
		{
			return $this->loadOrder($archivedOrder);
		}
		else
		{
			throw new Main\ArgumentNullException('ORDER_DATA');
		}
	}

	abstract protected function loadOrder($archivedOrder);
}
