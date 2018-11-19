<?php
namespace Thurly\Disk\Integration;

use Thurly\Main\Loader;

class BizProcManager
{
	/**
	 * Tells if module bizproc is available.
	 *
	 * @return bool
	 */
	public static function isAvailable()
	{
		return Loader::includeModule('bizproc') && \CBPRuntime::isFeatureEnabled();
	}
}