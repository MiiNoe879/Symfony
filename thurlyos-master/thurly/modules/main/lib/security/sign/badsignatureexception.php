<?php
namespace Thurly\Main\Security\Sign;

use Thurly\Main\SystemException;

/**
 * Class BadSignatureException
 * @since 14.0.7
 * @package Thurly\Main\Security\Sign
 */
class BadSignatureException
	extends SystemException
{
	/**
	 * Creates new exception object for signing purposes.
	 *
	 * @param string $message Message.
	 * @param \Exception $previous Previous exception.
	 */
	public function __construct($message = "", \Exception $previous = null)
	{
		parent::__construct($message, 140, '', 0, $previous);
	}
}