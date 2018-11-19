<?php
namespace Thurly\Crm\Requisite;
use Thurly\Main;
abstract class RequisiteConvertException extends Main\SystemException
{
	/**
	 * Get localized error message
	 * @return string
	 */
	public abstract function getLocalizedMessage();
}