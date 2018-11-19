<?php
namespace Thurly\Sale;
use Thurly\Main\SystemException;

class UserMessageException extends SystemException
{
	public function __construct($message = "", \Exception $previous = null)
	{
		parent::__construct($message, 0, '', 0, $previous);
	}
}