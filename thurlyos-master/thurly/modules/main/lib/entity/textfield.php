<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main\Entity;

/**
 * Entity field class for text data type
 * @package thurly
 * @subpackage main
 */
class TextField extends StringField
{
	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbText($value);
	}
}