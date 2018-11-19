<?php
namespace Thurly\FaceId;


class FaceCard
{
	/**
	 * @return bool
	 */
	static public function isAvailableByUser($userId)
	{
		return (!static::applicationIsInactive() && !static::licenceIsRestricted() && static::agreementIsAccepted($userId));
	}

	/**
	 * @return bool
	 */
	static public function licenceIsRestricted()
	{
		$r = false;
		if (\CModule::IncludeModule('thurlyos'))
		{
			$r = in_array(\CThurlyOS::getLicenseType(), array('project', 'tf'));
		}
		return $r;
	}

	/**
	 * @return bool
	 */
	static public function agreementIsAccepted($userId)
	{
		$result = false;

		if(intval($userId)<=0)
			return $result;

		if(\Thurly\Faceid\AgreementTable::checkUser($userId))
		{
			$result = true;
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	static protected function applicationIsInactive()
	{
		$r = false;
		if (\CModule::IncludeModule('rest'))
		{
			$appInfo = \Thurly\Rest\AppTable::getByClientId('thurly.1c');
			if(!$appInfo || $appInfo['ACTIVE'] === \Thurly\Rest\AppTable::INACTIVE)
			{
				$r = true;
			}
		}
		else
		{
			$r = true;
		}

		return $r;
	}
}