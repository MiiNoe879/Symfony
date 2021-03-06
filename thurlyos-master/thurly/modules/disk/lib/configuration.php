<?php


namespace Thurly\Disk;


use Thurly\Disk\Document\GoogleViewerHandler;
use Thurly\Disk\Integration\ThurlyOSManager;
use Thurly\Main\Config\Option;
use Thurly\Main\Loader;

final class Configuration
{
	const REVISION_API = 6;

	public static function isEnabledDefaultEditInUf()
	{
		static $isAllow = null;
		if($isAllow === null)
		{
			$isAllow = 'Y' == Option::get(Driver::INTERNAL_MODULE_ID, 'disk_allow_edit_object_in_uf', 'Y');
		}
		return $isAllow;
	}

	public static function isEnabledKeepVersion()
	{
		static $isAllow = null;
		if($isAllow === null)
		{
			$isAllow = 'Y' == Option::get(Driver::INTERNAL_MODULE_ID, 'disk_keep_version', 'Y');
		}
		return $isAllow;
	}

	public static function isEnabledStorageSizeRestriction()
	{
		static $isAllow = null;
		if($isAllow === null)
		{
			$isAllow = 'Y' == Option::get(Driver::INTERNAL_MODULE_ID, 'disk_restriction_storage_size_enabled', 'N');
		}
		return $isAllow;
	}

	public static function getVersionLimitPerFile()
	{
		$value = (int)Option::get(Driver::INTERNAL_MODULE_ID, 'disk_version_limit_per_file', 0);

		return $value?: null;
	}
	
	public static function isEnabledExternalLink()
	{
		static $isAllow = null;
		if($isAllow === null)
		{
			$isAllow = 'Y' == Option::get(Driver::INTERNAL_MODULE_ID, 'disk_allow_use_external_link', 'Y');
		}
		return $isAllow;
	}

	public static function isEnabledObjectLock()
	{
		static $isAllow = null;
		if($isAllow === null)
		{
			$isAllow = 'Y' == Option::get(Driver::INTERNAL_MODULE_ID, 'disk_object_lock_enabled', 'N');
		}
		return $isAllow;
	}

	public static function getDocumentServiceCodeForCurrentUser()
	{
		return UserConfiguration::getDocumentServiceCode();
	}

	public static function canCreateFileByCloud()
	{
		static $isAllow = null;
		if($isAllow === null)
		{
			$isAllow = 'Y' == Option::get(Driver::INTERNAL_MODULE_ID, 'disk_allow_create_file_by_cloud', 'Y');
		}
		return $isAllow;
	}

	public static function canAutoConnectSharedObjects()
	{
		static $isAllow = null;
		if($isAllow === null)
		{
			$isAllow = 'Y' == Option::get(Driver::INTERNAL_MODULE_ID, 'disk_allow_autoconnect_shared_objects', 'N');
		}
		return $isAllow;
	}

	public static function isSuccessfullyConverted()
	{
		return Option::get(
			Driver::INTERNAL_MODULE_ID,
			'successfully_converted',
			false
		) == 'Y';
	}

	public static function getRevisionApi()
	{
		return Option::get(
			Driver::INTERNAL_MODULE_ID,
			'disk_revision_api',
			0
		);
	}

	public static function allowIndexFiles()
	{
		return Option::get(
			Driver::INTERNAL_MODULE_ID,
			'disk_allow_index_files',
			'Y'
		) == 'Y';
	}

	public static function getDefaultViewerServiceCode()
	{
		static $service = null;
		if ($service !== null)
		{
			return $service;
		}

		$service = Option::get(Driver::INTERNAL_MODULE_ID, 'default_viewer_service', GoogleViewerHandler::getCode());

		return $service;
	}

	public static function allowDocumentTransformation()
	{
		return Option::get(
				Driver::INTERNAL_MODULE_ID,
				'disk_allow_document_transformation',
				'N'
			) == 'Y';
	}

	public static function getMaxSizeForDocumentTransformation()
	{
		return intval(Option::get(Driver::INTERNAL_MODULE_ID, 'disk_max_size_for_document_transformation', 40)) * 1024 * 1024;
	}

	public static function allowVideoTransformation()
	{
		return Option::get(
				Driver::INTERNAL_MODULE_ID,
				'disk_allow_video_transformation',
				'N'
			) == 'Y';
	}

	/**
	 * Returns maximum size (in bytes) of video files that could be transformed.
	 *
	 * @return int
	 */
	public static function getMaxSizeForVideoTransformation()
	{
		if(ThurlyOSManager::isEnabled() && ThurlyOSManager::isLicensePaid())
		{
			return intval(Option::get(Driver::INTERNAL_MODULE_ID, 'disk_max_size_for_video_transformation_paid', 3072)) * 1024 * 1024;
		}
		return intval(Option::get(Driver::INTERNAL_MODULE_ID, 'disk_max_size_for_video_transformation', 300)) * 1024 * 1024;
	}

	public static function allowTransformFilesOnOpen()
	{
		static $allow = null;
		if ($allow !== null)
		{
			return $allow;
		}
		$allow = Option::get(
				Driver::INTERNAL_MODULE_ID,
				'disk_transform_files_on_open',
				'N'
			) == 'Y';

		return $allow;
	}
}

/**
 * Class UserConfiguration
 * Represents configuration for current user
 * @package Thurly\Disk
 */
final class UserConfiguration
{
	public static function resetDocumentServiceCode()
	{
		\CUserOptions::setOption(Driver::INTERNAL_MODULE_ID, 'doc_service', array('default' => ''));
	}

	public static function getDocumentServiceCode()
	{
		global $USER;
		static $service = null;

		if ($service !== null || !$USER instanceof \CUser || !$USER->getId() )
		{
			return $service;
		}
		/** @noinspection PhpParamsInspection */
		$userSettings = \CUserOptions::getOption(Driver::INTERNAL_MODULE_ID, 'doc_service', array('default' => ''));
		if(empty($userSettings['default']))
		{
			$userSettings['default'] = '';
		}
		$service = $userSettings['default'];

		return $userSettings['default'];
	}
}