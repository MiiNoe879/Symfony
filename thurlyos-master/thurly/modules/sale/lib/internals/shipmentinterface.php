<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage sale
 * @copyright 2001-2014 Thurly
 */

interface IShipmentOrder
{
	public function getShipmentCollection();
	public function loadShipmentCollection();
}