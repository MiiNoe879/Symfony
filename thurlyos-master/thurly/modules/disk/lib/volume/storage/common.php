<?php

namespace Thurly\Disk\Volume\Storage;

use Thurly\Main\ArgumentTypeException;
use Thurly\Disk\Internals\ObjectTable;
use Thurly\Disk\Volume;


/**
 * Disk storage volume measurement class.
 * @package Thurly\Disk\Volume
 */
class Common extends Volume\Storage\Storage
{
	/**
	 * Returns entity type list.
	 * @return string[]
	 */
	public static function getEntityType()
	{
		$entityTypes = array(\Thurly\Disk\ProxyType\Common::className());

		$entityTypes = array_merge($entityTypes, \Thurly\Disk\Volume\Module\Im::getEntityType());

		return $entityTypes;
	}

	/**
	 * Runs measure test to get volumes of selecting objects.
	 * @param array $collectData List types data to collect: ATTACHED_OBJECT, SHARING_OBJECT, EXTERNAL_LINK, UNNECESSARY_VERSION.
	 * @return $this
	 */
	public function measure($collectData = array(self::DISK_FILE, self::PREVIEW_FILE, self::UNNECESSARY_VERSION))
	{
		$this->addFilter('@ENTITY_TYPE', self::getEntityType());

		parent::measure($collectData);

		return $this;
	}

	/**
	 * @param Volume\Fragment $fragment Storage entity object.
	 * @return string
	 * @throws ArgumentTypeException
	 */
	public static function getUrl(Volume\Fragment $fragment)
	{
		$storage = $fragment->getStorage();
		if (!$storage instanceof \Thurly\Disk\Storage)
		{
			throw new ArgumentTypeException('Fragment must be subclass of '.\Thurly\Disk\Storage::className());
		}

		$url = $storage->getProxyType()->getStorageBaseUrl();

		$testUrl = trim($url, '/');
		if (
			$testUrl == '' ||
			$testUrl == \Thurly\Disk\ProxyType\Base::SUFFIX_DISK
		)
		{
			return null;
		}

		return $url;
	}
}

