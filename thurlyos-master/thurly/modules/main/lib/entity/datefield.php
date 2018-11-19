<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main\Entity;

use Thurly\Main;
use Thurly\Main\Type;

/**
 * Entity field class for date data type
 * @package thurly
 * @subpackage main
 */
class DateField extends ScalarField
{
	public function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		$this->addFetchDataModifier(array($this, 'assureValueObject'));
	}

	public function getValidators()
	{
		$validators = parent::getValidators();

		if ($this->validation === null)
		{
			$validators[] = new Validator\Date;
		}

		return $validators;
	}

	public function assureValueObject($value)
	{
		if ($value instanceof Type\DateTime)
		{
			// oracle sql helper returns datetime instead of date - it doesn't see the difference
			$value = new Type\Date(
				$value->format(Main\UserFieldTable::MULTIPLE_DATE_FORMAT),
				Main\UserFieldTable::MULTIPLE_DATE_FORMAT
			);
		}

		return $value;
	}

	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbDate($value);
	}
}