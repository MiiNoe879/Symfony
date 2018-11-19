<?php

namespace Thurly\Disk\Volume;

interface IClearConstraint
{
	/**
	 * Check ability to clear storage.
	 * @param \Thurly\Disk\Storage $storage Storage to clear.
	 * @return boolean
	 */
	public function isAllowClearStorage(\Thurly\Disk\Storage $storage);
}
