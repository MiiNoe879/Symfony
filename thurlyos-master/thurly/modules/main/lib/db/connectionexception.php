<?php
namespace Thurly\Main\DB;

/**
 * Class ConnectionException used to indicate errors during database connection process.
 *
 * @see \Thurly\Main\DB\ConnectionException::__construct
 * @package Thurly\Main\DB
 */
class ConnectionException extends Exception
{
	/**
	 * @param string $message Application message.
	 * @param string $databaseMessage Database reason.
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = "", $databaseMessage = "", \Exception $previous = null)
	{
		parent::__construct($message, $databaseMessage, $previous);
	}
}
