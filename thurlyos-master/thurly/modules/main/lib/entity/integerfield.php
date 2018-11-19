<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main\Entity;

/**
 * Entity field class for integer data type
 * @package thurly
 * @subpackage main
 */
class IntegerField extends ScalarField
{
	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbInteger($value);
	}
}