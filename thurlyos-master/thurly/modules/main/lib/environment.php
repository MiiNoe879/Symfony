<?php
namespace Thurly\Main;

use Thurly\Main\Type\ParameterDictionary;

class Environment
	extends ParameterDictionary
{
	/**
	 * Creates env object.
	 *
	 * @param array $arEnv
	 */
	public function __construct(array $arEnv)
	{
		parent::__construct($arEnv);
	}
}