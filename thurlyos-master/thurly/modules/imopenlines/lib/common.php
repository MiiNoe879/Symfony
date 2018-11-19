<?php

namespace Thurly\ImOpenLines;

use Thurly\Main\Localization\Loc;
use Thurly\Main\IO;
use Thurly\Main\Localization\LanguageTable;
use Thurly\Main\Application;

Loc::loadMessages(__FILE__);

class Common
{
	const TYPE_THURLY24 = 'B24';
	const TYPE_CP = 'CP';

	const CACHE_TTL_MONTH = 2700000;

	/**
	 * Unsupported old-fashioned permission check.
	 * @return bool
	 * @deprecated Use Thurly\ImOpenLines\Security\Permissions instead.
	 */
	public static function hasAccessForAdminPages()
	{
		if (\IsModuleInstalled('thurlyos'))
		{
			return $GLOBALS['USER']->CanDoOperation('thurlyos_config');
		}
		else
		{
			return $GLOBALS["USER"]->IsAdmin();
		}
	}

	public static function getPortalType()
	{
		$type = '';
		if(defined('BX24_HOST_NAME'))
		{
			$type = self::TYPE_THURLY24;
		}
		else
		{
			$type = self::TYPE_CP;
		}
		return $type;
	}

	public static function getPublicFolder()
	{
		return self::GetPortalType() == self::TYPE_THURLY24 || file_exists($_SERVER['DOCUMENT_ROOT'].'/openlines/')? '/openlines/': SITE_DIR . 'services/openlines/';
	}

	public static function getServerAddress()
	{
		$publicUrl = \Thurly\Main\Config\Option::get("imopenlines", "portal_url");

		if ($publicUrl != '')
			return $publicUrl;
		else
			return (\Thurly\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'], Array(80, 443))?'':':'.$_SERVER['SERVER_PORT']);
	}

	public static function deleteBrokenSession()
	{
		$orm = \Thurly\ImOpenLines\Model\SessionTable::getList(array(
			'select' => Array('ID'),
			'filter' => Array('=CONFIG.ID' => '')
		));
		while ($session = $orm->fetch())
		{
			\Thurly\ImOpenLines\Model\SessionTable::delete($session['ID']);
		}

		$orm = \Thurly\ImOpenLines\Model\SessionCheckTable::getList(array(
			'filter' => Array('=SESSION.ID' => '')
		));
		while ($session = $orm->fetch())
		{
			\Thurly\ImOpenLines\Model\SessionCheckTable::delete($session['SESSION_ID']);
		}

		return '\Thurly\ImOpenLines\Common::deleteBrokenSession();';
	}

	public static function setUserAgrees($params)
	{
		if (empty($params['USER_CODE']))
			return false;

		$params['AGREEMENT_ID'] = intval($params['AGREEMENT_ID']);
		if ($params['AGREEMENT_ID'] <= 0)
			return false;

		$params['FLAG'] = $params['FLAG'] == 'N'? 'N': 'Y';

		\Thurly\Imopenlines\Model\UserRelationTable::update($params['USER_CODE'], Array('AGREES' => $params['FLAG']));

		if ($params['FLAG'] == 'Y' && \Thurly\Main\Loader::includeModule('crm'))
		{
			\Thurly\Main\UserConsent\Consent::addByContext(
				intval($params['AGREEMENT_ID']),
				\Thurly\Crm\Integration\UserConsent::PROVIDER_CODE,
				intval($params['CRM_ACTIVITY_ID']),
				array('IP' => '', 'URL' => self::getHistoryLink($params['SESSION_ID'], $params['CONFIG_ID']))
			);
		}

		return true;
	}

	public static function getAgreementLink($agreementId)
	{
		$agreementId = intval($agreementId);

		$ag = new \Thurly\Main\UserConsent\Agreement($agreementId);
		$data = $ag->getData();

		return \Thurly\ImOpenLines\Common::getServerAddress().'/pub/imol.php?id='.$agreementId.'&sec='.$data['SECURITY_CODE'];
	}

	public static function getHistoryLink($sessionId, $configId)
	{
		$sessionId = intval($sessionId);
		$configId = intval($configId);

		return \Thurly\ImOpenLines\Common::getServerAddress().\Thurly\ImOpenLines\Common::getPublicFolder()."statistics.php?".($configId? 'CONFIG_ID='.$configId.'&': '').'IM_HISTORY=imol|'.$sessionId;
	}

	public static function getThurlyUrlByLang($lang = null)
	{
		$url = '';
		if (\Thurly\Main\Loader::includeModule('thurlyos'))
		{
			if (!$lang)
			{
				if (defined('B24_LANGUAGE_ID'))
					$lang = B24_LANGUAGE_ID;
				else
					$lang = substr((string)\Thurly\Main\Config\Option::get('main', '~controller_group_name'), 0, 2);
			}

			$areaConfig = \CThurlyOS::getAreaConfig($lang);
			if ($areaConfig)
			{
				$url = 'www'.$areaConfig['DEFAULT_DOMAIN'];
			}
			else
			{
				$url = 'www.thurlyos.com';
			}
		}
		else
		{
			if (LANGUAGE_ID == 'de')
			{
				$url = 'www.thurlyos.de';
			}
			else if (LANGUAGE_ID == 'ua')
			{
				$url = 'www.thurlyos.ua';
			}
			else if (LANGUAGE_ID == 'kz')
			{
				$url = 'www.thurlyos.kz';
			}
			else if (LANGUAGE_ID == 'by')
			{
				$url = 'www.thurlyos.by';
			}
			else if (LANGUAGE_ID == 'ru')
			{
				$url = 'www.thurlyos.ru';
			}
			else
			{
				$url = 'www.thurlyos.com';
			}
		}

		$partnerId = \Thurly\Main\Config\Option::get("thurlyos", "partner_id", 0);
		if ($partnerId)
		{
			$url .= '/?p='.$partnerId;
		}

		return "https://".$url;
	}

	public static function setCacheTag($tag, $cacheTtl = self::CACHE_TTL_MONTH)
	{
		if (!is_string($tag))
			return false;

		$app = \Thurly\Main\Application::getInstance();
		$managedCache = $app->getManagedCache();
		$managedCache->clean("imol_cache_tag_".$tag);
		$managedCache->read($cacheTtl, "imol_cache_tag_".$tag);
		$managedCache->setImmediate("imol_cache_tag_".$tag, true);

		return true;
	}

	public static function getCacheTag($tag, $cacheTtl = self::CACHE_TTL_MONTH)
	{
		if (!is_string($tag))
			return false;

		$app = \Thurly\Main\Application::getInstance();
		$managedCache = $app->getManagedCache();
		if ($result = $managedCache->read($cacheTtl, "imol_cache_tag_".$tag))
		{
			$result = $managedCache->get("imol_cache_tag_".$tag) === false? false: true;
		}
		return $result;
	}

	public static function removeCacheTag($tag)
	{
		if (!is_string($tag))
			return false;

		$app = \Thurly\Main\Application::getInstance();
		$managedCache = $app->getManagedCache();
		$managedCache->clean("imol_cache_tag_".$tag);

		return true;
	}

	public static function getWorkTimeEnd($date = null)
	{
		$workTimeEnd = explode('.', \Thurly\Main\Config\Option::get('calendar', 'work_time_end', '18'));

		if (!($date instanceof \Thurly\Main\Type\DateTime))
		{
			$date = new \Thurly\Main\Type\DateTime();
		}
		$date->setTime($workTimeEnd[0], $workTimeEnd[1], 0);

		return $date;
	}

	public static function objectEncode($params)
	{
		if (is_array($params))
		{
			array_walk_recursive($params, function(&$item, $key){
				if ($item instanceof \Thurly\Main\Type\DateTime)
				{
					$item = date('c', $item->getTimestamp());
				}
			});
		}

		return \CUtil::PhpToJSObject($params);
	}
}
