<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage seo
 * @copyright 2001-2013 Thurly
 */

namespace Thurly\Seo\Engine;

use Thurly\Main\Loader;
use Thurly\Seo\Engine;
use Thurly\Seo\IEngine;

if(!defined("THURLY_CLOUD_ADV_URL"))
{
	define("THURLY_CLOUD_ADV_URL", 'https://cloud-adv.thurly.info');
}

if(!defined("SEO_THURLY_API_URL"))
{
	define("SEO_THURLY_API_URL", THURLY_CLOUD_ADV_URL."/rest/");
}

class Thurly extends Engine implements IEngine
{
	const ENGINE_ID = 'thurly';

	protected $engineId = 'thurly';
	protected $engineRegistered = false;

	CONST API_URL = SEO_THURLY_API_URL;

	public function __construct()
	{
		$this->engine = static::getEngine($this->engineId);
		if($this->engine)
		{
			$this->engineRegistered = true;
			parent::__construct();
		}
	}

	/**
	 * Checks if domain is registered.
	 *
	 * @return bool
	 */
	public function isRegistered()
	{
		return $this->engineRegistered;
	}

	public function getInterface()
	{
		if($this->authInterface === null)
		{
			if(Loader::includeModule('socialservices'))
			{
				$this->authInterface = new \CThurlySeoOAuthInterface($this->engine['CLIENT_ID'], $this->engine['CLIENT_SECRET']);
			}
		}

		return $this->authInterface;
	}

	public function setAuthSettings($settings = null)
	{
		if(is_array($settings) && array_key_exists("expires_in" ,$settings))
		{
			$settings["expires_in"] += time();
		}

		$this->engineSettings['AUTH'] = $settings;
		$this->saveSettings();
	}
}