<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2013 Thurly
 */

namespace Thurly\Main\Entity\Validator;

use Thurly\Main\Entity;

class Enum extends Base
{
	/**
	 * @param $value
	 * @param $primary
	 * @param array $row
	 * @param Entity\Field | Entity\EnumField | Entity\BooleanField $field
	 * @return bool|string
	 */
	public function validate($value, $primary, array $row, Entity\Field $field)
	{
		if (in_array($value, $field->getValues(), true) || $value == '')
		{
			return true;
		}

		return $this->getErrorMessage($value, $field);
	}
}
