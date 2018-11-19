<?
namespace Thurly\Main\UI\Uploader;
/**
 * Class ErrorCatcher is used in main/tools/upload.php:16 to catch unknown server response.
 * @package Thurly\Main\UI\Uploader
 *
 */
class ErrorCatcher
{
	/**
	 * @param string $path URL where error was registered.
	 * @param string $errorText Error text.
	 * @return void
	 */
	public function log($path, $errorText)
	{
		if (check_thurly_sessid() &&
			is_string($path) &&
			is_string($errorText) &&
			\Thurly\Main\Config\Option::get("main", "uploaderLog", "N") == "Y")
		{
			trigger_error("Uploading error! Path: ".substr($path, 0, 100)."\n Text:".substr($errorText, 0, 500), E_USER_WARNING);
		}
	}
}