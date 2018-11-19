<?php
use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

if (
	!Loader::includeModule('crm') && !Loader::includeModule('seo') &&
	!Loader::includeModule('socialservices')
)
{
	return;
}

use \Thurly\Crm\Ads\AdsAudience;

Loc::loadMessages(__FILE__);

class CrmAdsRetargetingAjaxController extends \Thurly\Crm\SiteButton\ComponentController
{
	protected function getActions()
	{
		return array(
			'getProvider',
			'getAccounts',
			'getAudiences',
			'logout',
		);
	}

	protected function getProvider()
	{
		$type = $this->request->get('type');
		$this->responseData['data'] = static::getAdsProvider($type);
		$this->checkAdsErrors();
	}

	protected function logout()
	{
		$type = $this->request->get('type');
		AdsAudience::removeAuth($type);
		$this->responseData['data'] = static::getAdsProvider($type);
		$this->checkAdsErrors();
	}

	protected function getAccounts()
	{
		$type = $this->request->get('type');
		$this->responseData['data'] = AdsAudience::getAccounts($type);
		$this->checkAdsErrors();
	}

	protected function getAudiences()
	{
		$type = $this->request->get('type');
		$accountId = $this->request->get('accountId');
		$this->responseData['data'] = AdsAudience::getAudiences($type, $accountId);
		$this->checkAdsErrors();
	}

	protected function checkAdsErrors()
	{
		$this->errors = array_merge($this->errors, AdsAudience::getErrors());
	}

	protected static function getAdsProvider($adsType)
	{
		$providers = AdsAudience::getProviders();
		$isFound = false;
		$provider = array();
		foreach ($providers as $type => $provider)
		{
			if ($type == $adsType)
			{
				$isFound = true;
				break;
			}
		}

		if (!$isFound)
		{
			return null;
		}

		return $provider;
	}

	protected function checkPermissions()
	{
		/**@var $USER \CAllUser*/
		global $USER;
		$crmPerms = new CCrmPerms($USER->GetID());
		return $crmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE');
	}

	protected function prepareRequestData()
	{
		$this->requestData = array(

		);
	}
}

$controller = new CrmAdsRetargetingAjaxController();
$controller->exec();