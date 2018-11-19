<?php

namespace Thurly\Disk\ZipNginx;


use Thurly\Disk\Driver;
use Thurly\Main\Config\Option;
use Thurly\Main\Web\HttpClient;

final class Configuration
{
	const TEST_URL = '/thurly/services/disk/index.php?action=downloadTestZipArchive';

	/**
	 * Returns true if work with mod_zip is enabled.
	 *
	 * @return bool
	 * @throws \Thurly\Main\ArgumentNullException
	 */
	public static function isEnabled()
	{
		return Option::get(Driver::INTERNAL_MODULE_ID, 'disk_nginx_mod_zip_enabled', 'N') === 'Y';
	}

	/**
	 * Disables work with mod_zip.
	 *
	 * @return void
	 */
	public static function disable()
	{
		Option::set(Driver::INTERNAL_MODULE_ID, 'disk_nginx_mod_zip_enabled', 'N');
	}

	/**
	 * Enables work with mod_zip.
	 *
	 * @return void
	 */
	public static function enable()
	{
		Option::set(Driver::INTERNAL_MODULE_ID, 'disk_nginx_mod_zip_enabled', 'Y');
	}

	/**
	 * Checks real opportunity on the current server to send archive by mod_zip.
	 *
	 * @return bool
	 */
	public static function isModInstalled()
	{
		$http = new HttpClient(array(
			'socketTimeout' => 5,
			'streamTimeout' => 5,
			'version' => HttpClient::HTTP_1_1,
		));

		if($http->get(Driver::getInstance()->getUrlManager()->getHostUrl() . static::TEST_URL) === false)
		{
			return false;
		}

		if($http->getStatus() != '200')
		{
			return false;
		}

		$contentType = $http->getHeaders()->getContentType();

		if(!$contentType || !is_string($contentType))
		{
			return false;
		}

		return strpos($contentType, 'application/zip') !== false;
	}
}