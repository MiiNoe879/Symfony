<?php
namespace Thurly\Sale\Internals;

/**
 * Class ItemsPool
 * @package Thurly\Sale\Internals
 */
class ItemsPool extends PoolBase
{
	protected static $pool = array();
//	public static function get($code, $providerName, $productId)
//	{
//		$hash = $providerName."|".$productId;
//		if (isset(static::$pool[$code][$type]))
//		{
//			return static::$pool[$code][$type];
//		}
//
//		return null;
//	}
//
//	/**
//	 * @param $code
//	 * @param $type
//	 * @param $value
//	 */
//	public static function add($code, $type, $value)
//	{
//		static::$pool[$code][$type][] = $value;
//	}
}
