<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2015 Thurly
 */

namespace Thurly\Main;

use Thurly\Main\Type\Dictionary;

class ErrorCollection extends Dictionary
{
	/**
	 * Constructor ErrorCollection.
	 * @param Error[] $values Initial errors in the collection.
	 */
	public function __construct(array $values = null)
	{
		if($values)
		{
			$this->add($values);
		}
	}

	/**
	 * Adds an array of errors to the collection.
	 * @param Error[] $errors
	 * @return void
	 */
	public function add(array $errors)
	{
		foreach($errors as $error)
		{
			$this->setError($error);
		}
	}

	/**
	 * Returns an error with the necessary code.
	 * @param string|int $code The code of the error.
	 * @return Error|null
	 */
	public function getErrorByCode($code)
	{
		foreach($this->values as $error)
		{
			/** @var Error $error */
			if($error->getCode() === $code)
			{
				return $error;
			}
		}

		return null;
	}

	/**
	 * Adds an error to the collection.
	 * @param Error $error An error object.
	 * @param $offset Offset in the array.
	 * @return void
	 */
	public function setError(Error $error, $offset = null)
	{
		parent::offsetSet($offset, $error);
	}

	/**
	 * \ArrayAccess thing.
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->setError($value, $offset);
	}
}
