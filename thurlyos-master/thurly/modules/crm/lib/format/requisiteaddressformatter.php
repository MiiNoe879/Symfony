<?php
namespace Thurly\Crm\Format;
use Thurly\Crm\EntityRequisite;
use Thurly\Main;
use Thurly\Crm\RequisiteAddress;

class RequisiteAddressFormatter extends EntityAddressFormatter
{
	public static function prepareLines(array $fields, array $options = null)
	{
		return parent::prepareLines(RequisiteAddress::mapEntityFields($fields, $options), $options);
	}
	public static function format(array $fields, array $options = null)
	{
		return parent::formatLines(self::prepareLines($fields, $options), $options);
	}
	public static function formatByCountry(array $fields, $countryId, array $options = null)
	{
		$countryId = (int)$countryId;
		switch ($countryId)
		{
			case 1:                // ru
			case 4:                // by
			case 14:               // ua
				$format = EntityAddressFormatter::RUS;
				break;
			case 6:                // kz
				$format = EntityAddressFormatter::RUS2;
				break;
			case 46:               // de
				$format = EntityAddressFormatter::EU;
				break;
			case 122:              // us
				$format = EntityAddressFormatter::USA;
				break;
			default:
				$format = EntityAddressFormatter::Undefined;
		}
		$options['FORMAT'] = $format;

		return EntityAddressFormatter::format($fields, $options);
	}
}