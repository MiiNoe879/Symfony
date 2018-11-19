<?php
namespace Thurly\Bizproc\BaseType;

use Thurly\Main\Localization\Loc;
use Thurly\Bizproc\FieldType;

Loc::loadMessages(__FILE__);

/**
 * Class Datetime
 * @package Thurly\Bizproc\BaseType
 */
class Datetime extends Date
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return FieldType::DATETIME;
	}
}