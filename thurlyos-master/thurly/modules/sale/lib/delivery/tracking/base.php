<?php

namespace Thurly\Sale\Delivery\Tracking;

use \Thurly\Sale\Delivery\Services;
use Thurly\Sale\Result;

/**
 * Class Base
 * @package Thurly\Sale\Delivery\Tracking
 *
 * Base class for shipment tracking services handlers
 */
abstract class Base
{
	/** @var array */
	protected $params;
	/** @var  Services\Base */
	protected $deliveryService;

	/**
	 * @param array $params
	 * @param Services\Base $deliveryService
	 */
	public function __construct(array $params, Services\Base $deliveryService)
	{
		$this->params = $params;
		$this->deliveryService = $deliveryService;
	}

	/**
	 * Returns class name for administration interface
	 * @return string
	 */
	abstract public function getClassTitle();

	/**
	 * Returns class description for administration interface
	 * @return string
	 */
	abstract public function getClassDescription();

	/**
	 * @param $trackingNumber
	 * @return \Thurly\Sale\Delivery\Tracking\StatusResult.
	 */
	public function getStatus($trackingNumber)
	{
		return new StatusResult();
	}

	/**
	 * @param string $trackingNumber
	 * @param array $shipmentData
	 * @return StatusResult
	 */
	public function getStatusShipment($shipmentData)
	{
		return $this->getStatus($shipmentData['TRACKING_NUMBER']);
	}

	/**
	 * @param string[] $trackingNumbers
	 * @return \Thurly\Sale\Result.
	 */
	public function getStatuses(array $trackingNumbers)
	{
		return new Result();
	}

	/**
	 * @param array $shipmentsData
	 * @return \Thurly\Sale\Result
	 */
	public function getStatusesShipment(array $shipmentsData)
	{
		$trackingNumbers = array_keys($shipmentsData);
		return $this->getStatuses($trackingNumbers);
	}

	/**
	 * Returns params structure
	 * @return array
	 */
	abstract public function getParamsStructure();

	/**
	 * @param string $paramKey
	 * @param string $inputName
	 * @return string Html
	 * @throws \Thurly\Main\SystemException
	 */
	public function getEditHtml($paramKey, $inputName)
	{
		$paramsStructure = $this->getParamsStructure();

		return \Thurly\Sale\Internals\Input\Manager::getEditHtml(
			$inputName,
			$paramsStructure[$paramKey],
			$this->params[$paramKey]
		);
	}

	/**
	 * @param string $trackingNumber
	 * @return string Url were we can see tracking information
	 */
	public function getTrackingUrl($trackingNumber = '')
	{
		return '';
	}
}