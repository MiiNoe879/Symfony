<?php
namespace Thurly\Sale\Cashbox\Internals;

use Thurly\Sale\Cashbox\CheckManager;
use Thurly\Sale\Cashbox\Manager;
use Thurly\Sale\Result;

class Pool
{
	protected static $docs = array();

	/**
	 * @param $code
	 * @return mixed|null
	 */
	public static function getDocs($code)
	{
		if (isset(static::$docs[$code]))
		{
			return static::$docs[$code];
		}

		return null;
	}

	/**
	 * @param $code
	 * @param $doc
	 */
	public static function addDoc($code, $doc)
	{
		static::$docs[$code][] = $doc;
	}

	/**
	 * @param null $code
	 */
	public static function resetDocs($code = null)
	{
		if ($code !== null)
		{
			unset(static::$docs[$code]);
		}
		else
		{
			static::$docs = array();
		}
	}

	/**
	 * @param $code
	 * @return Result
	 */
	public static function generateChecks($code)
	{
		$result = new Result();

		$docs = static::getDocs($code);
		if (!$docs)
			return $result;

		$result = CheckManager::addChecks($docs);

		static::resetDocs($code);

		return $result;
	}
}
