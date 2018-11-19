<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2016 Thurly
 */

namespace Thurly\Main\Entity\Query\Filter\Expression;

/**
 * Wrapper for columns values in QueryFilter.
 * @package    thurly
 * @subpackage main
 */
class Column extends Base
{
	/**
	 * @var string
	 */
	protected $definition;

	/**
	 * @param $definition
	 */
	public function __construct($definition)
	{
		$this->definition = $definition;
	}

	/**
	 * @return string
	 */
	public function getDefinition()
	{
		return $this->definition;
	}

	/**
	 * @param string $definition
	 */
	public function setDefinition($definition)
	{
		$this->definition = $definition;
	}
}
