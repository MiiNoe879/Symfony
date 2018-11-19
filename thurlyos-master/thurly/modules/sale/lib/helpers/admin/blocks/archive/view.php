<?php
namespace Thurly\Sale\Helpers\Admin\Blocks\Archive;

use Thurly\Main,
	Thurly\Sale,
	Thurly\Sale\Archive\Manager;

class View
{
	/** @var Sale\Archive\Order  */
	protected $order;

	/**
	 * View constructor.
	 *
	 * @param int $id
	 */
	function __construct($id)
	{
		$id = (int)$id;
		$this->order = Manager::returnArchivedOrder($id);
	}

	/**
	 * @return Sale\Order
	 */
	public function loadOrder()
	{
		return $this->order;
	}

	/**
	 * Return blocks appropriate archive version.
	 *
	 * @return array
	 *
	 * @throws Main\ObjectNotFoundException
	 */
	public function getTemplates()
	{
		$schema = __NAMESPACE__."\\View".$this->order->getVersion()."\\Schema";
		if (class_exists($schema))
		{
			/** @var Schema $view */
			$view = new $schema();

			return $view->getBlocks($this->order);
		}
		return array();
	}
}