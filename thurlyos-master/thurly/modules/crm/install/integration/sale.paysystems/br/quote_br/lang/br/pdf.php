<?
use Thurly\Main\IO\Path;
use Thurly\Main\IO\File;

$filePath = Path::getDirectory(Path::normalize(__FILE__)).Path::DIRECTORY_SEPARATOR.'html.php';
if (File::isFileExists($filePath))
	include($filePath);
