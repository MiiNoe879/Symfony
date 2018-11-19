<?php
IncludeModuleLangFile(__FILE__);
class CThurlyCloudCDN
{
	/**
	 *
	 * @var CThurlyCloudCDNConfig $config
	 *
	 */
	private static $config = /*.(CThurlyCloudCDNConfig).*/ null;
	private static $proto = "";
	private static $ajax = false;
	private static $domain_changed = false;
	/**
	 *
	 * @return bool
	 *
	 */
	public static function IsActive()
	{
		foreach (GetModuleEvents("main", "OnEndBufferContent", true) as $arEvent)
		{
			if ($arEvent["TO_MODULE_ID"] === "thurlycloud" && $arEvent["TO_CLASS"] === "CThurlyCloudCDN")
			{
				return true;
			}
		}
		return false;
	}
	/**
	 *
	 * @return void
	 * @throws CThurlyCloudException
	 */
	private static function stop()
	{
		$o = CThurlyCloudCDNConfig::getInstance()->loadFromOptions();
		$a = new CThurlyCloudCDNWebService($o->getDomain());
		$a->actionStop();
	}
	/**
	 *
	 * @param bool $bActive
	 * @param bool $force
	 * @return bool
	 *
	 */
	public static function SetActive($bActive, $force = false)
	{
		/* @global CMain $APPLICATION */
		global $APPLICATION;
		if ($bActive)
		{
			if (!self::IsActive() || $force)
			{
				try
				{
					$o = CThurlyCloudCDNConfig::getInstance()->loadRemoteXML(true);
					$o->saveToOptions();
					if (!$o->isActive())
					{
						if (
							$o->getQuota()->getTrafficSize() > $o->getQuota()->getAllowedSize()
						)
						{
							$ex = new CApplicationException(GetMessage("BCL_CDN_ERROR_QUOTA_LIMIT"));
							$APPLICATION->ThrowException($ex);
							return false;
						}
						elseif (
						$o->getQuota()->isExpired()
						)
						{
							$ex = new CApplicationException(GetMessage("BCL_CDN_ERROR_QUOTA_EXPIRED"));
							$APPLICATION->ThrowException($ex);
							return false;
						}
					}
					RegisterModuleDependences("main", "OnEndBufferContent", "thurlycloud", "CThurlyCloudCDN", "OnEndBufferContent");
					self::$domain_changed = false;
				}
				catch(CThurlyCloudException $e)
				{
					$ex = new CApplicationException($e->getMessage()."\n".$e->getDebugInfo());
					$APPLICATION->ThrowException($ex);
					return false;
				}
			}
			elseif (self::$domain_changed)
			{
				try
				{
					$o = CThurlyCloudCDNConfig::getInstance()->loadRemoteXML(true);
					$o->saveToOptions();
					self::$domain_changed = false;
				}
				catch(CThurlyCloudException $e)
				{
					$ex = new CApplicationException($e->getMessage()."\n".$e->getDebugInfo());
					$APPLICATION->ThrowException($ex);
					return false;
				}
			}
		}
		else
		{
			if (self::IsActive() || $force)
			{
				try
				{
					self::stop();
					UnRegisterModuleDependences("main", "OnEndBufferContent", "thurlycloud", "CThurlyCloudCDN", "OnEndBufferContent");
				}
				catch(CThurlyCloudException $e)
				{
					UnRegisterModuleDependences("main", "OnEndBufferContent", "thurlycloud", "CThurlyCloudCDN", "OnEndBufferContent");
					$ex = new CApplicationException($e->getMessage()."\n".$e->getDebugInfo());
					$APPLICATION->ThrowException($ex);
					return false;
				}
			}
		}
		return true;
	}
	/**
	 *
	 * @return bool
	 *
	 */
	private static function updateConfig()
	{
		if (!self::$config->lock())
			return true;

		$delayExpiration = true;
		try
		{
			try
			{
				self::$config = CThurlyCloudCDNConfig::getInstance()->loadRemoteXML();
				self::$config->saveToOptions();
				self::$config->unlock();
			}
			catch(CThurlyCloudException $e)
			{
				//In case of documented XML error we'll disable CDN
				if($e->getErrorCode() !== "")
				{
					self::SetActive(false);
					$delayExpiration = false;
				}
				throw $e;
			}
		}
		catch (Exception $e)
		{
			if ($delayExpiration)
			{
				self::$config->setExpired(time() + 1800);
			}

			CAdminNotify::Add(array(
				"MESSAGE" => GetMessage("BCL_CDN_NOTIFY", array(
					"#HREF#" => "/thurly/admin/thurlycloud_cdn.php?lang=".LANGUAGE_ID,
				)),
				"TAG" => "thurlycloud_off",
				"MODULE_ID" => "thurlycloud",
				"ENABLE_CLOSE" => "Y",
			));
			self::$config->unlock();
			return false;
		}

		self::$config->unlock();

		//Web service were disabled
		if (!self::$config->isActive())
		{
			//By traffic quota
			if ( self::$config->getQuota()->getTrafficSize() > self::$config->getQuota()->getAllowedSize() )
			{
				self::$config->setExpired(time() + 1800);
				CAdminNotify::Add(array(
					"MESSAGE" => GetMessage("BCL_CDN_NOTIFY_QUOTA_LIMIT"),
					"TAG" => "thurlycloud_off",
					"MODULE_ID" => "thurlycloud",
					"ENABLE_CLOSE" => "N",
				));
				self::$config->unlock();
				return false;
			}
			//Or by license
			elseif ( self::$config->getQuota()->isExpired() )
			{
				self::$config->setExpired(time() + 1800);
				CAdminNotify::Add(array(
					"MESSAGE" => GetMessage("BCL_CDN_NOTIFY_QUOTA_EXPIRED"),
					"TAG" => "thurlycloud_off",
					"MODULE_ID" => "thurlycloud",
					"ENABLE_CLOSE" => "N",
				));
				self::$config->unlock();
				return false;
			}
		}

		CAdminNotify::DeleteByTag("thurlycloud_off");
		return true;
	}
	/**
	 *
	 * @param string &$content
	 * @return void
	 *
	 */
	public static function OnEndBufferContent(&$content)
	{
		if (isset($_GET["nocdn"]))
			return;

		$appCache = \Thurly\Main\Data\AppCacheManifest::getInstance();
		if ($appCache->isEnabled())
			return;

		self::$proto = CMain::IsHTTPS() ? "https" : "http";
		self::$config = CThurlyCloudCDNConfig::getInstance()->loadFromOptions();

		if (self::$config->isExpired())
		{
			if(!self::updateConfig())
				return;
		}

		if(!self::$config->isActive())
			return;

		$sites = self::$config->getSites();
		$siteId = defined("ADMIN_SECTION")? "admin": (defined("SITE_ID")? SITE_ID: "");
		if (!isset($sites[$siteId]))
			return;

		self::$ajax = preg_match("/<head>/i", substr($content, 0, 1024)) === 0;

		$arPrefixes = array_map(array(
			"CThurlyCloudCDN",
			"_preg_quote",
		), self::$config->getLocationsPrefixes(self::$config->isKernelRewriteEnabled(), self::$config->isContentRewriteEnabled()));

		$arExtensions = array_map(array(
			"CThurlyCloudCDN",
			"_preg_quote",
		), self::$config->getLocationsExtensions());

		if (!empty($arPrefixes) && !empty($arExtensions))
		{
			$prefix_regex = "(?:".implode("|", $arPrefixes).")";
			$extension_regex = "(?i:".implode("|", $arExtensions).")";
			$regex = "/
				((?i:
					(?<!;)href=
					|(?<!;)src=
					|BX\\.loadCSS\\(
					|BX\\.loadScript\\(
					|BX\\.getCDNPath\\(
					|jsUtils\\.loadJSFile\\(
					|background\\s*:\\s*url\\(
					|image\\s*:\\s*url\\(
					|'SRC':
				))                                                   #attribute
				(\"|')                                               #open_quote
				(".$prefix_regex.")                                  #prefix
				([^?'\"]+\\.)                                        #href body
				(".$extension_regex.")                               #extension
				(|\\?\\d+|\\?v=\\d+)                                 #params
				(\\2)                                                #close_quote
			/x";
			$content = preg_replace_callback($regex, array(
				"CThurlyCloudCDN",
				"_filter",
			), $content);
		}
	}
	/**
	 *
	 * @return void
	 *
	 */
	public static function domainChanged()
	{
		self::$domain_changed = true;
	}
	/**
	 *
	 * @param string $str
	 * @return string
	 *
	 */
	private static function _preg_quote($str)
	{
		return preg_quote($str, "/");
	}
	/**
	 *
	 * @param array[int]string $match
	 * @return string
	 *
	 */
	private static function _filter($match)
	{
		$attribute = $match[1];
		$open_quote = $match[2];
		$prefix = $match[3];
		$link = $match[4];
		$extension = $match[5];
		$params = $match[6];
		$close_quote = $match[7];
		$location = /*.(CThurlyCloudCDNLocation).*/ null;

		if(self::$ajax && $extension === "js")
			return $match[0];

		//if(preg_match("/^background/i", $attribute))
		//	$proto = self::$proto."://";
		//else
			$proto = "//";

		foreach (self::$config->getLocations() as $location)
		{
			/* @var CThurlyCloudCDNLocation $location */
			if ($location->getProto() === self::$proto)
			{
				$server = $location->getServerNameByPrefixAndExtension($prefix, $extension, $link);
				if ($server !== "")
				{
					$filePath = $prefix.$link.$extension;
					if ($params === '')
						$filePath = CUtil::GetAdditionalFileURL($filePath);

					//Fix spaces in the link
					$link = str_replace(" ", "%20", $link);
					return $attribute.$open_quote.$proto.$server.$filePath.$params.$close_quote;
				}
			}
		}
		return $match[0];
	}
	/**
	 * Shows information about CDN free space in Admin's informer popup
	 *
	 * @return void
	 */
	public static function OnAdminInformerInsertItems()
	{
		global $USER;

		if (IsModuleInstalled('intranet'))
			return;

		$CDNAIParams = array(
			"TITLE" => GetMessage("BCL_CDN_AI_TITLE"),
			"COLOR" => "green",
		);

		if (self::IsActive())
		{
			if ($USER->CanDoOperation("thurlycloud_cdn"))
			{
				$CDNAIParams["FOOTER"] = '<a href="/thurly/admin/thurlycloud_cdn.php?lang='.LANGUAGE_ID.'">'.GetMessage("BCL_CDN_AI_SETT").'</a>';
			}

			$cdn_config = CThurlyCloudCDNConfig::getInstance()->loadFromOptions();
			$cdn_quota = $cdn_config->getQuota();
			$PROGRESS_TOTAL = $cdn_quota->getAllowedSize();
			$PROGRESS_VALUE = $cdn_quota->getTrafficSize();

			if ($PROGRESS_TOTAL > 0.0 || $PROGRESS_VALUE > 0.0)
			{
				$PROGRESS_AVAILABLE = $PROGRESS_TOTAL-$PROGRESS_VALUE;
				if($PROGRESS_AVAILABLE < 0.0)
					$PROGRESS_AVAILABLE = 0.0;

				$PROGRESS_FREE = 0.0;
				if($PROGRESS_TOTAL > 0.0)
					$PROGRESS_FREE = round(($PROGRESS_TOTAL-$PROGRESS_VALUE)/$PROGRESS_TOTAL*100);

				$PROGRESS_FREE_BAR = $PROGRESS_FREE > 100.0? 100: intval($PROGRESS_FREE);
				$PROGRESS_FREE_BAR = $PROGRESS_FREE < 0.0? 0: intval($PROGRESS_FREE_BAR);

				$CDNAIParams["ALERT"] = false;
				if ($PROGRESS_FREE < 10.0)
					$CDNAIParams["ALERT"] = true;
				elseif (!$cdn_config->isActive())
					$CDNAIParams["ALERT"] = true;

				$CDNAIParams["HTML"] = '
					<div class="adm-informer-item-section">
						<span class="adm-informer-item-l">
							<span class="adm-informer-strong-text">'.GetMessage("BCL_CDN_AI_USAGE_TOTAL").'</span> '.CFile::FormatSize($PROGRESS_TOTAL, 0).'
						</span>
						<span class="adm-informer-item-r">
								<span class="adm-informer-strong-text">'.GetMessage("BCL_CDN_AI_USAGE_AVAIL").'</span> '.CFile::FormatSize($PROGRESS_AVAILABLE, 0).'
						</span>
					</div>
					<div class="adm-informer-status-bar-block" >
						<div class="adm-informer-status-bar-indicator" style="width:'.(100-$PROGRESS_FREE_BAR).'%; "></div>
						<div class="adm-informer-status-bar-text">'.(100-$PROGRESS_FREE).'%</div>
					</div>
				';
			}
		}
		else
		{
			$CDNAIParams["HTML"] = '
				<div class="adm-informer-item-section">
					<span class="adm-informer-strong-text">'.GetMessage("BCL_CDN_AI_IS_OFF").'</span>
				</div>
				<div class="adm-informer-status-bar-block" >
					<div class="adm-informer-status-bar-indicator" style="width:0%; "></div>
					<div class="adm-informer-status-bar-text">0%</div>
				</div>
			';
			$CDNAIParams["ALERT"] = true;
			if ($USER->CanDoOperation("thurlycloud_cdn"))
			{
				$CDNAIParams["FOOTER"] = '<a href="/thurly/admin/thurlycloud_cdn.php?lang='.LANGUAGE_ID.'">'.GetMessage("BCL_CDN_AI_TURN_ON").'</a>';
			}
		}

		CAdminInformer::AddItem($CDNAIParams);
	}
}
