<?
use Thurly\Main\ArgumentNullException;
use Thurly\Main\Config\Option;
use Thurly\Main\Entity;
use Thurly\Main\Loader;
use Thurly\Rest\RestException;
use Thurly\Rest\AccessException;
use Thurly\OAuth;

class CRestProvider
	extends \IRestService
{
	const ERROR_BATCH_LENGTH_EXCEEDED = 'ERROR_BATCH_LENGTH_EXCEEDED';
	const ERROR_BATCH_METHOD_NOT_ALLOWED = 'ERROR_BATCH_METHOD_NOT_ALLOWED';

	// default license shown instead of absent or unknown
	const LICENSE_DEFAULT = "project";

	// controller group id => rest license id
	protected static $licenseList = array(
		"project" => "project",
		"corporation" => "corporation",
		"company" => "company",
		"team" => "team",
		"demo" => "demo",
		"nfr" => "nfr",
		"tf" => "tf",
	);

	protected static $arApp = null;
	protected static $arScope = null;
	protected static $arMethodsList = null;

	public function getDescription()
	{
		if(!is_array(self::$arMethodsList))
		{
			$bThurlyOS = CModule::IncludeModule('thurlyos') || IsModuleInstalled('intranet');

			$globalMethods = array(
				\CRestUtil::GLOBAL_SCOPE => array(
					'batch' => array(__CLASS__, 'methodsBatch'),

					'scope' => array(__CLASS__, 'scopeList'),
					'methods' => array(__CLASS__, 'methodsList'),
					'events' => array(__CLASS__, 'eventsList'),

					'server.time' => array(__CLASS__, 'getServerTime'),

					'event.bind' => array(__CLASS__, 'eventBind'),
					'event.unbind' => array(__CLASS__, 'eventUnBind'),
					'event.get' => array(__CLASS__, 'eventGet'),
					'event.test' => array(
						'callback' => array(__CLASS__, 'eventTest'),
						'options' => array()
					),
				),
			);

			$ownMethods = array();

			if($bThurlyOS)
			{
				$ownMethods = array(
					\CRestUtil::GLOBAL_SCOPE => array(
						'app.info' => array(__CLASS__, 'appInfo'),

						'app.option.get' => array(__CLASS__, 'appOptionGet'),
						'app.option.set' => array(__CLASS__, 'appOptionSet'),
						'user.option.get' => array(__CLASS__, 'userOptionGet'),
						'user.option.set' => array(__CLASS__, 'userOptionSet'),

						\CRestUtil::EVENTS => array(
							'OnAppUninstall' => array(
								'rest',
								'OnRestAppDelete',
								array(__CLASS__, 'OnAppEvent'),
								array(
									"sendAuth" => false,
									"category" => \Thurly\Rest\Sqs::CATEGORY_IMPORTANT,
								)
							),
							'OnAppInstall' => array(
								'rest',
								'OnRestAppInstall',
								array(__CLASS__, 'OnAppEvent'),
								array(
									"sendRefreshToken" => true,
									"category" => \Thurly\Rest\Sqs::CATEGORY_IMPORTANT,
								)
							),
							'OnAppUpdate' => array(
								'rest',
								'OnRestAppUpdate',
								array(__CLASS__, 'OnAppEvent'),
								array(
									"sendRefreshToken" => true,
									"category" => \Thurly\Rest\Sqs::CATEGORY_IMPORTANT,
								)
							),
							'OnAppPayment' => array(
								'thurlyos',
								'OnAfterAppPaid',
								array(__CLASS__, 'OnAppPayment'),
								array(
									"category" => \Thurly\Rest\Sqs::CATEGORY_IMPORTANT,
								)
							),
							'OnAppTest' => array(
								'rest',
								'OnRestAppTest',
								array(__CLASS__, 'OnAppEvent'),
								array(
									"sendRefreshToken" => true,
								)
							),
							'OnAppMethodConfirm' => array(
								'rest',
								'OnRestAppMethodConfirm',
								array(__CLASS__, 'OnAppEvent'),
								array(
									"sendAuth" => false,
									"category" => \Thurly\Rest\Sqs::CATEGORY_IMPORTANT,
								)
							),
						),
					),
				);
			}

			$arDescription = array();

			foreach(GetModuleEvents("rest", "OnRestServiceBuildDescription", true) as $arEvent)
			{
				$res = ExecuteModuleEventEx($arEvent);
				if(is_array($res))
				{
					$arDescription = array_merge_recursive($res, $arDescription);
				}
			}

			self::$arMethodsList = array_merge_recursive(
				$globalMethods,
				$ownMethods,
				$arDescription
			);

			if(!array_key_exists('profile', self::$arMethodsList[\CRestUtil::GLOBAL_SCOPE]))
			{
				self::$arMethodsList[\CRestUtil::GLOBAL_SCOPE]['profile'] = array(
					'callback' => array(__CLASS__, 'getProfile'),
					'options' => array(),
				);
			}

			array_change_key_case(self::$arMethodsList, CASE_LOWER);

			foreach (self::$arMethodsList as $scope => $arScopeMethods)
			{
				self::$arMethodsList[$scope] = array_change_key_case(self::$arMethodsList[$scope], CASE_LOWER);
				if(
					array_key_exists(\CRestUtil::EVENTS, self::$arMethodsList[$scope])
					&& is_array(self::$arMethodsList[$scope][\CRestUtil::EVENTS])
				)
				{
					self::$arMethodsList[$scope][\CRestUtil::EVENTS] = array_change_key_case(self::$arMethodsList[$scope][\CRestUtil::EVENTS], CASE_UPPER);
				}
				if(
					array_key_exists(\CRestUtil::PLACEMENTS, self::$arMethodsList[$scope])
					&& is_array(self::$arMethodsList[$scope][\CRestUtil::PLACEMENTS])
				)
				{
					self::$arMethodsList[$scope][\CRestUtil::PLACEMENTS] = array_change_key_case(self::$arMethodsList[$scope][\CRestUtil::PLACEMENTS], CASE_UPPER);
				}
			}
		}

		return self::$arMethodsList;
	}

	public static function getProfile($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$USER->isAuthorized())
		{
			throw new \Thurly\Rest\AccessException("User authorization required");
		}

		$dbRes = CUser::getById($USER->getId());
		$userInfo = $dbRes->fetch();

		$result = array();

		if($userInfo['ACTIVE'] == 'Y')
		{
			$result = array(
				'ID' => $userInfo['ID'],
				'ADMIN' => \CRestUtil::isAdmin(),
				'NAME' => $userInfo['NAME'],
				'LAST_NAME' => $userInfo['LAST_NAME'],
				'PERSONAL_GENDER' => $userInfo['PERSONAL_GENDER'],
			);

			if($userInfo['PERSONAL_PHOTO'] > 0)
			{
				$result['PERSONAL_PHOTO'] = \CRestUtil::GetFile($userInfo["PERSONAL_PHOTO"]);
			}

			$result['TIME_ZONE'] = $userInfo['TIME_ZONE'];
			$result['TIME_ZONE_OFFSET'] = $userInfo['TIME_ZONE_OFFSET'] + date('Z');

			$securityState = array(
				"ID" => $result['ID'],
				"NAME" => $result['NAME'],
				"LAST_NAME" => $result['LAST_NAME'],
			);

			$server->setSecurityState($securityState);
		}

		return $result;
	}


	public static function methodsBatch($arQuery, $start, \CRestServer $server)
	{
		$arQuery = array_change_key_case($arQuery, CASE_UPPER);

		$bHalt = (bool)$arQuery['HALT'];

		$arResult = array(
			'result' => array(),
			'next' => array(),
			'total' => array(),
			'time' => array(),
			'error' => array(),
		);
		if(isset($arQuery['CMD']))
		{
			$cnt = 0;

			$authData = $server->getAuth();
			foreach ($arQuery['CMD'] as $key => $call)
			{
				if(($cnt++) < \CRestUtil::BATCH_MAX_LENGTH)
				{
					$queryData = parse_url($call);

					$method = $queryData['path'];
					$query = $queryData['query'];

					$arParams = \CRestUtil::ParseBatchQuery($query, $arResult);

					if($method === \CRestUtil::METHOD_DOWNLOAD || $method === \CRestUtil::METHOD_UPLOAD)
					{
						$res = array('error' => self::ERROR_BATCH_METHOD_NOT_ALLOWED, 'error_description' => 'Method is not allowed for batch usage');
					}
					else
					{
						if(is_array($authData))
						{
							foreach($authData as $authParam => $authValue)
							{
								$arParams[$authParam] = $authValue;
							}
						}

						$pseudoServer = new \CRestServerBatchItem(array(
							'CLASS' => __CLASS__,
							'METHOD' => $method,
							'QUERY' => $arParams
						));
						$pseudoServer->setApplicationId($server->getClientId());
						$pseudoServer->setAuthKeys(array_keys($authData));
						$pseudoServer->setAuthData($server->getAuthData());
						$pseudoServer->setAuthType($server->getAuthType());

						$res = $pseudoServer->process();

						unset($pseudoServer);
					}
				}
				else
				{

					$res = array('error' => self::ERROR_BATCH_LENGTH_EXCEEDED, 'error_description' => 'Max batch length exceeded');
				}

				if(is_array($res))
				{
					if(isset($res['error']))
					{
						$res['error'] = $res;
					}

					foreach ($res as $k=>$v)
					{
						$arResult[$k][$key] = $v;
					}
				}

				if($res['error'] && $bHalt)
				{
					break;
				}
			}
		}

		return array(
			'result' => $arResult['result'],
			'result_error' => $arResult['error'],
			'result_total' => $arResult['total'],
			'result_next' => $arResult['next'],
			'result_time' => $arResult['time'],
		);
	}

	public static function scopeList($arQuery, $n, \CRestServer $server)
	{
		$arQuery = array_change_key_case($arQuery, CASE_UPPER);

		if($arQuery['FULL'] == true)
		{
			$arScope = \CRestUtil::getScopeList(self::getDescription());
		}
		else
		{
			$arScope = self::getScope($server);
		}

		return $arScope;
	}

	public static function methodsList($arQuery, $n, \CRestServer $server)
	{
		$arMethods = self::getDescription();
		$arScope = array(\CRestUtil::GLOBAL_SCOPE);
		$arResult = array();

		$arQuery = array_change_key_case($arQuery, CASE_UPPER);

		if(isset($arQuery['SCOPE']))
		{
			if($arQuery['SCOPE'] != '')
				$arScope = array($arQuery['SCOPE']);
		}
		elseif($arQuery['FULL'] == true)
		{
			$arScope = array_keys($arMethods);
		}
		else
		{
			$arScope = self::getScope($server);
			$arScope[] = \CRestUtil::GLOBAL_SCOPE;
		}

		foreach ($arMethods as $scope => $arScopeMethods)
		{
			if(in_array($scope, $arScope))
			{
				unset($arScopeMethods[\CRestUtil::METHOD_DOWNLOAD]);
				unset($arScopeMethods[\CRestUtil::METHOD_UPLOAD]);
				unset($arScopeMethods[\CRestUtil::EVENTS]);
				unset($arScopeMethods[\CRestUtil::PLACEMENTS]);

				foreach($arScopeMethods as $method => $methodDesc)
				{
					if(isset($methodDesc["options"]) && $methodDesc["options"]["private"] === true)
					{
						unset($arScopeMethods[$method]);
					}
				}

				$arResult = array_merge($arResult, array_keys($arScopeMethods));
			}
		}

		return $arResult;
	}

	public static function eventsList($arQuery, $n, \CRestServer $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		$arMethods = self::getDescription();
		$arScope = array(\CRestUtil::GLOBAL_SCOPE);
		$arResult = array();

		$arQuery = array_change_key_case($arQuery, CASE_UPPER);

		if(isset($arQuery['SCOPE']))
		{
			if($arQuery['SCOPE'] != '')
				$arScope = array($arQuery['SCOPE']);
		}
		elseif($arQuery['FULL'] == true)
		{
			$arScope = array_keys($arMethods);
		}
		else
		{
			$arScope = self::getScope($server);
			$arScope[] = \CRestUtil::GLOBAL_SCOPE;
		}

		foreach ($arMethods as $scope => $arScopeMethods)
		{
			if(in_array($scope, $arScope) && isset($arScopeMethods[\CRestUtil::EVENTS]))
			{
				$arResult = array_merge($arResult, array_keys($arScopeMethods[\CRestUtil::EVENTS]));
			}
		}

		return $arResult;
	}

	// auth_type = N - user no. N, 0/null/empty - user, who has generated the event;

	public static function eventBind($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if($USER->CanDoOperation('thurlyos_config'))
		{
			$params = array_change_key_case($params, CASE_UPPER);

			$eventName = ToUpper($params['EVENT']);
			$eventCallback = $params['HANDLER'];
			$eventUser = intval($params['AUTH_TYPE']);

			if(strlen($eventName) <= 0)
			{
				throw new ArgumentNullException("EVENT");
			}

			if(strlen($eventCallback) <= 0)
			{
				throw new ArgumentNullException("HANDLER");
			}

			$arApp = self::getApp($server);

			if(\Thurly\Rest\HandlerHelper::checkCallback($eventCallback, $arApp))
			{
				$arScope = self::getScope($server);
				$arScope[] = \CRestUtil::GLOBAL_SCOPE;

				$arDescription = self::getDescription();

				foreach($arScope as $scope)
				{
					if(
						isset($arDescription[$scope])
						&& is_array($arDescription[$scope][\CRestUtil::EVENTS])
						&& array_key_exists($eventName, $arDescription[$scope][\CRestUtil::EVENTS])
					)
					{
						$arEvent = $arDescription[$scope][\CRestUtil::EVENTS][$eventName];
						if(is_array($arEvent))
						{
							$arFields = array(
								'APP_ID' => $arApp['ID'],
								'EVENT_NAME' => $eventName,
								'EVENT_HANDLER' => $eventCallback,
							);

							if($eventUser > 0)
							{
								$arFields['USER_ID'] = $eventUser;
							}

							$result = \Thurly\Rest\EventTable::add($arFields);
							if($result->isSuccess())
							{
								\Thurly\Rest\Event\Sender::bind($arEvent[0], $arEvent[1]);
							}
							else
							{
								$errorMessage = $result->getErrorMessages();
								throw new RestException('Unable to set event handler: '.implode('. ', $errorMessage), RestException::ERROR_CORE);
							}
						}

						return true;
					}
				}

				throw new RestException('Event not found', \Thurly\Rest\EventTable::ERROR_EVENT_NOT_FOUND);
			}
			else
			{
				return false;
			}
		}
		else
		{
			throw new AccessException();
		}
	}

	public static function eventUnbind($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if($USER->CanDoOperation('thurlyos_config'))
		{
			$params = array_change_key_case($params, CASE_UPPER);

			$eventName = ToUpper($params['EVENT']);
			$eventCallback = $params['HANDLER'];

			if(strlen($eventName) <= 0)
			{
				throw new ArgumentNullException("EVENT");
			}

			if(strlen($eventCallback) <= 0)
			{
				throw new ArgumentNullException("HANDLER");
			}

			$arApp = self::getApp($server);

			$filter = array(
				'=APP_ID' => $arApp["ID"],
				'=EVENT_NAME' => $eventName,
				'=EVENT_HANDLER' => $eventCallback,
			);

			if(isset($params['AUTH_TYPE']))
			{
				$filter['=USER_ID'] = intval($params['AUTH_TYPE']);
			}

			$dbRes = \Thurly\Rest\EventTable::getList(array(
				'filter' => $filter
			));

			$cnt = 0;
			while($arEvent = $dbRes->fetch())
			{
				$result = \Thurly\Rest\EventTable::delete($arEvent["ID"]);
				if($result->isSuccess())
				{
					// we shouldn't make Unbind here, it'll be done during the first event call
					$cnt++;
				}
			}

			return array('count' => $cnt);
		}
		else
		{
			throw new AccessException();
		}
	}

	public static function eventGet($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if($USER->CanDoOperation('thurlyos_config'))
		{
			$arEvents = array();

			$arApp = self::getApp($server);

			$dbRes = \Thurly\Rest\EventTable::getList(array(
				"filter" => array(
					"=APP_ID" => $arApp["ID"],
				),
				'order' => array(
					"ID" => "ASC",
				)
			));
			while($arRes = $dbRes->fetch())
			{
				$arEvents[] = array(
					"event" => $arRes['EVENT_NAME'],
					"handler" => $arRes['EVENT_HANDLER'],
					"auth_type" => $arRes['USER_ID'],
				);
			}

			return $arEvents;
		}
		else
		{
			throw new AccessException();
		}
	}

	public static function eventTest($params, $n, \CRestServer $server)
	{
		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		$appInfo = static::getApp($server);

		foreach(GetModuleEvents("rest", "OnRestAppTest", true) as $event)
		{
			ExecuteModuleEventEx($event, array(array(
				"APP_ID" => $appInfo["ID"],
				"QUERY" => $params
			)));
		}

		return 1;
	}

	public static function appInfo($params, $n, \CRestServer $server)
	{
		if(\Thurly\Main\ModuleManager::isModuleInstalled('thurlyos'))
		{
			$licenseInfo = COption::GetOptionString("main", "~controller_group_name");

			list($lang, $licenseName, $additional) = explode("_", $licenseInfo, 3);

			if(!array_key_exists($licenseName, static::$licenseList))
			{
				$licenseName = static::LICENSE_DEFAULT;
			}

			if(!$lang)
			{
				$lang = LANGUAGE_ID;
			}

			$license = $lang."_".static::$licenseList[$licenseName];
		}
		else
		{
			$license = LANGUAGE_ID.'_selfhosted';
		}

		if($server->getClientId())
		{
			$arApp = self::getApp($server);

			$info = \Thurly\Rest\AppTable::getAppStatusInfo($arApp, '');

			$res = array(
				'ID' => $arApp['ID'],
				'CODE' => $arApp['CODE'],
				'VERSION' => intval($arApp['VERSION']),
				'STATUS' => $info['STATUS'],
				'INSTALLED' => $arApp['INSTALLED'] == \Thurly\Rest\AppTable::INSTALLED,
				'PAYMENT_EXPIRED' => $info['PAYMENT_EXPIRED'],
				'DAYS' => $info['DAYS_LEFT'],
				'LICENSE' => $license,
				'LANGUAGE_ID' => \CRestUtil::getLanguage(),
			);

			$server->setSecurityState($res);
		}
		elseif($server->getPasswordId())
		{
			$res = array(
				'SCOPE' => static::getScope($server),
				'LICENSE' => $license,
			);
		}
		else
		{
			throw new AccessException("Application context required");
		}

		foreach(GetModuleEvents('rest', 'OnRestAppInfo', true) as $event)
		{
			$eventData = ExecuteModuleEventEx($event, array($server, &$res));
			if(is_array($eventData))
			{
				if(!isset($res['ADDITIONAL']))
				{
					$res['ADDITIONAL'] = array();
				}

				$res['ADDITIONAL'] = array_merge($res['ADDITIONAL'], $eventData);
			}
		}

		return $res;
	}

	/**
	 * Gets application option values
	 *
	 * @param array $params array([option => option_name])
	 * @param int $n Standard pagination param
	 * @param CRestServer $server Standard Server object link
	 *
	 * @return array|mixed|null|string
	 *
	 * @throws AccessException
	 * @throws ArgumentNullException
	 */
	public static function appOptionGet($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if(!$USER->IsAuthorized())
		{
			throw new AccessException("User authorization required");
		}

		$appOptions = Option::get("rest", "options_".$server->getClientId(), "");

		if(strlen($appOptions) > 0)
		{
			$appOptions = unserialize($appOptions);
		}
		else
		{
			$appOptions = array();
		}

		if(isset($params['option']))
		{
			return isset($appOptions[$params['option']]) ? $appOptions[$params['option']] : null;
		}
		else
		{
			return $appOptions;
		}
	}

	/**
	 * Sets application options values
	 *
	 * @param array $params array(option_name => option_value) || array(options => array(option_name => option_value,....))
	 * @param int $n Standard pagination param
	 * @param CRestServer $server Standard Server object link
	 *
	 * @return true
	 *
	 * @throws AccessException
	 * @throws ArgumentNullException
	 */
	public static function appOptionSet($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if(!isset($params["options"]))
		{
			$params['options'] = $params;
		}

		if(count($params['options']) <= 0)
		{
			throw new ArgumentNullException('options');
		}

		if($USER->CanDoOperation('thurlyos_config'))
		{
			$appOptions = Option::get("rest", "options_".$server->getClientId(), "");
			if(strlen($appOptions) > 0)
			{
				$appOptions = unserialize($appOptions);
			}
			else
			{
				$appOptions = array();
			}

			foreach($params['options'] as $key => $value)
			{
				$appOptions[$key] = $value;
			}

			Option::set('rest', "options_".$server->getClientId(), serialize($appOptions));
		}
		else
		{
			throw new AccessException("Administrator authorization required");
		}

		return true;
	}

	/**
	 * Gets user option values for application
	 *
	 * @param array $params array([option => option_name])
	 * @param int $n Standard pagination param
	 * @param CRestServer $server Standard Server object link
	 *
	 * @return array|mixed|null|string
	 *
	 * @throws AccessException
	 */
	public static function userOptionGet($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if(!$USER->IsAuthorized())
		{
			throw new AccessException("User authorization required");
		}

		$userOptions = \CUserOptions::GetOption("app_options", "options_".$server->getClientId(), array());

		if(isset($params['option']))
		{
			return isset($userOptions[$params['option']]) ? $userOptions[$params['option']] : null;
		}
		else
		{
			return $userOptions;
		}
	}

	/**
	 * Sets user options values for application
	 *
	 * @param array $params array(option_name => option_value) || array(options => array(option_name => option_value,....))
	 * @param int $n Standard pagination param.
	 * @param CRestServer $server Standard Server object link
	 *
	 * @return bool
	 *
	 * @throws AccessException
	 * @throws ArgumentNullException
	 */
	public static function userOptionSet($params, $n, \CRestServer $server)
	{
		global $USER;

		if(!$server->getClientId())
		{
			throw new AccessException("Application context required");
		}

		if(!$USER->IsAuthorized())
		{
			throw new AccessException("User authorization required");
		}

		if(!isset($params["options"]))
		{
			$params['options'] = $params;
		}

		if(count($params['options']) <= 0)
		{
			throw new ArgumentNullException('options');
		}

		$userOptions = \CUserOptions::GetOption("app_options", "options_".$server->getClientId(), array());

		foreach($params['options'] as $key => $value)
		{
			$userOptions[$key] = $value;
		}

		\CUserOptions::SetOption("app_options", "options_".$server->getClientId(), $userOptions);

		return true;
	}

	public static function getServerTime($params)
	{
		return date('c', time());
	}

	public static function OnAppEvent($arParams, $arHandler)
	{
		$arEventFields = $arParams[0];
		if($arEventFields['APP_ID'] == $arHandler['APP_ID'] || $arEventFields['APP_ID'] == $arHandler['APP_CODE'])
		{
			$arEventFields["LANGUAGE_ID"] = \CRestUtil::getLanguage();

			unset($arEventFields['APP_ID']);
			return $arEventFields;
		}
		else
		{
			throw new Exception('Wrong app!');
		}
	}

	public static function OnAppPayment($arParams, $arHandler)
	{
		if($arParams[0] == $arHandler['APP_ID'])
		{
			$app = \Thurly\Rest\AppTable::getByClientId($arHandler['APP_ID']);
			if($app)
			{
				$info = \Thurly\Rest\AppTable::getAppStatusInfo($app, '');

				return array(
					'CODE' => $app['CODE'],
					'VERSION' => intval($app['VERSION']),
					'STATUS' => $info['STATUS'],
					'PAYMENT_EXPIRED' => $info['PAYMENT_EXPIRED'],
					'DAYS' => $info['DAYS_LEFT']
				);
			}
		}

		throw new Exception('Wrong app!');
	}

	protected static function getApp(\CRestServer $server)
	{
		if(self::$arApp == null)
		{
			if(CModule::IncludeModule('oauth'))
			{
				$client = OAuth\Base::instance($server->getClientId());

				if($client)
				{
					self::$arApp = $client->getClient();

					if(is_array(self::$arApp) && is_array(self::$arApp['SCOPE']))
					{
						self::$arApp['SCOPE'] = implode(',', self::$arApp['SCOPE']);
					}
				}
			}
			elseif($server->getClientId())
			{
				self::$arApp = \Thurly\Rest\AppTable::getByClientId($server->getClientId());
			}
			else
			{
				throw new AccessException("Application context required");
			}
		}

		return self::$arApp;
	}

	protected static function getScope(\CRestServer $server)
	{
		if(self::$arScope == null)
		{
			self::$arScope = array();

			$authData = $server->getAuthData();

			$scopeList = explode(',', $authData['scope']);

			$arServerDesc = $server->getServiceDescription();

			self::$arScope = array();
			foreach($scopeList as $scope)
			{
				if(array_key_exists($scope, $arServerDesc))
				{
					self::$arScope[] = $scope;
				}
			}
		}

		return self::$arScope;
	}
}
?>