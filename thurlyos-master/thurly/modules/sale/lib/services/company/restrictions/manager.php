<?php
namespace Thurly\Sale\Services\Company\Restrictions;

use Thurly\Main;
use Thurly\Sale\Internals;
use Thurly\Sale\Services\Base\RestrictionManager;

class Manager extends RestrictionManager
{
	protected static $classNames = null;

	/**
	 * @param array $parameters
	 * @return Main\DB\Result
	 * @throws Main\ArgumentException
	 */
	public static function getList(array $parameters)
	{
		return Internals\CompanyTable::getList($parameters);
	}

	/**
	 * @return array
	 */
	public static function getBuildInRestrictions()
	{
		return array(
			'\Thurly\Sale\Services\Company\Restrictions\Currency' => 'lib/services/company/restrictions/currency.php',
			'\Thurly\Sale\Services\Company\Restrictions\Site' => 'lib/services/company/restrictions/site.php',
			'\Thurly\Sale\Services\Company\Restrictions\EntityType' => 'lib/services/company/restrictions/entitytype.php',
			'\Thurly\Sale\Services\Company\Restrictions\Location' => 'lib/services/company/restrictions/location.php',
			'\Thurly\Sale\Services\Company\Restrictions\PaySystem' => 'lib/services/company/restrictions/paysystem.php',
			'\Thurly\Sale\Services\Company\Restrictions\Delivery' => 'lib/services/company/restrictions/delivery.php',
			'\Thurly\Sale\Services\Company\Restrictions\PersonType' => 'lib/services/company/restrictions/persontype.php',
			'\Thurly\Sale\Services\Company\Restrictions\Price' => 'lib/services/company/restrictions/price.php',
		);
	}

	/**
	 * @return string
	 */
	public static function getEventName()
	{
		return 'onSaleCompanyRulesClassNamesBuildList';
	}

	/**
	 * @return int
	 */
	protected static function getServiceType()
	{
		return self::SERVICE_TYPE_COMPANY;
	}
}