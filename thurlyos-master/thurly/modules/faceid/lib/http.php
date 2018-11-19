<?php
namespace Thurly\FaceId;

use Thurly\Main\ArgumentException;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Http
{
	const MODULE_ID = 'faceid';

	const TYPE_THURLY24 = 'B24';
	const TYPE_CP = 'CP';
	const VERSION = 1;

	private $controllerUrl = 'http://faceid.thurly.info/json/faceid.php';
	private $licenceCode = '';
	private $domain = '';
	private $type = '';
	private $error = null;

	function __construct()
	{
		$this->error = new Error(null, '', '');
		if (defined('FD_CONTROLLER_URL'))
		{
			$this->controllerUrl = FD_CONTROLLER_URL;
		}

		if(defined('BX24_HOST_NAME'))
		{
			$this->licenceCode = BX24_HOST_NAME;
		}
		else
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/classes/general/update_client.php");
			$this->licenceCode = md5("THURLY".\CUpdateClient::GetLicenseKey()."LICENCE");
		}
		$this->type = self::getPortalType();
		$this->domain = self::getServerAddress();

		return true;
	}

	public static function getPortalType()
	{
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

	public static function getServerAddress()
	{
		$publicUrl = \Thurly\Main\Config\Option::get(self::MODULE_ID, "portal_url");

		if ($publicUrl != '')
			return $publicUrl;
		else
			return (\Thurly\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'], Array(80, 443))?'':':'.$_SERVER['SERVER_PORT']);
	}


	public static function requestSign($type, $str)
	{
		if ($type == self::TYPE_THURLY24 && function_exists('bx_sign'))
		{
			return bx_sign($str);
		}
		else
		{
			/** @var string $LICENSE_KEY */
			include($_SERVER["DOCUMENT_ROOT"]."/thurly/license_key.php");
			return md5($str.md5($LICENSE_KEY));
		}
	}

	public function query($command, $params = array(), $waitResponse = true)
	{
		if (strlen($command) <= 0 || !is_array($params))
			return false;

		foreach ($params as $key => $value)
		{
			$params[$key] = empty($value)? '#EMPTY#': $value;
		}

		$params['BX_COMMAND'] = $command;
		$params['BX_LICENCE'] = $this->licenceCode;
		$params['BX_DOMAIN'] = $this->domain;
		$params['BX_TYPE'] = $this->type;
		$params['BX_VERSION'] = self::VERSION;
		$params = \Thurly\Main\Text\Encoding::convertEncodingArray($params, SITE_CHARSET, 'UTF-8');
		$params["BX_HASH"] = self::requestSign($this->type, md5(implode("|", $params)));

		Log::write(Array($this->controllerUrl, $params), 'COMMAND: '.$command);

		$waitResponse = $waitResponse? true: \Thurly\Main\Config\Option::get("faceid", "wait_response");

		$httpClient = new \Thurly\Main\Web\HttpClient(array(
			"socketTimeout" => 20,
			"streamTimeout" => 60,
			"waitResponse" => $waitResponse,
		));
		$httpClient->setHeader('User-Agent', 'Thurly FaceId Client');
		$response = $httpClient->post($this->controllerUrl, $params);

		if (defined('FD_CONTROLLER_URL'))
		{
			Log::write(Array($response), 'COMMAND RESULT: '.$command);
		}

		if ($response === false)
		{
			return array('success' => false, 'result' => array(
				'msg' => 'no connection with controller', 'code' => 'FAIL_CONNECT')
			);
		}

		try
		{
			return \Thurly\Main\Web\Json::decode($response);
		}
		catch (ArgumentException $e)
		{
			return array('success' => false, 'result' => array(
				'msg' => 'wrong response from cloud', 'code' => 'FAIL_RESPONSE')
			);
		}
	}

	public function sendMessage($dialogId, $messageId, $messageText, $userName, $userAge = 30)
	{
		$params = Array(
			'DIALOG_ID' => $dialogId,
			'MESSAGE_ID' => $messageId,
			'MESSAGE_TEXT' => $messageText,
			'USER_NAME' => $userName,
			'USER_AGE' => $userAge
		);

		$query = $this->query(
			'SendMessage',
			$params
		);
		if (isset($query->error))
		{
			$this->error = new Error(__METHOD__, $query->error->code, $query->error->msg);
			return false;
		}

		return $query;
	}

	public function getError()
	{
		return $this->error;
	}
}