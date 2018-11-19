<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2017 Thurly
 */

namespace Thurly\Main\Entity\Query\Filter\Expression;

/**
 * Wrapper for null values in QueryFilter.
 * @package    thurly
 * @subpackage main
 */
class NullEx extends Base
{
	public function __toString()
	{
		return 'NULL';
	}
}
