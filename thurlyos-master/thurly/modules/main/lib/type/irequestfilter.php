<?php
namespace Thurly\Main\Type;

use Thurly\Main;

interface IRequestFilter
{
	/**
	 * @param array $values
	 * @return array
	 */
	function filter(array $values);
}
