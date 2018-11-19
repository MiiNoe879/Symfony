<?php

namespace Thurly\Disk\Volume;

interface IVolumeIndicatorStorage
{
	/**
	 * Gets available disk space. Units ara bytes.
	 * @param \Thurly\Disk\Storage|null $storage Storage entity object.
	 * @return int
	 */
	public static function getAvailableSpace(\Thurly\Disk\Storage $storage = null);

	/**
	 * Returns entity type list.
	 * @return string[]
	 */
	public static function getEntityType();
}
