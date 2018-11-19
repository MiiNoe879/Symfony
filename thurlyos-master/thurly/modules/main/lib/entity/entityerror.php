<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2015 Thurly
 */

namespace Thurly\Main\Entity;

class EntityError extends \Thurly\Main\Error
{
	public function __construct($message, $code='BX_ERROR')
	{
		parent::__construct($message, $code);
	}
}
