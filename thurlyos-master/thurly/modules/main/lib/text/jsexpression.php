<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2015 Thurly
 */

namespace Thurly\Main\Text;

/**
 * Class description
 * @package thurly
 * @subpackage main
 */
class JsExpression
{
	/** @var string */
	protected $expression;

	function __construct($expression)
	{
		$this->expression = $expression;
	}

	function __toString()
	{
		return $this->expression;
	}
}
