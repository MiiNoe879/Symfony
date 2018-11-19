<?php

namespace Thurly\Sale\Services\Company\Restrictions;

use Thurly\Main\Localization\Loc;
use Thurly\Sale\Services\Base;
use \Thurly\Sale\Services\PaySystem\Restrictions;

Loc::loadMessages(__FILE__);

class PersonType extends Restrictions\PersonType
{
	/**
	 * @param $mode
	 * @return mixed
	 */
	public static function getSeverity($mode)
	{
		$result = Base\RestrictionManager::SEVERITY_STRICT;

		if($mode == Base\RestrictionManager::MODE_MANAGER)
			return Base\RestrictionManager::SEVERITY_SOFT;

		return $result;
	}
}