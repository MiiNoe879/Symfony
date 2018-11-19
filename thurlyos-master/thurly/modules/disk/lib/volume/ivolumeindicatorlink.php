<?php

namespace Thurly\Disk\Volume;
use \Thurly\Disk\Volume;

interface IVolumeIndicatorLink
{
	/**
	 * @param Volume\Fragment $fragment Entity object.
	 * @return string
	 */
	public static function getUrl(Volume\Fragment $fragment);
}
