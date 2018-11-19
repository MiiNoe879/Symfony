<?php

namespace Thurly\Im\Disk\ProxyType;

use Thurly\Disk\Driver;
use Thurly\Disk\Security\DiskSecurityContext;
use Thurly\Disk\Security\SecurityContext;
use Thurly\Main\Localization\Loc;
use Thurly\Disk\ProxyType;
Loc::loadMessages(__FILE__);

class Im extends ProxyType\Base
{

	/**
	 * @param $user
	 * @return SecurityContext
	 */
	public function getSecurityContextByUser($user)
	{
		return new DiskSecurityContext($user);
	}

	/**
	 * @inheritdoc
	 */
	public function getStorageBaseUrl()
	{
		return '/';
	}

	/**
	 * @inheritdoc
	 */
	public function getEntityUrl()
	{
		return '/';
	}

	/**
	 * @inheritdoc
	 */
	public function getEntityTitle()
	{
		return Loc::getMessage('IM_DISK_STORAGE_TITLE');
	}

	/**
	 * @inheritdoc
	 */
	public function getEntityImageSrc($width, $height)
	{
		return '/thurly/js/im/images/blank.gif';
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle()
	{
		return $this->getEntityTitle();
	}
}