<?php

namespace Thurly\Disk\Volume\Module;

use Thurly\Main\Localization\Loc;
use Thurly\Disk\Volume;
use Thurly\Disk\Internals\ObjectTable;

/**
 * Disk storage volume measurement class.
 * @package Thurly\Disk\Volume
 */
class DiskTrashcan extends Volume\Module\Disk
{
	/**
	 * Runs measure test to get volumes of selecting objects.
	 * @param array $collectData List types data to collect: ATTACHED_OBJECT, SHARING_OBJECT, EXTERNAL_LINK, UNNECESSARY_VERSION.
	 * @return $this
	 */
	public function measure($collectData = array(self::DISK_FILE, self::PREVIEW_FILE))
	{
		$this->addFilter('!DELETED_TYPE', ObjectTable::DELETED_TYPE_NONE);

		parent::measure($collectData);

		return $this;
	}

	/**
	 * @param Volume\Fragment $fragment Module description structure.
	 * @return string
	 */
	public static function getTitle(Volume\Fragment $fragment)
	{
		Loc::loadMessages(__FILE__);
		return Loc::getMessage('DISK_VOLUME_MODULE_DISKTRASHCAN');
	}
}

