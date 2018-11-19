<?php

namespace Thurly\Disk\Volume;
use \Thurly\Disk\Volume;

interface IVolumeIndicatorParent
{
	/**
	 * @param Volume\Fragment $fragment Entity object.
	 * @return string[]
	 */
	public static function getParents(Volume\Fragment $fragment);
}
