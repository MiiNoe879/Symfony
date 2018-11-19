<?php
namespace Thurly\ImConnector;

use \Thurly\Main\IO\File,
	\Thurly\Main\Config\Option;

/**
 * Class for logging.
 * @package Thurly\ImConnector
 */
class Log
{
	/**
	 * Writes data to the log.
	 *
	 * @param mixed $data Data for logging.
	 * @param string $prefix The prefix of the log file.
	 * @param string $title The header of each record of the log.
	 * @return bool
	 * @throws \Thurly\Main\ArgumentNullException
	 */
	public static function write($data, $prefix = 'debug', $title = '')
	{
		if (Option::get(Library::MODULE_ID, "debug") == "Y")
		{
			$log = "\n------------------------\n";
			$log .= date("Y.m.d G:i:s")."\n";
			if(!empty($title))
				$log .= $title."\n";
			$log .= print_r($data, true);
			$log .= "\n------------------------\n";

			if (function_exists('BXSiteLog'))
			{
				BXSiteLog(Library::MODULE_ID . "_" . $prefix . ".log", $log);
			}
			else
			{
				File::putFileContents($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/" . Library::MODULE_ID . "_" . $prefix . ".log", $log, File::APPEND);
			}

			return true;
		}
		else
		{
			return false;
		}
	}
}