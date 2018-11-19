<?php

namespace Thurly\Disk\Volume;

use Thurly\Main;
use Thurly\Disk\Internals\ObjectTable;
use Thurly\Disk\Volume;

/**
 * Disk storage volume measurement class.
 * @package Thurly\Disk\Volume
 */
class FolderDeleted extends Volume\Folder
{
	/**
	 * Runs measure test to get volumes of selecting objects.
	 * @param array $collectData List types data to collect: ATTACHED_OBJECT, SHARING_OBJECT, EXTERNAL_LINK, UNNECESSARY_VERSION.
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function measure($collectData = array(self::DISK_FILE, self::PREVIEW_FILE, self::UNNECESSARY_VERSION))
	{
		$this->addFilter('!DELETED_TYPE', ObjectTable::DELETED_TYPE_NONE);

		parent::measure($collectData);

		return $this;
	}
}
