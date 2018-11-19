<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CIntranetMailCheckComponent extends CThurlyComponent
{

	public function executeComponent()
	{
		global $APPLICATION;

		$this->arParams['LAST_MAIL_CHECK']       = null;
		$this->arParams['IS_TIME_TO_MAIL_CHECK'] = null;

		$settedUp = null;

		if (defined('SKIP_MAIL_CHECK') && SKIP_MAIL_CHECK == true)
			$settedUp = false;

		if (defined('ADMIN_SECTION') && ADMIN_SECTION == true)
			$settedUp = false;

		if ($settedUp !== false)
		{
			$isMobileInstalled = COption::GetOptionString('main', 'wizard_mobile_installed', 'N', SITE_ID) == 'Y';
			$isMobileVersion   = strpos($APPLICATION->GetCurPage(), SITE_DIR.'m/') === 0;
			if ($isMobileInstalled && $isMobileVersion)
				$settedUp = false;
		}

		if ($settedUp !== false)
		{
			if (!is_callable(array('CIntranetUtils', 'IsExternalMailAvailable')) || !CIntranetUtils::IsExternalMailAvailable())
				$settedUp = false;
		}


		if ($settedUp !== false)
		{
			$lastMailCheck = CUserOptions::GetOption('global', 'last_mail_check_'.SITE_ID, null);
			if (isset($lastMailCheck) && intval($lastMailCheck) < 0)
				$settedUp = false;

			$lastMailSync = CUserOptions::getOption('global', 'last_mail_sync_'.SITE_ID, null);
		}

		if ($settedUp !== false)
		{
			$checkInterval = COption::getOptionString('intranet', 'mail_check_period', 10) * 60;

			$isTimeToMailCheck = true;
			if (isset($lastMailCheck))
			{
				$settedUp = true;
				$isTimeToMailCheck = false;
				if (intval($lastMailCheck) >= 0)
					$isTimeToMailCheck = time() - intval($lastMailCheck) >= $checkInterval;
			}
		}

		if ($settedUp !== false)
		{
			$this->arParams['CHECK_INTERVAL'] = $checkInterval;

			$this->arParams['LAST_MAIL_SYNC']  = $lastMailSync;
			$this->arParams['LAST_MAIL_CHECK'] = $lastMailCheck;

			$this->arParams['IS_TIME_TO_MAIL_CHECK'] = $isTimeToMailCheck;
		}

		$this->arParams['SETTED_UP'] = $settedUp;

		$this->includeComponentTemplate();
	}

}
