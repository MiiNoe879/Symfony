<?php

namespace Thurly\Disk\Search;


use Thurly\Disk\BaseObject;
use Thurly\Disk\Driver;
use Thurly\Disk\File;
use Thurly\Disk\Folder;
use Thurly\Disk\Internals\Error\ErrorCollection;
use Thurly\Disk\TypeFile;
use Thurly\Main\Config\Option;
use Thurly\Main\ModuleManager;

final class ContentManager
{
	/** @var  ErrorCollection */
	protected $errorCollection;

	/**
	 * Constructor IndexManager.
	 */
	public function __construct()
	{
		$this->errorCollection = new ErrorCollection;
	}

	/**
	 * Returns text content of object.
	 * If object is folder, then returns only name of folder. @see getFolderContent();
	 * If object is file, then returns content of document and name of file. @see getFileContent();
	 * @param BaseObject $object File or Folder.
	 * @return string
	 */
	public function getObjectContent(BaseObject $object)
	{
		if($object instanceof File)
		{
			return $this->getFileContent($object);
		}
		if($object instanceof Folder)
		{
			return $this->getFolderContent($object);
		}

		return '';
	}

	/**
	 * Returns content of folder.
	 * Now it is only folder name.
	 * @param Folder $folder Folder.
	 * @return string
	 */
	public function getFolderContent(Folder $folder)
	{
		return strip_tags($folder->getName()) . "\r\n";
	}

	/**
	 * Returns content of file. If it is office document, then manager try to extract text from document.
	 *
	 * @param File $file File.
	 * @return string
	 */
	public function getFileContent(File $file)
	{
		static $maxFileSize = null;
		if(!isset($maxFileSize))
		{
			$maxFileSize = (int)Option::get("search", "max_file_size", 0) * 1024;
		}

		$searchData = '';
		$searchData .= strip_tags($file->getName()) . "\r\n";
		$searchData .= strip_tags($file->getCreateUser()->getFormattedName()) . "\r\n";


		if($maxFileSize > 0 && $file->getSize() > $maxFileSize)
		{
			return $searchData;
		}

		$searchDataFile = array();
		$fileArray = null;

		//improve work with s3
		if(!ModuleManager::isModuleInstalled('thurlyos') || TypeFile::isDocument($file))
		{
			$fileArray = \CFile::makeFileArray($file->getFileId());
		}

		if($fileArray && $fileArray['tmp_name'])
		{
			$fileAbsPath = \CBXVirtualIo::getInstance()->getLogicalName($fileArray['tmp_name']);
			foreach(GetModuleEvents('search', 'OnSearchGetFileContent', true) as $event)
			{
				if($searchDataFile = executeModuleEventEx($event, array($fileAbsPath, $file->getExtension())))
				{
					break;
				}
			}

			return is_array($searchDataFile)? $searchData  . "\r\n" . $searchDataFile['CONTENT'] : $searchData;
		}

		return $searchData;
	}

	/**
	 * Returns file content which was stored in search index.
	 * @param File $file File.
	 *
	 * @return null|string
	 */
	public function getFileContentFromIndex(File $file)
	{
		$indexManager = Driver::getInstance()->getIndexManager();
		$storedIndex = $indexManager->getStoredIndex($file);

		if (empty($storedIndex['BODY']))
		{
			return null;
		}

		return $storedIndex['BODY'];
	}
}