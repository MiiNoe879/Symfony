<?php
namespace Thurly\Crm\Format;
use Thurly\Main;
use Thurly\Crm\CompanyAddress;

class CompanyAddressFormatter extends EntityAddressFormatter
{
	public static function prepareLines(array $fields, array $options = null)
	{
		return parent::prepareLines(CompanyAddress::mapEntityFields($fields, $options), $options);
	}
	public static function format(array $fields, array $options = null)
	{
		return parent::formatLines(self::prepareLines($fields, $options), $options);
	}
}