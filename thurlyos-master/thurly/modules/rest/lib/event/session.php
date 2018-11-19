<?php
namespace Thurly\Rest\Event;

/**
 * Class Session
 *
 * Session restriction for REST events
 *
 * @package Thurly\Rest
 **/
class Session
{
	const PARAM_SESSION = 'EVENT_SESSION';

	private static $SID = null;
	private static $TTL = null;

	private static $ttlDecreased = false;
	private static $set = false;

	public static function get()
	{
		if(!self::$SID || !self::$set)
		{
			self::$SID = md5(uniqid(rand(), true));
			self::$TTL = \CRestUtil::HANDLER_SESSION_TTL;
			self::$ttlDecreased = true;
		}
		else
		{
			if(!self::$ttlDecreased)
			{
				self::$TTL--;
				self::$ttlDecreased = true;
			}
		}

		return self::$TTL <= 0 ? false : array(
			'SID' => self::$SID,
			'TTL' => self::$TTL,
		);
	}

	public static function set($session)
	{
		self::$SID = $session['SID'];
		self::$TTL = $session['TTL'];

		self::$ttlDecreased = false;
		self::$set = true;
	}
}