<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage rest
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Rest\OAuth;


use Thurly\Main\Application;
use Thurly\Rest\OAuthService;

class Auth
{
	const AUTH_TYPE = 'oauth';

	const CACHE_TTL = 3600;
	const CACHE_PREFIX = "oauth_";

	const PARAM_LOCAL_USER = 'LOCAL_USER';
	const PARAM_TZ_OFFSET = 'TZ_OFFSET';

	protected static $authQueryParams = array(
		'auth', 'access_token'
	);

	public static function authorizeApplication($clientId, $userId, $state = '')
	{
		if($userId > 0)
		{
			$client = OAuthService::getEngine()->getClient();
			$additionalParams = static::getTokenParams(array(), $userId);

			$codeInfo = $client->getCode($clientId, $state, $additionalParams);

			if($codeInfo['result'])
			{
				return $codeInfo['result'];
			}
			else
			{
				return $codeInfo;
			}
		}

		return false;
	}

	public static function get($appId, $scope, $additionalParams, $userId)
	{
		if($userId > 0)
		{
			$client = OAuthService::getEngine()->getClient();

			$additionalParams = static::getTokenParams($additionalParams, $userId);

			$authResult = $client->getAuth($appId, $scope, $additionalParams);

			if($authResult['result'])
			{
				if($authResult['result']['access_token'])
				{
					$authResult['result']['user_id'] = $userId;
					$authResult['result']['client_id'] = $appId;

					static::store($authResult['result']);
				}

				return $authResult['result'];
			}
			else
			{
				return $authResult;
			}
		}

		return false;
	}

	public static function check($accessToken)
	{
		$authResult = static::restore($accessToken);

		if($authResult === false)
		{
			$client = OAuthService::getEngine()->getClient();
			$tokenInfo = $client->checkAuth($accessToken);

			if($tokenInfo['result'])
			{
				$authResult = $tokenInfo['result'];
				$authResult['user_id'] = $authResult['parameters'][static::PARAM_LOCAL_USER];
				unset($authResult['parameters'][static::PARAM_LOCAL_USER]);
			}
			else
			{
				$authResult = $tokenInfo;
				$authResult['access_token'] = $accessToken;
			}

			static::store($authResult);
		}
		elseif($authResult['expires_in'])
		{
			$authResult['expires_in'] -= time();
		}

		return $authResult;
	}

	public static function onRestCheckAuth(array $query, $scope, &$res)
	{
		$authKey = null;
		foreach(static::$authQueryParams as $key)
		{
			if(array_key_exists($key, $query) && !is_array($query[$key]))
			{
				$authKey = $query[$key];
				break;
			}
		}

		if($authKey)
		{
			$tokenInfo = static::check($authKey);
			if(is_array($tokenInfo))
			{
				$error = array_key_exists('error', $tokenInfo);

				if(!$error)
				{
					\CRestUtil::updateAppStatus($tokenInfo);
				}

				if(!$error && $tokenInfo['expires_in'] <= 0)
				{
					$tokenInfo = array('error' => 'expired_token', 'error_description' => 'The access token provided has expired');
					$error = true;
				}

				if(!$error && $scope !== \CRestUtil::GLOBAL_SCOPE && isset($tokenInfo['scope']))
				{
					$tokenScope = explode(',', $tokenInfo['scope']);
					if(!in_array($scope, $tokenScope))
					{
						$tokenInfo = array('error' => 'insufficient_scope', 'error_description' => 'The request requires higher privileges than provided by the access token');
						$error = true;
					}
				}

				if(!$error && $tokenInfo['user_id'] > 0)
				{
					if(!\CRestUtil::makeAuth($tokenInfo))
					{
						$tokenInfo = array('error' => 'authorization_error', 'error_description' => 'Unable to authorize user');
						$error = true;
					}
					elseif(!\CRestUtil::checkAppAccess($tokenInfo['client_id']))
					{
						$tokenInfo = array('error' => 'user_access_error', 'error_description' => 'The user does not have access to the application.');
						$error = true;
					}
				}

				$res = $tokenInfo;

				$res['parameters_clear'] = static::$authQueryParams;
				$res['auth_type'] = static::AUTH_TYPE;

				$res['parameters_callback'] = array(__CLASS__, 'updateTokenParameters');

				return !$error;
			}

			return false;
		}

		return null;
	}

	public static function updateTokenParameters($tokenInfo)
	{
		$authResult = static::restore($tokenInfo['access_token']);

		if(is_array($authResult))
		{
			if(!is_array($authResult['parameters']))
			{
				$authResult['parameters'] = array();
			}

			$authResult['parameters'] = array_replace_recursive($authResult['parameters'], $tokenInfo['parameters']);

			static::rewrite($authResult);
		}
	}

	protected static function restore($accessToken)
	{
		$managedCache = Application::getInstance()->getManagedCache();

		$authResult = false;
		if($managedCache->read(static::CACHE_TTL, static::getCacheId($accessToken)))
		{
			$authResult = $managedCache->get(static::getCacheId($accessToken));
		}

		return $authResult;
	}

	protected static function store(array $authResult)
	{
		$managedCache = Application::getInstance()->getManagedCache();
		if($managedCache->read(static::CACHE_TTL, static::getCacheId($authResult["access_token"])))
		{
			$authResult['expires_in'] += time();
			$managedCache->set(static::getCacheId($authResult["access_token"]), $authResult);
		}
	}

	protected static function rewrite(array $authResult)
	{
		$managedCache = Application::getInstance()->getManagedCache();

		$managedCache->clean(static::getCacheId($authResult["access_token"]));
		$managedCache->read(static::CACHE_TTL, static::getCacheId($authResult["access_token"]));
		$managedCache->set(static::getCacheId($authResult["access_token"]), $authResult);
	}

	protected static function getCacheId($accessToken)
	{
		return static::CACHE_PREFIX.$accessToken;
	}

	protected static function getTokenParams($additionalParams, $userId)
	{
		if(!is_array($additionalParams))
		{
			$additionalParams = array();
		}

		$additionalParams[static::PARAM_LOCAL_USER] = $userId;
		$additionalParams[static::PARAM_TZ_OFFSET] = \CTimeZone::getOffset();

		return $additionalParams;
	}
}