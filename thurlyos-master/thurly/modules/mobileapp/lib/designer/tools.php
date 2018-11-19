<?php
namespace Thurly\MobileApp\Designer;


use Thurly\Main\Application;
use Thurly\Main\Config\Option;
use Thurly\Main\IO\File;

class Tools
{
	private static $jsMobileCorePath = "/thurly/cache/js/mobileapp_designer/mobile_core.js";

	public static function getMobileJSCorePath()
	{
		self::generateMobileJSFile();
		return self::$jsMobileCorePath;
	}

	private static function generateMobileJSFile()
	{
		$lastModificationHash = Option::get("mobileapp","mobile_core_modification","");
		$coreMobileFileList = array(
			"/thurly/js/main/core/core.js",
			"/thurly/js/main/core/core_ajax.js",
			"/thurly/js/main/core/core_db.js",
			"/thurly/js/mobileapp/thurly_mobile.js",
			"/thurly/js/mobileapp/mobile_lib.js"
		);

		$modificationHash = self::getArrayFilesHash($coreMobileFileList);

		$coreFile = new File(Application::getDocumentRoot().self::$jsMobileCorePath);

		if($modificationHash == $lastModificationHash && $coreFile->isExists())
			return;

		CheckDirPath(Application::getDocumentRoot()."/thurly/cache/js/mobileapp_designer/");

		$content = "";
		foreach ($coreMobileFileList as $filePath)
		{
			$file = new \Thurly\Main\IO\File(Application::getDocumentRoot().$filePath);
			if($file->isExists())
			{
				$fileContent = $file->getContents();
				$content.="\n\n".$fileContent;

			}
		}


		$coreFile->open("w+");
		$coreFile->putContents($content);
		$coreFile->close();

		Option::set("mobileapp","mobile_core_modification", $modificationHash);

	}

	public static function getArrayFilesHash($fileList = array())
	{
		$fileModificationString = "";
		foreach ($fileList as $item)
		{
			$file = new File(Application::getDocumentRoot().$item);
			$fileModificationString .= $item."|";
			if($file->isExists())
			{
				$file->getModificationTime();
				$fileModificationString .= "|".$file->getModificationTime();
			}	
		}

		return md5($fileModificationString);
	}


}