<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage security
 * @copyright 2001-2013 Thurly
 */

/**
 * Class CSecurityCloudMonitorRequest
 * @since 12.5.0
 */
class CSecurityCloudMonitorRequest
{
	const THURLY_CHECKER_URL_PATH = "/thurly/site_checker.php";
	const REMOTE_STATUS_OK = "ok";
	const REMOTE_STATUS_ERROR = "error";
	const REMOTE_STATUS_FATAL_ERROR = "fatal_error";
	const TIMEOUT = 10;

	private static $validActions = array("check", "get_results");
	protected static $trustedHosts = array("www.1c-thurly.ru", "www.thurlysoft.com", "www.thurly.de");
	protected $response = array();
	protected $checkingToken = "";
	protected $protocolVersion = 2;

	public function __construct($action, $protocolVersion, $token = "")
	{
		if(!in_array($action, self::$validActions))
			return null;

		$this->checkingToken = $token;
		$this->response = $this->receiveData($action);
		$this->protocolVersion = $protocolVersion;
	}

	/**
	 * @param string $checkingToken
	 * @return $this
	 */
	public function setCheckingToken($checkingToken)
	{
		$this->checkingToken = $checkingToken;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCheckingToken()
	{
		return $this->checkingToken;
	}

	/**
	 * Make a request to the Thurly server and returns the result
	 * @param array $action
	 * @return array|bool
	 */
	public function receiveData($action)
	{
		$payload = $this->getPayload($action, false);
		if(!$payload)
			return false;

		$response = self::sendRequest($payload);
		if(!$response)
		{
			$response = array();
		}

		if(!isset($response["status"]))
		{
			$response["status"] = self::REMOTE_STATUS_FATAL_ERROR;
			$response["error_text"] = GetMessage("SECURITY_SITE_CHECKER_CONNECTION_ERROR");
		}

		return $response;
	}

	/**
	 * @return bool
	 */
	public function isOk()
	{
		return 	$this->checkStatus(self::REMOTE_STATUS_OK);
	}

	/**
	 * @return bool
	 */
	public function isFatalError()
	{
		return 	$this->checkStatus(self::REMOTE_STATUS_FATAL_ERROR);
	}

	/**
	 * @return bool
	 */
	public function isError()
	{
		return 	$this->checkStatus(self::REMOTE_STATUS_ERROR);
	}

	/**
	 * @return bool
	 */
	public function isSuccess()
	{
		return 	(isset($this->response["status"]));
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function getValue($key)
	{
		if(isset($this->response[$key]))
		{
			return $this->response[$key];
		}
		else
		{
			return "";
		}
	}

	/**
	 * @param string $status
	 * @return bool
	 */
	protected function checkStatus($status)
	{
		return 	(isset($this->response["status"]) && $this->response["status"] === $status);
	}

	/**
	 * Generate payload for request to Thurly
	 * @param string $action - "check" or "receive_results"
	 * @param bool $collectInformation
	 * @return string
	 */
	protected function getPayload($action = "check", $collectInformation = true)
	{
		if(!in_array($action, self::$validActions))
			return false;

		$payload = array(
				"action" => $action,
				"host"   => self::getHostName(),
				"lang"   => LANGUAGE_ID,
				"license_key" => self::getLicenseKey(),
				"testing_token" => $this->checkingToken,
				"version" => $this->protocolVersion
			);

		if($collectInformation || $action === "check")
		{
			$payload["system_information"] = base64_encode(serialize(self::getSystemInformation()));
			$payload["additional_information"] = base64_encode(serialize(self::getAdditionalInformation()));
		}

		return $payload;
	}


	/**
	 * @param string $response
	 * @return array
	 */
	protected static function decodeResponse($response)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$result = json_decode($response, true);
		if(!defined("BX_UTF"))
			$result = $APPLICATION->ConvertCharsetArray($result, "UTF-8", LANG_CHARSET);

		return $result;
	}

	/**
	 * Return Thurly Cloud Security web service url
	 *
	 * @param string $host Thurly security scanner host.
	 * @return string
	 */
	protected static function buildCheckerUrl($host)
	{
		return sprintf('https://%s%s', $host, self::THURLY_CHECKER_URL_PATH);
	}

	/**
	 * Return Thurly Cloud Security host
	 *
	 * @return string
	 */
	protected static function getServiceHost()
	{
		return COption::GetOptionString("main", "update_site", "www.thurlysoft.com");
	}

	/**
	 * Send request to Thurly (check o receive)
	 * @param array $payload
	 * @return array|bool
	 */
	protected static function sendRequest(array $payload)
	{
		$targetHost = static::getServiceHost();
		// Trusted host *must* have a valid SSL certificate
		$skipSslValidation = !in_array($targetHost, static::$trustedHosts, true);
		$httpClient = new \Thurly\Main\Web\HttpClient(array(
			'disableSslVerification' => $skipSslValidation,
			'streamTimeout' => static::TIMEOUT
		));

		$response = $httpClient->post(self::buildCheckerUrl($targetHost), $payload);
		if ($response && $httpClient->getStatus() == 200)
		{
			return self::decodeResponse($response);
		}

		return false;
	}

	/**
	 * Return License key, your Captain Obvious
	 * @return string
	 */
	protected static function getLicenseKey()
	{
		if (defined("LICENSE_KEY"))
		{
			$licenseKey = LICENSE_KEY;
		}
		else
		{
			$licenseKey = "DEMO";
		}
		return md5($licenseKey);
	}

	/**
	 * Return system information, such as php version
	 * @return array
	 */
	protected static function getSystemInformation()
	{
		return CSecuritySystemInformation::getSystemInformation();
	}

	/**
	 * Return additional information, such as P&P or LDAP server information
	 *
	 * @since 14.5.4
	 * @return array
	 */
	protected static function getAdditionalInformation()
	{
		return CSecuritySystemInformation::getAdditionalInformation();
	}

	/**
	 * Return host name for site checking
	 * @return string
	 */
	protected function getHostName()
	{
		$sheme = (CMain::IsHTTPS() ? "https" : "http")."://";
		$serverPort = self::getServerPort();
		$url = self::getDomainName();
		$url .= ($serverPort && strpos($url, ":") === false) ? ":".$serverPort : "";
		return $sheme.$url;
	}

	/**
	 * Return current server port, except 80 and 443
	 * @return int|bool
	 */
	protected static function getServerPort()
	{
		if($_SERVER["SERVER_PORT"] && !in_array($_SERVER["SERVER_PORT"], array(80, 443)))
			return $_SERVER["SERVER_PORT"];
		else
			return false;
	}

	/**
	 * Return current domain name (in puny code for cyrillic domain)
	 * @return string
	 */
	protected static function getDomainName()
	{
		return CSecuritySystemInformation::getCurrentHost();
	}
}
