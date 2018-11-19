<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2013 Thurly
 */

namespace Thurly\Main\Entity\Validator;

use Thurly\Main\Entity;
use Thurly\Main\Type;

class Date extends Base
{
	public function validate($value, $primary, array $row, Entity\Field $field)
	{
		if (empty($value))
		{
			return true;
		}

		if ($value instanceof Type\Date)
		{
			// self-validating object
			return true;
		}

		if (\CheckDateTime($value, FORMAT_DATE))
		{
			return true;
		}

		return $this->getErrorMessage($value, $field);
	}
}
