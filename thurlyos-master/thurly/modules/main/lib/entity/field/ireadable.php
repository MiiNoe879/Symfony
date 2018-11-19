<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2017 Thurly
 */

namespace Thurly\Main\Entity\Field;

/**
 * Interface for Entity Fields to be filtered by Query.
 * @package Thurly\Main\Entity\Query\Filter
 */
interface IReadable
{
	/**
	 * Should return raw SQL with escaped and quoted value.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function convertValueToDb($value);
}
