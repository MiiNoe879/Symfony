<?php
/**
 * Thurly Framework
 * @package    thurly
 * @subpackage main
 * @copyright  2001-2013 Thurly
 */

namespace Thurly\Main\Entity\Validator;

use Thurly\Main\Entity;
use Thurly\Main\ArgumentTypeException;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class RegExp extends Base
{
	/**
	 * @var string
	 */
	protected $pattern;

	/**
	 * @var string
	 */
	protected $errorPhraseCode = 'MAIN_ENTITY_VALIDATOR_REGEXP';

	/**
	 * @param string $pattern
	 * @param null   $errorPhrase
	 *
	 * @throws ArgumentTypeException
	 */
	public function __construct($pattern, $errorPhrase = null)
	{
		if (!is_string($pattern))
		{
			throw new ArgumentTypeException('pattern', 'string');
		}

		$this->pattern = $pattern;

		parent::__construct($errorPhrase);
	}


	public function validate($value, $primary, array $row, Entity\Field $field)
	{
		if (preg_match($this->pattern, $value))
		{
			return true;
		}

		return $this->getErrorMessage($value, $field);
	}
}
