<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage rest
 * @copyright 2001-2016 Thurly
 */

namespace Thurly\Rest\Dictionary;

use Thurly\Main\Application;
use Thurly\Main\ArgumentException;
use Thurly\Main\Type\Dictionary;
use Thurly\Main\Web\HttpClient;
use Thurly\Main\Web\Json;
use Thurly\Main\Web\Uri;

class RemoteDictionary extends Dictionary
{
	const ID = 'generic';
	const BASE_URL = 'https://www.thurlyos.ru/util/';

	const CACHE_TTL = 86400;
	const CACHE_PREFIX = 'rest_dictionary';

	protected $language = null;

	public function __construct()
	{
		$this->language = LANGUAGE_ID;

		$values = $this->init();

		parent::__construct($values);
	}

	public function setLanguage($language)
	{
		if($language !== $this->language)
		{
			$this->language = $language;
			$this->set($this->init());
		}
	}

	protected function init()
	{
		$managedCache = Application::getInstance()->getManagedCache();
		if($managedCache->read(static::CACHE_TTL, $this->getCacheId()))
		{
			$dictionary = $managedCache->get($this->getCacheId());
		}
		else
		{
			$dictionary = $this->load();
			$managedCache->set($this->getCacheId(), $dictionary);
		}

		return $dictionary;
	}

	protected function load()
	{
		$httpClient = new HttpClient();

		$uri = $this->getDictionaryUri();

		$httpResult = $httpClient->get($uri->getLocator());

		try
		{
			$result = Json::decode($httpResult);
		}
		catch(ArgumentException $e)
		{
			$result = null;
		}

		return $result;
	}

	protected function getCacheId()
	{
		return static::CACHE_PREFIX.'/'.static::ID.'/'.$this->language;
	}

	/**
	 * @return Uri
	 */
	protected function getDictionaryUri()
	{
		$uri = new Uri(static::BASE_URL);
		$uri->addParams(array(
			'type' => static::ID,
			'lng' => $this->language,
		));

		return $uri;
	}
}
