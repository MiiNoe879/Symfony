<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2012 Thurly
 */

namespace Thurly\Tasks\Util\Entity;

/**
 * Entity field class for datetime data type
 * @package thurly
 * @subpackage main
 */

class DateTimeField extends \Thurly\Main\Entity\DateTimeField
{
	public function __construct($name, $parameters = array())
	{
		parent::__construct($name, $parameters);

		$this->addFetchDataModifier(array($this, 'assureValueObject'));
	}

	// tell ORM what kind of type we are, since ORM uses a simple array of standard types to determine
	public function getDataType()
	{
		return 'datetime';
	}

	public function assureValueObject($value)
	{
		if ($value)
		{
			return \Thurly\Tasks\Util\Type\DateTime::createFromInstance($value);
		}

		return $value;
	}
}