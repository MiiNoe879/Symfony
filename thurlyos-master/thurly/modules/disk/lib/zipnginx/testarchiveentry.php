<?php

namespace Thurly\Disk\ZipNginx;

use Thurly\Disk\AttachedObject;
use Thurly\Disk\File;

class TestArchiveEntry extends ArchiveEntry
{
	const TEST_FILE = '/thurly/images/disk/is.png';

	protected function __construct()
	{
		parent::__construct();

		$this->path = static::TEST_FILE;
		$this->name = 'is.png';
		$this->size = 0;
	}

	/**
	 * Creates test entry.
	 *
	 * @return static
	 */
	public static function create()
	{
		return new static;
	}

	/**
	 * Creates test entry.
	 *
	 * @param File $file File.
	 * @return static
	 */
	public static function createFromFile(File $file)
	{
		return new static;
	}

	/**
	 * Creates test entry.
	 *
	 * @param AttachedObject $attachedObject Attached object.
	 * @return static
	 */
	public static function createFromAttachedObject(AttachedObject $attachedObject)
	{
		return new static;
	}

	/**
	 * Creates test entry.
	 *
	 * @param array $fileArray Array of file from b_file.
	 * @param string $name Name of file.
	 * @return static
	 * @throws \Thurly\Main\ArgumentNullException
	 */
	public static function createFromFileArray(array $fileArray, $name)
	{
		return new static;
	}
}