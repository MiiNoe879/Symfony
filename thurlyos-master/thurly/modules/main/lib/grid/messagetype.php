<?

namespace Thurly\Main\Grid;


/**
 * Class MessageType. Types of grid message
 * @package Thurly\Main\Grid
 */
class MessageType
{
	const MESSAGE = "MESSAGE";
	const ERROR = "ERROR";
	const WARNING = "WARNING";
	const INFO = "INFO";
	const SUCCESS = "SUCCESS";


	/**
	 * Gets types list
	 * @return array
	 */
	public static function getList()
	{
		$reflection = new \ReflectionClass(__CLASS__);
		return $reflection->getConstants();
	}
}