<?php

namespace Thurly\Disk\Volume\Storage;

use Thurly\Main\DB;
use Thurly\Disk\Internals\ObjectTable;
use Thurly\Disk\ProxyType;
use Thurly\Disk\Volume;


/**
 * Disk storage volume measurement class.
 * @package Thurly\Disk\Volume
 */
class Group extends Volume\Storage\Storage
{
	/**
	 * Returns entity type list.
	 * @return string[]
	 */
	public static function getEntityType()
	{
		return array(ProxyType\Group::className());
	}

	/**
	 * Runs measure test to get volumes of selecting objects.
	 * @param array $collectData List types data to collect: ATTACHED_OBJECT, SHARING_OBJECT, EXTERNAL_LINK, UNNECESSARY_VERSION.
	 * @return $this
	 */
	public function measure($collectData = array(self::DISK_FILE, self::PREVIEW_FILE, self::UNNECESSARY_VERSION))
	{
		$this
			->addFilter('@ENTITY_TYPE', self::getEntityType())
			->addSelect('GROUP_ID', 'storage.ENTITY_ID')
		;

		parent::measure($collectData);

		return $this;
	}

	/**
	 * Returns calculation result set.
	 * @param array $collectedData List types of collected data to return.
	 * @return DB\Result
	 */
	public function getMeasurementResult($collectedData = array())
	{
		$this->addFilter('!GROUP_ID', null);
		return parent::getMeasurementResult($collectedData);
	}
}

