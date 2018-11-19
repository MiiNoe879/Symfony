<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main\Data;

abstract class NosqlConnection extends Connection
{
	abstract public function get($key);
	abstract public function set($key, $value);
}
