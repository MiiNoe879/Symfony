<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage rest
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Rest;


use Thurly\Main\ArgumentException;
use Thurly\Main\Context;
use Thurly\Main\Localization\Loc;
use Thurly\Main\SystemException;
use Thurly\Main\Web\HttpClient;
use Thurly\Main\Web\Json;
use Thurly\Rest\OAuth\Engine;

Loc::loadMessages(__FILE__);

if(!defined("THURLY_OAUTH_URL"))
{
	$defaultValue = \Thurly\Main\Config\Option::get('rest', 'oauth_server', 'https://oauth.thurly.info');
	define("THURLY_OAUTH_URL", $defaultValue);
}

if(!defined('THURLYREST_URL'))
{
	define('THURLYREST_URL', THURLY_OAUTH_URL);
}


class OAuthService
{
	const SERVICE_URL = THURLYREST_URL;
	const CLIENT_TYPE = 'B';

	const REGISTER = "/oauth/register/";

	protected static $engine = null;

	/**
	 * @return \Thurly\Rest\OAuth\Engine
	 */
	public static function getEngine()
	{
		if(!static::$engine)
		{
			static::$engine = new Engine();
		}

		return static::$engine;
	}

	public static function register()
	{
		$httpClient = new HttpClient();

		$queryParams = array(
			"redirect_uri" => static::getRedirectUri(),
			"type" => static::CLIENT_TYPE,
		);

		$memberId = \CRestUtil::getMemberId();
		if($memberId !== null)
		{
			$queryParams["member_id"] = $memberId;
		}

		$queryParams = \CRestUtil::signLicenseRequest($queryParams, static::getEngine()->getLicense());

		$httpResult = $httpClient->post(static::SERVICE_URL.static::REGISTER, $queryParams);

		try
		{
			$result = Json::decode($httpResult);
		}
		catch(ArgumentException $e)
		{
			$result = array(
				"error" => "Wrong answer from service: ".$httpResult,
			);
		}

		if($result["error"])
		{
			throw new SystemException($result["error"]);
		}
		else
		{
			static::getEngine()->setAccess($result);
		}
	}

	public static function unregister()
	{
		if(static::getEngine()->isRegistered())
		{
			static::getEngine()->clearAccess();
		}
	}

	public static function getMemberId()
	{
		if(static::getEngine()->isRegistered())
		{
			return md5(static::getEngine()->getClientId());
		}
		else
		{
			return null;
		}
	}

	public static function getRedirectUri()
	{
		$request = Context::getCurrent()->getRequest();

		$host = defined('BX24_HOST_NAME') ? BX24_HOST_NAME : $request->getHttpHost();

		return ($request->isHttps() ? 'https' : 'http').'://'.preg_replace("/:(443|80)$/", "", $host);
	}
}