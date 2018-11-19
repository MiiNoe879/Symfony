<?php
namespace Thurly\Main\Diag;


class CacheTracker
{
	private static $cacheStatBytes = 0;
	private static $arCacheDebug = array();

	private static $skipUntil = array(
		"thurly\\main\\diag\\cachetracker::add" => true,
		"thurly\\main\\data\\cache->initcache" => true,
		"thurly\\main\\data\\cache->startdatacache" => true,
		"thurly\\main\\data\\cache->enddatacache" => true,
		"thurly\\main\\data\\cache->clean" => true,
		"thurly\\main\\data\\cache->cleandir" => true,
		"thurly\\main\\data\\managedcache->read" => true,
		"thurly\\main\\data\\managedcache->setimmediate" => true,
		"thurly\\main\\data\\managedcache->clean" => true,
		"thurly\\main\\data\\managedcache->cleandir" => true,
		"thurly\\main\\data\\managedcache->cleanall" => true,
		"cthurlycomponent->startresultcache" => true,
	);

	/**
	 * @param int $cacheStatBytes
	 */
	public static function setCacheStatBytes($cacheStatBytes)
	{
		self::$cacheStatBytes = $cacheStatBytes;
	}

	public static function addCacheStatBytes($cacheStatBytes)
	{
		self::$cacheStatBytes += $cacheStatBytes;
	}

	public static function getCacheStatBytes()
	{
		return self::$cacheStatBytes;
	}

	public static function add($size, $path, $baseDir, $initDir, $filename, $operation)
	{
		$prev = array();
		$found = -1;
		foreach (Helper::getBackTrace(8) as $tr)
		{
			$func = $tr["class"].$tr["type"].$tr["function"];

			if ($found < 0 && !isset(self::$skipUntil[strtolower($func)]))
			{
				$found = count(self::$arCacheDebug);
				self::$arCacheDebug[$found] = array(
					"TRACE" => array(),
					"path" => $path,
					"basedir" => $baseDir,
					"initdir" => $initDir,
					"filename" => $filename,
					"cache_size" => $size,
					"callee_func" => $prev["class"].$prev["type"].$prev["function"],
					"operation" => $operation,
				);
				self::$arCacheDebug[$found]["TRACE"][] = array(
					"func" => $prev["class"].$prev["type"].$prev["function"],
					"args" => array(),
					"file" => $prev["file"],
					"line" => $prev["line"],
				);
			}

			if ($found > -1)
			{
				if (count(self::$arCacheDebug[$found]["TRACE"]) < 8)
				{
					$args = array();
					if (is_array($tr["args"]))
					{
						foreach ($tr["args"] as $k1 => $v1)
						{
							if (is_array($v1))
							{
								foreach ($v1 as $k2 => $v2)
								{
									if (is_scalar($v2))
										$args[$k1][$k2] = $v2;
									elseif (is_object($v2))
										$args[$k1][$k2] = get_class($v2);
									else
										$args[$k1][$k2] = gettype($v2);
								}
							}
							else
							{
								if (is_scalar($v1))
									$args[$k1] = $v1;
								elseif (is_object($v1))
									$args[$k1] = get_class($v1);
								else
									$args[$k1] = gettype($v1);
							}
						}
					}

					self::$arCacheDebug[$found]["TRACE"][] = array(
						"func" => $func,
						"args" => $args,
						"file" => $tr["file"],
						"line" => $tr["line"],
					);
				}
				else
				{
					break;
				}
			}
			$prev = $tr;
		}
	}

	public static function getCacheTracking()
	{
		return static::$arCacheDebug;
	}

	public static function setCacheTracking($val)
	{
		static::$arCacheDebug = $val;
	}
}