<?php

namespace Thurly\Sale\TradingPlatform\Ebay\Feed\Data\Processors;

abstract class DataProcessor
{
	abstract public function process($data);
}