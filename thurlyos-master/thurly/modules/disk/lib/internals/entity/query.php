<?php

namespace Thurly\Disk\Internals\Entity;

use Thurly\Disk\Internals\Iterator;

/**
 * Class Query
 * @package Thurly\Disk\Internals\Entity
 * @internal
 */
final class Query extends \Thurly\Main\Entity\Query
{
	/**
	 * Generates where condition by filter.
	 * @return string
	 */
	public function getWhere()
	{
		return $this->buildWhere();
	}
}