<?php
namespace Thurly\Main\Mail;

class StopException
	extends \Thurly\Main\SystemException
{
	protected $isBufferCleaned = false;

	public function __construct($message = "", $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, '', '', $previous);
	}
}
