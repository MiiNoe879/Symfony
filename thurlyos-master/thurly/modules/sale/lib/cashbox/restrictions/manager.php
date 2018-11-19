<?php

namespace Thurly\Sale\Cashbox\Restrictions;

use Thurly\Sale\Services\Base;

class Manager extends Base\RestrictionManager
{
	protected static $classNames = null;

	/**
	 * @return string
	 */
	public static function getEventName()
	{
		return 'onSaleCashboxRestrictionsClassNamesBuildList';
	}

	/**
	 * @return array
	 */
	public static function getBuildInRestrictions()
	{
		return array(
			'\Thurly\Sale\Cashbox\Restrictions\Company' => 'lib/cashbox/restrictions/company.php',
			'\Thurly\Sale\Cashbox\Restrictions\PaySystem' => 'lib/cashbox/restrictions/paysystem.php',
		);
	}

	/**
	 * @return int
	 */
	protected static function getServiceType()
	{
		return parent::SERVICE_TYPE_CASHBOX;
	}
}