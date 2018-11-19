<?php
namespace Thurly\Rest\Marketplace;

use Thurly\Main\ArgumentException;
use Thurly\Main\Context;
use Thurly\Main\Loader;
use Thurly\Main\Text\Encoding;
use Thurly\Main\Web\HttpClient;
use Thurly\Main\Web\Json;

if(!defined('REST_MARKETPLACE_URL'))
{
	define('REST_MARKETPLACE_URL', 'https://www.1c-thurly.ru/buy_tmp/b24_app.php');
}

class Transport
{
	const SERVICE_URL = REST_MARKETPLACE_URL;

	const SOCKET_TIMEOUT = 10;
	const STREAM_TIMEOUT = 10;

	const METHOD_GET_LAST = 'get_last';
	const METHOD_GET_DEV = 'get_dev';
	const METHOD_GET_BEST = 'get_best';
	const METHOD_GET_BUY = 'get_buy';
	const METHOD_GET_UPDATES = 'get_updates';
	const METHOD_GET_CATEGORIES = 'get_categories';
	const METHOD_GET_CATEGORY = 'get_category';
	const METHOD_GET_TAG = 'get_tag';
	const METHOD_GET_APP = 'get_app';
	const METHOD_GET_INSTALL = 'get_app_install';
	const METHOD_SET_INSTALL = 'is_installed';
	const METHOD_SEARCH_APP = 'search_app';

	protected static $instance = null;

	/**
	 * Resturns class instance.
	 *
	 * @return \Thurly\Rest\Marketplace\Transport
	 */
	public static function instance()
	{
		if(static::$instance == null)
		{
			static::$instance = new self();
		}

		return static::$instance;
	}


	public function __construct()
	{
	}

	public function call($method, $fields = array())
	{
		$query = $this->prepareQuery($method, $fields);

		$httpClient = new HttpClient(array(
			'socketTimeout' => static::SOCKET_TIMEOUT,
			'streamTimeout' => static::STREAM_TIMEOUT,
		));

		$response = $httpClient->post(self::SERVICE_URL, $query);

		return $this->prepareAnswer($response);
	}

	public function batch($actions)
	{
		$query = array();
		foreach($actions as $key => $batch)
		{
			$query[$key] = $this->prepareQuery($batch[0], $batch[1]);
		}

		$query = array('batch' => $query);

		$httpClient = new HttpClient();
		$response = $httpClient->post(self::SERVICE_URL, $query);

		return $this->prepareAnswer($response);
	}

	protected function prepareQuery($method, $fields)
	{
		if(!is_array($fields))
		{
			$fields = array();
		}

		$fields['action'] = $method;
		$fields['lang'] = LANGUAGE_ID;

		if(Loader::includeModule('thurlyos'))
		{
			$fields['tariff'] = \CThurlyOS::getLicensePrefix();
			$fields['host_name'] = BX24_HOST_NAME;
		}
		else
		{
			$request = Context::getCurrent()->getRequest();
			$fields['host_name'] = $request->getHttpHost();
		}

		return Encoding::convertEncoding($fields, LANG_CHARSET, 'utf-8');
	}

	protected function prepareAnswer($response)
	{
		$responseData = false;
		if($response && strlen($response) > 0)
		{
			try
			{
				$responseData = Json::decode($response);
			}
			catch(ArgumentException $e)
			{
				$responseData = false;
			}
		}
		return is_array($responseData) ? $responseData : false;
	}
}