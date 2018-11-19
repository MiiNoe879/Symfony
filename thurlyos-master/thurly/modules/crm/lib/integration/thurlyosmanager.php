<?php
namespace Thurly\Crm\Integration;

use Thurly\Main;
use Thurly\Main\Loader;
use Thurly\Main\ModuleManager;
use \Thurly\Main\Config\Option;

/**
 * Class ThurlyOSManager
 *
 * Required in Biitrix24 context. Provodes information about the license and supported features.
 * @package Thurly\Crm\Integration
 */
class ThurlyOSManager
{
	//region Members
	/** @var bool|null */
	private static $hasPurchasedLicense = null;
	/** @var bool|null */
	private static $hasDemoLicense = null;
	/** @var bool|null */
	private static $hasNfrLicense = null;
	/** @var bool|null */
	private static $hasPurchasedUsers = null;
	/** @var bool|null */
	private static $hasPurchasedDiskSpace = null;
	/** @var bool|null */
	private static $isPaidAccount = null;
	/** @var bool|null */
	private static $enableRestBizProc = null;
	/** @var array|null */
	private static $entityAccessFlags = null;
	/** @var array|null */
	private static $unlimitedAccessFlags = null;
	//endregion
	//region Methods
	/**
	 * Check if current manager enabled.
	 * @return bool
	 */
	public static function isEnabled()
	{
		return ModuleManager::isModuleInstalled('thurlyos');
	}
	/**
	 * Check if portal has paid license, paid for extra users, paid for disk space or SIP features.
	 * @return bool
	 */
	public static function isPaidAccount()
	{
		if(self::$isPaidAccount !== null)
		{
			return self::$isPaidAccount;
		}

		self::$isPaidAccount = self::hasPurchasedLicense()
			|| self::hasPurchasedUsers()
			|| self::hasPurchasedDiskSpace();

		if(!self::$isPaidAccount)
		{
			//Phone number check: voximplant::account_payed
			//SIP connector check: main::~PARAM_PHONE_SIP
			self::$isPaidAccount = \COption::GetOptionString('voximplant', 'account_payed', 'N') === 'Y'
				|| \COption::GetOptionString('main', '~PARAM_PHONE_SIP', 'N') === 'Y';
		}

		return self::$isPaidAccount;
	}
	/**
	 * Check if portal has paid license.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasPurchasedLicense()
	{
		if(self::$hasPurchasedLicense !== null)
		{
			return self::$hasPurchasedLicense;
		}

		if(!(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos'))
			&& method_exists('CThurlyOS', 'IsLicensePaid'))
		{
			return (self::$hasPurchasedLicense = false);
		}

		return (self::$hasPurchasedLicense = \CThurlyOS::IsLicensePaid());
	}
	/**
	 *  Check if portal has trial license.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasDemoLicense()
	{
		if(self::$hasDemoLicense !== null)
		{
			return self::$hasDemoLicense;
		}

		if(!(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos'))
			&& method_exists('CThurlyOS', 'IsDemoLicense'))
		{
			return (self::$hasDemoLicense = false);
		}

		return (self::$hasDemoLicense = \CThurlyOS::IsDemoLicense());
	}
	/**
	 * Check if portal has NFR license.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasNfrLicense()
	{
		if(self::$hasNfrLicense !== null)
		{
			return self::$hasNfrLicense;
		}

		if(!(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos'))
			&& method_exists('CThurlyOS', 'IsNfrLicense'))
		{
			return (self::$hasNfrLicense = false);
		}

		return (self::$hasNfrLicense = \CThurlyOS::IsNfrLicense());
	}
	/**
	 * Check if portal has paid for extra users.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasPurchasedUsers()
	{
		if(self::$hasPurchasedUsers !== null)
		{
			return self::$hasPurchasedUsers;
		}

		if(!(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos'))
			&& method_exists('CThurlyOS', 'IsExtraUsers'))
		{
			return (self::$hasPurchasedUsers = false);
		}

		return (self::$hasPurchasedUsers = \CThurlyOS::IsExtraUsers());
	}
	/**
	 * Check if portal has paid for extra disk space.
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function hasPurchasedDiskSpace()
	{
		if(self::$hasPurchasedDiskSpace !== null)
		{
			return self::$hasPurchasedDiskSpace;
		}

		if(!(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos'))
			&& method_exists('CThurlyOS', 'IsExtraDiskSpace'))
		{
			return (self::$hasPurchasedDiskSpace = false);
		}

		return (self::$hasPurchasedDiskSpace = \CThurlyOS::IsExtraDiskSpace());
	}
	/**
	 * Check if Business Processes are enabled for REST API.
	 * @return bool
	 */
	public static function isRestBizProcEnabled()
	{
		if(self::$enableRestBizProc !== null)
		{
			return self::$enableRestBizProc;
		}

		return (self::$enableRestBizProc = (self::hasPurchasedLicense() || self::hasNfrLicense()));
	}
	/**
	 * Prepare JavaScript for license purchase information.
	 * @param array $params Popup params.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function prepareLicenseInfoPopupScript(array $params)
	{
		if(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos')
			&& method_exists('CThurlyOS', 'initLicenseInfoPopupJS'))
		{
			\CThurlyOS::initLicenseInfoPopupJS();

			$popupID = isset($params['ID']) ? \CUtil::JSEscape($params['ID']) : '';
			$title = isset($params['TITLE']) ? \CUtil::JSEscape($params['TITLE']) : '';
			$content = isset($params['CONTENT']) ? \CUtil::JSEscape($params['CONTENT']) : '';

			return "if(typeof(B24.licenseInfoPopup) !== 'undefined'){ B24.licenseInfoPopup.show('{$popupID}', '{$title}', '{$content}'); }";
		}

		return '';
	}
	/**
	 * Prepare HTML for license purchase information.
	 * @param array $params Popup params.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function prepareLicenseInfoHtml(array $params)
	{
		if(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos'))
		{
			$popupID = isset($params['ID']) ? \CUtil::JSEscape($params['ID']) : '';
			$content = '';
			if(isset($params['CONTENT']))
			{
				$licenseListUrl = \CUtil::JSEscape(\CThurlyOS::PATH_LICENSE_ALL);
				$demoLicenseUrl = \CUtil::JSEscape(\CThurlyOS::PATH_LICENSE_DEMO);

				$content = str_replace(
					array(
						'#LICENSE_LIST_SCRIPT#',
						'#DEMO_LICENSE_SCRIPT#'
					),
					array(
						"BX.CrmRemoteAction.items['{$popupID}'].execute('{$licenseListUrl}');",
						"BX.CrmRemoteAction.items['{$popupID}'].execute('{$demoLicenseUrl}');"
					),
					$params['CONTENT']
				);
			}

			$serviceUrl = \CUtil::JSEscape(\CThurlyOS::PATH_COUNTER);
			$hostName = \CUtil::JSEscape(BX24_HOST_NAME);
			return "{$content}
				<script type='text/javascript'>
					BX.ready(
						function()
						{
							BX.CrmRemoteAction.create(
								'{$popupID}',
								{
									serviceUrl: '{$serviceUrl}',
									data: { host: '{$hostName}', action: 'tariff', popupId: '{$popupID}' }
								}
							);
						}
					);
				</script>";
		}

		return '';
	}
	/**
	 * Get URL for "Choose a ThurlyOS plan" page.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function getLicenseListPageUrl()
	{
		if(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos'))
		{
			return \CThurlyOS::PATH_LICENSE_ALL;
		}

		return '';
	}
	/**
	 * Get URL for "Free 30-day trial" page.
	 * @return string
	 * @throws Main\LoaderException
	 */
	public static function getDemoLicensePageUrl()
	{
		if(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos'))
		{
			return \CThurlyOS::PATH_LICENSE_DEMO;
		}

		return '';
	}
	/**
	 * Check accessability of entity type according to ThurlyOS restrictions.
	 * @param int $entityTypeID Entity type ID.
	 * @param int $userID User ID (if not specified then current user ID will be taken).
	 * @return bool
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 */
	public static function isAccessEnabled($entityTypeID, $userID = 0)
	{
		if(!is_integer($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(!\CCrmOwnerType::IsDefined($entityTypeID))
		{
			throw new Main\ArgumentOutOfRangeException('entityTypeID',
				\CCrmOwnerType::FirstOwnerType,
				\CCrmOwnerType::LastOwnerType
			);
		}

		if(!is_integer($userID))
		{
			$userID = (int)$userID;
		}

		if($userID <= 0)
		{
			$userID = \CCrmSecurityHelper::GetCurrentUserID();
		}

		if(self::$entityAccessFlags === null)
		{
			self::$entityAccessFlags = array();
		}

		if(!isset(self::$entityAccessFlags[$userID]))
		{
			self::$entityAccessFlags[$userID] = array();
		}

		$code = $entityTypeID === \CCrmOwnerType::Lead ? 'crm_lead' : 'crm';
		if(isset(self::$entityAccessFlags[$userID][$code]))
		{
			return self::$entityAccessFlags[$userID][$code];
		}

		if(!(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos')
			&& method_exists('CThurlyOSBusinessTools', 'isToolAvailable')))
		{
			return (self::$entityAccessFlags[$userID][$code] = true);
		}

		return (self::$entityAccessFlags[$userID][$code] = \CThurlyOSBusinessTools::isToolAvailable($userID, $code));
	}
	/**
	 * Check if user has unlimited access
	 * @param int $userID User ID (if not specified then current user ID will be taken).
	 * @return bool
	 * @throws Main\LoaderException
	 */
	public static function isUnlimitedAccess($userID = 0)
	{
		if(!is_integer($userID))
		{
			$userID = (int)$userID;
		}

		if($userID <= 0)
		{
			$userID = \CCrmSecurityHelper::GetCurrentUserID();
		}

		if(self::$unlimitedAccessFlags === null)
		{
			self::$unlimitedAccessFlags = array();
		}

		if(isset(self::$unlimitedAccessFlags[$userID]))
		{
			return self::$unlimitedAccessFlags[$userID];
		}

		if(!(ModuleManager::isModuleInstalled('thurlyos')
			&& Loader::includeModule('thurlyos')
			&& method_exists('CThurlyOSBusinessTools', 'isUserUnlimited')))
		{
			return (self::$unlimitedAccessFlags[$userID] = true);
		}

		return (self::$unlimitedAccessFlags[$userID] = \CThurlyOSBusinessTools::isUserUnlimited($userID));
	}
	/**
	 * Get maximum allowed deal category quantity.
	 * @return int
	 * @throws Main\LoaderException
	 */
	public static function getDealCategoryCount()
	{
		if(!(ModuleManager::isModuleInstalled('thurlyos') && Loader::includeModule('thurlyos')))
		{
			return 0;
		}

		return ((int)Option::get('crm', 'crm_deal_category_limit', 1));
	}
	//endregion

	/**
	 * Check if specified feature is enabled
	 * @param string $releaseName Name of release.
	 * @return bool
	 */
	public static function isFeatureEnabled($releaseName)
	{
		if(!self::isEnabled())
		{
			return true;
		}

		return \Thurly\ThurlyOS\Release::isAvailable($releaseName);
	}
}
?>