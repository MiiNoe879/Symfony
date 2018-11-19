<?php

namespace Thurly\Sale\TradingPlatform\Ebay\Feed\Data\Converters;

use Thurly\Sale\TradingPlatform\Xml2Array;

class Order extends DataConverter
{
	public function convert($data)
	{
		$result = Xml2Array::convert($data, false);
		return isset($result["Order"]) && !empty($result["Order"]) ? Xml2Array::normalize($result["Order"]) : array();
	}
}