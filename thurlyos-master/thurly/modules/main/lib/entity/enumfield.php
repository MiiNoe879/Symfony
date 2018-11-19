<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Main\Entity;
use Thurly\Main\SystemException;

/**
 * Entity field class for enum data type
 * @package thurly
 * @subpackage main
 */
class EnumField extends ScalarField
{
	protected $values;

	function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		if (empty($parameters['values']))
		{
			throw new SystemException(sprintf(
				'Required parameter "values" for %s field is not found',
				$this->name
			));
		}

		if (!is_array($parameters['values']))
		{
			throw new SystemException(sprintf(
				'Parameter "values" for %s field should be an array',
				$this->name
			));
		}


		$this->values = $parameters['values'];
	}

	public function getValidators()
	{
		$validators = parent::getValidators();

		if ($this->validation === null)
		{
			$validators[] = new Validator\Enum;
		}

		return $validators;
	}

	public function getValues()
	{
		return $this->values;
	}

	public function convertValueToDb($value)
	{
		return $this->getConnection()->getSqlHelper()->convertToDbString($value);
	}
}