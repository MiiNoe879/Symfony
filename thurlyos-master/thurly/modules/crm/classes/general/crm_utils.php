<?php
class CCrmUtils
{
	private static $ENABLE_TRACING = false;
	public static function EnableTracing($enable)
	{
		self::$ENABLE_TRACING = $enable;
	}

	public static function Trace($id, $msg, $forced = false)
	{
		if(!$forced && !self::$ENABLE_TRACING)
		{
			return;
		}

		\Thurly\Main\Diag\Debug::writeToFile($msg, $id, 'crm.log');
	}

	public static function Dump($id, $obj)
	{
		\Thurly\Main\Diag\Debug::dump($obj, $id);
	}
}
