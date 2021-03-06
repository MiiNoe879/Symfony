<?php
use Thurly\Main\Loader;

IncludeModuleLangFile(__FILE__);

class CRestUtil
{
	const GLOBAL_SCOPE = '_global';
	const EVENTS = '_events';
	const PLACEMENTS = '_placements';

	const HANDLER_SESSION_TTL = 3;

	const BATCH_MAX_LENGTH = 50;

	const METHOD_DOWNLOAD = "download";
	const METHOD_UPLOAD = "upload";

	const TOKEN_DELIMITER = "|";

	const THURLY_1C_APP_CODE = 'thurly.1c';

	public static function sendHeaders()
	{
		Header('Access-Control-Allow-Origin: *');
		Header('Access-Control-Allow-Headers: origin, content-type, accept');
		Header('X-Content-Type-Options: nosniff');
	}

	public static function getStandardParams()
	{
		return array(
			"PARAMETERS" => array(
				"VARIABLE_ALIASES" => Array(
					"method" => Array("NAME" => GetMessage('REST_PARAM_METHOD_NAME')),
				),
				"SEF_MODE" => Array(
					"path" => array(
						"NAME" => GetMessage('REST_PARAM_PATH'),
						"DEFAULT" => "#method#",
						"VARIABLES" => array("method" => "method"),
					),
				),
			)
		);
	}

	public static function getRequestData()
	{
		$request = \Thurly\Main\Context::getCurrent()->getRequest();
		$server = \Thurly\Main\Context::getCurrent()->getServer();

		$query = $request->toArray();

		if($request->isPost() && $request->getPostList()->isEmpty())
		{
			$rawPostData = trim($request->getInput());

			if(isset($server['HTTP_CONTENT_TYPE']))
			{
				$requestContentType = $server['HTTP_CONTENT_TYPE'];
			}
			else
			{
				$requestContentType = $server['CONTENT_TYPE'];
			}

			$requestContentType = trim(preg_replace('/;.*$/', '', $requestContentType));

			$postData = array();

			switch($requestContentType)
			{
				case 'application/json':

					try
					{
						$postData = \Thurly\Main\Web\Json::decode($rawPostData);
					}
					catch(\Thurly\Main\ArgumentException $e)
					{
						$postData = array();
					}

					break;

				default:

					if(strlen($rawPostData) > 0)
					{
						parse_str($rawPostData, $postData);
					}

					break;
			}

			$query = array_replace($query, $postData);
		}

		CUtil::decodeURIComponent($query);

		return $query;
	}

	public static function isAdmin()
	{
		global $USER;

		if(\Thurly\Main\ModuleManager::isModuleInstalled('thurlyos'))
		{
			return $USER->CanDoOperation('thurlyos_config');
		}
		else
		{
			return $USER->IsAdmin();
		}
	}

	public static function signLicenseRequest(array $request, $licenseKey)
	{
		if(Loader::includeModule('thurlyos'))
		{
			$request['BX_TYPE'] = 'B24';
			$request['BX_LICENCE'] = BX24_HOST_NAME;
			$request['BX_HASH'] = \CThurlyOS::RequestSign(md5(implode("|", $request)));
		}
		else
		{
			$request['BX_TYPE'] = 'CP';
			$request['BX_LICENCE'] = md5("THURLY".$licenseKey."LICENCE");
			$request['BX_HASH'] = md5(md5(implode("|", $request)).md5($licenseKey));
		}

		return $request;
	}

	public static function ConvertDate($dt)
	{
		return $dt ? date('c', MakeTimeStamp($dt, FORMAT_DATE) + date("Z")) : '';
	}


	public static function ConvertDateTime($dt)
	{
		return $dt ? date('c', MakeTimeStamp($dt) - CTimeZone::GetOffset()) : '';
	}


	/**
	 * @param string $iso8601 date in ISO-8601 format (for example: '2013-05-14T12:00:50+04:00')
	 * @return string date in Thurly format, or FALSE (bool) on error
	 */
	public static function unConvertDate($iso8601)
	{
		if(is_array($iso8601))
		{
			foreach($iso8601 as $key => $value)
			{
				$iso8601[$key] = self::unConvertDateTime($value);
			}

			return $iso8601;
		}
		else
		{
			$date = false;
			$timestamp = strtotime($iso8601);

			if ($timestamp !== false)
				$date = ConvertTimeStamp($timestamp, 'SHORT');

			return ($date);
		}
	}

	/**
	 * @param string $iso8601 Datetime in ISO-8601 format (for example: '2013-05-14T12:00:50+04:00').
	 * @param bool $enableOffset Add user timezone offset.
	 * If $enableOffset == false, time in server timezone will be returned.
	 * If $enableOffset == true, time in user timezone will be returned.
	 * @return string datetime in Thurly format, or FALSE (bool) on error
	 */
	public static function unConvertDateTime($iso8601, $enableOffset = false)
	{
		if(is_array($iso8601))
		{
			foreach($iso8601 as $key => $value)
			{
				$iso8601[$key] = self::unConvertDateTime($value, $enableOffset);
			}

			return $iso8601;
		}
		else
		{
			$date = false;
			$timestamp = strtotime($iso8601);

			if ($timestamp !== false)
			{
				if($enableOffset)
				{
					$timestamp += CTimeZone::GetOffset();
				}
				$date = ConvertTimeStamp($timestamp, 'FULL');
			}

			return ($date);
		}
	}

	public static function getMemberId()
	{
		if(CModule::IncludeModule('thurlyos'))
		{
			return \CThurlyOS::getMemberId();
		}
		else
		{
			return \Thurly\Rest\OAuthService::getMemberId();
		}
	}

	public static function isStatic($url)
	{
		return preg_match("/^http[s]{0,1}:\/\/[^\/]*?(\.apps-thurlyos\.com|\.thurlyos-cdn\.com|cdn\.thurlyos\.|app\.thurlyos\.com|upload-.*?\.s3\.amazonaws\.com\/app_local\/)/i", $url);
	}

	public static function GetFile($fileId)
	{
		$fileSrc = array();
		$bMult = false;

		if(is_array($fileId))
		{
			$fileId = implode(',', $fileId);
			$bMult = true;
		}

		if(strlen($fileId) > 0)
		{
			$dbRes = CFile::GetList(array(), array('@ID' => $fileId));
			while($arRes = $dbRes->Fetch())
			{
				$fileSrc[$arRes['ID']] = CHTTP::URN2URI(CFile::GetFileSrc($arRes));
			}
		}

		return $bMult ? $fileSrc : $fileSrc[$fileId];
	}

	protected static function processBatchElement($query, $arResult, $keysCache = '')
	{
		$regexp = "/^\\$(".$keysCache.")(.*)/i";

		if(preg_match($regexp, $query, $arMatch))
		{
			$path = $arMatch[2];
			if(preg_match_all("/\\[([^\\]]+)\\]/", $path, $arPath))
			{
				$r = $arResult[$arMatch[1]];

				while(count($arPath[1]) > 0)
				{
					$key = array_shift($arPath[1]);
					if(isset($r[$key]))
					{
						$r = $r[$key];
					}
					else
					{
						break;
					}
				}

				$query = $r;
			}
		}

		return $query;
	}

	protected static function processBatchStructure($queryParams, $arResult, $keysCache = null)
	{
		$resultQueryParams = array();

		if(is_array($queryParams))
		{
			foreach($queryParams as $key => $param)
			{
				if($keysCache === null)
				{
					$keysCache = implode('|', array_keys($arResult));
				}

				$newKey = self::processBatchElement($key, $arResult);
				if(is_array($param))
				{
					$resultQueryParams[$newKey] = self::processBatchStructure($param, $arResult, $keysCache);
				}
				else
				{
					$resultQueryParams[$newKey] = self::processBatchElement($param, $arResult, $keysCache);
				}
			}
		}

		return $resultQueryParams;
	}

	public static function ParseBatchQuery($query, $arResult)
	{
		$resultQueryParams = array();

		if($query)
		{
			$queryParams = array();
			parse_str($query, $queryParams);

			$queryParams = \Thurly\Main\Text\Encoding::convertEncoding($queryParams, 'utf-8', LANG_CHARSET);

			$resultQueryParams = self::processBatchStructure($queryParams, $arResult);
		}

		return $resultQueryParams;
	}

	/** @deprecated */
	public static function getAuthForEvent($appId, $userId, array $additionalData = array())
	{
		return \Thurly\Rest\Event\Sender::getAuth($appId, $userId, $additionalData, \Thurly\Rest\Event\Sender::getDefaultEventParams());
	}

	/**
	 * @deprecated
	 *
	 * use \Thurly\Rest\OAuth\Auth::get
	 */
	public static function getAuth($appId, $appSecret, $scope, $additionalParams, $user_id = 0)
	{
		global $USER;

		if(CModule::IncludeModule('oauth'))
		{
			if(is_array($scope))
			{
				$scope = implode(',', $scope);
			}

			$oauth = new \Thurly\OAuth\Client\Application();
			$authParams = $oauth->getAuthorizeParamsInternal($appId, COAuthConstants::AUTH_RESPONSE_TYPE_AUTH_CODE, '', '', $scope, array(), $user_id > 0 ? $user_id : $USER->GetID());

			if(is_array($authParams) && isset($authParams[COAuthConstants::AUTH_RESPONSE_TYPE_AUTH_CODE]))
			{
				$res = $oauth->grantAccessTokenInternal($appId, COAuthConstants::GRANT_TYPE_AUTH_CODE, '', $authParams[COAuthConstants::AUTH_RESPONSE_TYPE_AUTH_CODE], $scope, $appSecret, '', $additionalParams, $user_id > 0 ? $user_id : $USER->GetID());

				return $res;
			}
		}

		return false;
	}

	public static function checkAuth($query, $scope, &$res)
	{
		// compatibility fix: other modules use checkAuth instead of /rest/download
		if(!is_array($query))
		{
			$query = array('auth' => $query);
		}

		foreach(GetModuleEvents('rest', 'OnRestCheckAuth', true) as $eventHandler)
		{
			$eventResult = ExecuteModuleEventEx($eventHandler, array($query, $scope, &$res));
			if($eventResult !== null)
			{
				return $eventResult;
			}
		}

		$res = array(
			"error" => "NO_AUTH_FOUND",
			"error_description" => "Wrong authorization data",
		);

		return false;
	}

	public static function makeAuth($res, $application_id = null)
	{
		global $USER;

		if($res['user_id'] > 0)
		{
			$dbRes = CUser::GetByID($res['user_id']);
			$userInfo = $dbRes->fetch();

			if($userInfo && $userInfo['ACTIVE'] === 'Y' && $USER->Authorize($res['user_id'], false, false, $application_id))
			{
				setSessionExpired(true);
				return true;
			}
		}
		elseif($res['user_id'] === 0)
		{
			setSessionExpired(true);
			return true;
		}

		return false;
	}

	public static function checkAppAccess($appId, $appInfo = null)
	{
		global $USER;

		$hasAccess = \CRestUtil::isAdmin();
		if(!$hasAccess)
		{
			if($appInfo === null)
			{
				$appInfo = \Thurly\Rest\AppTable::getByClientId($appId);
			}

			if($appInfo)
			{
				if(!empty($appInfo["ACCESS"]))
				{
					$rights = explode(",", $appInfo["ACCESS"]);
					$hasAccess = $USER->CanAccess($rights);
				}
				else
				{
					$hasAccess = true;
				}
			}
		}

		return $hasAccess;
	}

	public static function updateAppStatus(array $tokenInfo)
	{
		if(array_key_exists('status', $tokenInfo) && array_key_exists('client_id', $tokenInfo))
		{
			$appInfo = \Thurly\Rest\AppTable::getByClientId($tokenInfo['client_id']);
			if($appInfo)
			{
				$dateFinish = $appInfo['DATE_FINISH'] ? $appInfo['DATE_FINISH']->getTimestamp() : '';

				if($tokenInfo['status'] !== $appInfo['STATUS'] || $tokenInfo['date_finish'] != $dateFinish)
				{
					\Thurly\Rest\AppTable::update($appInfo['ID'], array(
						'STATUS' => $tokenInfo['status'],
						'DATE_FINISH' => $tokenInfo['date_finish'] ? \Thurly\Main\Type\DateTime::createFromTimestamp($tokenInfo['date_finish']) : '',
					));
				}
			}
		}
	}

	public static function saveFile($fileContent, $fileName = "")
	{
		if(is_array($fileContent))
		{
			list($fileName, $fileContent) = array_values($fileContent);
		}

		if(strlen($fileContent) > 0 && $fileContent !== 'false') // let it be >0
		{
			$fileContent = base64_decode($fileContent);
			if($fileContent !== false && strlen($fileContent) > 0)
			{
				if(strlen($fileName) <= 0)
				{
					$fileName = md5(mt_rand());
				}
				else
				{
					$fileName = \Thurly\Main\Text\Encoding::convertEncoding($fileName, LANG_CHARSET, 'utf-8');
				}

				$fileName = CTempFile::GetFileName($fileName);

				if(CheckDirPath($fileName))
				{
					file_put_contents($fileName, $fileContent);
					return CFile::MakeFileArray($fileName);
				}
			}
			else
			{
				return null; // wrong file content
			}
		}

		return false;
	}

	public static function CleanApp($appId, $bClean)
	{
		$arFields = array(
			'APP_ID' => $appId,
			'CLEAN' => $bClean
		);

		foreach (GetModuleEvents("rest", "OnRestAppDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($arFields));
		}

		\Thurly\Rest\EventTable::deleteByApp($appId);
		\Thurly\Rest\PlacementTable::deleteByApp($appId);

		if($bClean)
		{
			$dbRes = \Thurly\Rest\AppTable::getById($appId);
			$arApp = $dbRes->fetch();
			if($arApp)
			{
				// delete app settings
				COption::RemoveOption("rest", "options_".$arApp['CLIENT_ID']);
				CUserOptions::DeleteOption("app_options", "params_".$arApp['CLIENT_ID']."_".$arApp['VERSION']);
				// delete app user settings
				CUserOptions::DeleteOption("app_options", "options_".$arApp['CLIENT_ID'], array());

				// clean app iblocks
				CThurlyRestEntity::Clean($arApp['CLIENT_ID']);
			}
		}
	}

	/**
	 * Simple app installation without checks.
	 *
	 * @param string $appCode Application code.
	 *
	 * @return boolean
	 */
	public static function InstallApp($code)
	{
		$result = false;

		if(!\Thurly\Rest\OAuthService::getEngine()->isRegistered())
		{
			try
			{
				\Thurly\Rest\OAuthService::register();
			}
			catch(\Thurly\Main\SystemException $e)
			{
				$result = array('error' => $e->getCode(), 'error_description' => $e->getMessage());
			}
		}

		if(\Thurly\Rest\OAuthService::getEngine()->isRegistered())
		{
			$appDetailInfo = \Thurly\Rest\Marketplace\Client::getInstall($code);

			if($appDetailInfo)
			{
				$appDetailInfo = $appDetailInfo['ITEMS'];
			}

			if($appDetailInfo)
			{
				$queryFields = array(
					'CLIENT_ID' => $appDetailInfo['APP_CODE'],
					'VERSION' => $appDetailInfo['VER'],
				);

				$installResult = \Thurly\Rest\OAuthService::getEngine()
					->getClient()
					->installApplication($queryFields);

				if($installResult['result'])
				{
					$appFields = array(
						'CLIENT_ID' => $installResult['result']['client_id'],
						'CODE' => $appDetailInfo['CODE'],
						'ACTIVE' => \Thurly\Rest\AppTable::ACTIVE,
						'INSTALLED' => !empty($appDetailInfo['INSTALL_URL'])
							? \Thurly\Rest\AppTable::NOT_INSTALLED
							: \Thurly\Rest\AppTable::INSTALLED,
						'URL' => $appDetailInfo['URL'],
						'URL_DEMO' => $appDetailInfo['DEMO_URL'],
						'URL_INSTALL' => $appDetailInfo['INSTALL_URL'],
						'VERSION' => $installResult['result']['version'],
						'SCOPE' => implode(',', $installResult['result']['scope']),
						'STATUS' => $installResult['result']['status'],
						'SHARED_KEY' => $appDetailInfo['SHARED_KEY'],
						'CLIENT_SECRET' => '',
						'APP_NAME' => $appDetailInfo['NAME'],
						'MOBILE' => $appDetailInfo['BXMOBILE'] == 'Y' ? \Thurly\Rest\AppTable::ACTIVE : \Thurly\Rest\AppTable::INACTIVE,
					);

					if(
						$appFields['STATUS'] === \Thurly\Rest\AppTable::STATUS_TRIAL
						|| $appFields['STATUS'] === \Thurly\Rest\AppTable::STATUS_PAID
					)
					{
						$appFields['DATE_FINISH'] = \Thurly\Main\Type\DateTime::createFromTimestamp($installResult['result']['date_finish']);
					}
					else
					{
						$appFields['DATE_FINISH'] = '';
					}

					$existingApp = \Thurly\Rest\AppTable::getByClientId($appFields['CLIENT_ID']);

					if($existingApp)
					{
						$addResult = \Thurly\Rest\AppTable::update($existingApp['ID'], $appFields);
						\Thurly\Rest\AppLangTable::deleteByApp($existingApp['ID']);
					}
					else
					{
						$addResult = \Thurly\Rest\AppTable::add($appFields);
					}

					if($addResult->isSuccess())
					{
						$appId = $addResult->getId();
						if(is_array($appDetailInfo['MENU_TITLE']))
						{
							foreach($appDetailInfo['MENU_TITLE'] as $lang => $langName)
							{
								\Thurly\Rest\AppLangTable::add(array(
									'APP_ID' => $appId,
									'LANGUAGE_ID' => $lang,
									'MENU_NAME' => $langName
								));
							}
						}

						if($appDetailInfo["OPEN_API"] === "Y" && !empty($appFields["URL_INSTALL"]))
						{
							// checkCallback is already called inside checkFields
							$result = \Thurly\Rest\EventTable::add(array(
								"APP_ID" => $appId,
								"EVENT_NAME" => "ONAPPINSTALL",
								"EVENT_HANDLER" => $appFields["URL_INSTALL"],
							));
							if($result->isSuccess())
							{
								\Thurly\Rest\Event\Sender::bind('rest', 'OnRestAppInstall');
							}
						}

						\Thurly\Rest\AppTable::install($appId);

						$result = true;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @deprecated
	 *
	 * use \Thurly\Rest\AppTable::update
	 */
	public static function UpdateApp($appId, $oldVersion)
	{
		$arApp = CThurlyOSApp::GetByID($appId);

		$arFields = array(
			'APP_ID' => $appId,
			'VERSION' => $arApp['VERSION'],
			'PREVIOUS_VERSION' => $oldVersion,
		);

		foreach (GetModuleEvents("rest", "OnRestAppUpdate", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($arFields));
		}

		\Thurly\Rest\EventTable::deleteAppInstaller($appId);

		CUserOptions::DeleteOption("app_options", "params_".$arApp['APP_ID']."_".$arApp['VERSION']);
	}

	public static function getScopeList(array $description = null)
	{
		if($description == null)
		{
			$provider = new \CRestProvider();
			$description = $provider->getDescription();
		}

		unset($description[\CRestUtil::GLOBAL_SCOPE]);
		return array_keys($description);
	}

	public static function getEventList(array $description = null)
	{
		if($description == null)
		{
			$provider = new \CRestProvider();
			$description = $provider->getDescription();
		}

		$eventList = array();
		foreach($description as $scope => $scopeMethods)
		{
			if(
				array_key_exists(\CRestUtil::EVENTS, $scopeMethods)
				&& is_array($scopeMethods[\CRestUtil::EVENTS])
			)
			{
				$eventList[$scope] = array_keys($scopeMethods[\CRestUtil::EVENTS]);
			}
		}

		return $eventList;
	}

	public static function getApplicationToken(array $application)
	{
		if(!empty($application['APPLICATION_TOKEN']))
		{
			return $application['APPLICATION_TOKEN'];
		}
		else
		{
			$secret = array_key_exists("APP_SECRET_ID", $application) ? $application["APP_SECRET_ID"] : $application["CLIENT_SECRET"];
			return md5(\CRestUtil::getMemberId()."|".$application["ID"]."|".$secret."|".$application["SHARED_KEY"]);
		}
	}

	/**
	 * Generates link to file download
	 *
	 * @param array|string $query Params, which will be transferred to download handler
	 * @param CRestServer $server REST Server object
	 *
	 * @return string Absolute file download URL.
	 *
	 * @throws \Thurly\Main\ArgumentNullException
	 * @throws \Thurly\Main\ArgumentTypeException
	 * @throws \Thurly\Main\LoaderException
	 */
	public static function getDownloadUrl($query, \CRestServer $server)
	{
		return static::getSpecialUrl(static::METHOD_DOWNLOAD, $query, $server);
	}

	public static function getLanguage()
	{
		$languageId = '';

		$siteIterator = \Thurly\Main\SiteTable::getList(array(
			'select' => array('LANGUAGE_ID'),
			'filter' => array('=DEF' => 'Y', '=ACTIVE' => 'Y')
		));
		if($site = $siteIterator->fetch())
		{
			$languageId = (string)$site['LANGUAGE_ID'];
		}

		if($languageId == '')
		{
			if(\Thurly\Main\Loader::includeModule('thurlyos'))
			{
				$languageId = \CThurlyOS::getLicensePrefix();
			}
			else
			{
				$languageId = LANGUAGE_ID;
			}
		}

		if($languageId == '')
		{
			$languageId = 'en';
		}

		return $languageId;
	}

	/**
	 * Generates link to file upload
	 *
	 * @param array|string $query Params, which will be transferred to upload handler
	 * @param CRestServer $server REST Server object
	 *
	 * @return string Absolute file download URL.
	 *
	 * @throws \Thurly\Main\ArgumentNullException
	 * @throws \Thurly\Main\ArgumentTypeException
	 * @throws \Thurly\Main\LoaderException
	 */
	public static function getUploadUrl($query, \CRestServer $server)
	{
		return static::getSpecialUrl(static::METHOD_UPLOAD, $query, $server);
	}

	protected static function getSpecialUrl($method, $query, \CRestServer $server)
	{
		if(is_array($query))
		{
			$query = http_build_query($query);
		}

		$query = base64_encode($query."&_=".RandString(32));

		$scope = $server->getScope();
		if($scope === static::GLOBAL_SCOPE)
		{
			$scope = '';
		}

		$signature = $server->getTokenCheckSignature($method, $query);

		$token = $scope
			.static::TOKEN_DELIMITER.$query
			.static::TOKEN_DELIMITER.$signature;


		$authData = $server->getAuthData();

		if($authData['password_id'])
		{
			$auth = $server->getAuth();

			return static::getWebhookEndpoint(
				$auth['ap'],
				$auth['aplogin'],
				$method
			)."?".http_build_query(array(
				'token' => $token,
			));
		}
		else
		{
			$urlParam = array_merge(
				$server->getAuth(),
				array(
					'token' => $token,
				)
			);

			return \CHTTP::URN2URI(
					\Thurly\Main\Config\Option::get('rest', 'server_path', '/rest')."/".$method.".".$server->getTransport()
					."?".http_build_query($urlParam)
			);
		}
	}

	public static function getWebhookEndpoint($ap, $userId, $method = '')
	{
		return \CHTTP::URN2URI(\Thurly\Main\Config\Option::get('rest', 'rest_server_path', '/rest').'/'.urlencode($userId).'/'.urlencode($ap).'/'.($method === '' ? '' : urlencode($method).'/'));
	}

	public static function getAdministratorIdList()
	{
		$adminList = array();

		$dbAdminList = \CGroup::GetGroupUserEx(1);
		while($admin = $dbAdminList->fetch())
		{
			$adminList[] = $admin["USER_ID"];
		}

		return $adminList;
	}

	public static function getApplicationPage($id, $type = 'ID', $appInfo = null)
	{
		if($appInfo === null)
		{
			$appInfo = \Thurly\Rest\AppTable::getByClientId($id);
		}

		if($type !== 'ID' && $type !== 'CODE' && $type !== 'CLIENT_ID')
		{
			$type = 'ID';
		}

		$url = '';
		if(
			empty($appInfo['MENU_NAME'])
			&& empty($appInfo['MENU_NAME_DEFAULT'])
			&& empty($appInfo['MENU_NAME_LICENSE'])
			|| $appInfo['ACTIVE'] === \Thurly\Rest\AppTable::INACTIVE
		)
		{
			$url = 'marketplace/detail/'.urlencode($appInfo['CODE']).'/';
		}
		elseif($appInfo['CODE'] === static::THURLY_1C_APP_CODE)
		{
			$url = 'onec/';
		}
		else
		{
			$url = str_replace(
				'#id#',
				urlencode($appInfo[$type]),
				ltrim(
					\Thurly\Main\Config\Option::get(
						'rest',
						'application_page_tpl',
						'/marketplace/app/#id#/'
					),
					'/'
				)
			);
		}

		return SITE_DIR.$url;
	}

	public static function isSlider()
	{
		return ($_REQUEST['IFRAME'] == 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER');
	}
}
