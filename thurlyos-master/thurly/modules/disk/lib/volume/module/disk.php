<?php

namespace Thurly\Disk\Volume\Module;

use Thurly\Disk\Volume;

/**
 * Disk storage volume measurement class.
 * @package Thurly\Disk\Volume
 */
class Disk extends Volume\Module\Module
{
	/** @var string */
	protected static $moduleId = 'disk';

	/**
	 * Returns special folder code list.
	 * @return string[]
	 */
	public static function getSpecialFolderCode()
	{
		return array(
			\Thurly\Disk\Folder::CODE_FOR_CREATED_FILES,
			\Thurly\Disk\Folder::CODE_FOR_SAVED_FILES,
			\Thurly\Disk\Folder::CODE_FOR_UPLOADED_FILES,
		);
	}
}

