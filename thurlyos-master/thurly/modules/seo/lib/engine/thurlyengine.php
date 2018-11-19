<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage seo
 * @copyright 2001-2013 Thurly
 */
namespace Thurly\Seo\Engine;

use Thurly\Main\Text;
use Thurly\Main\Web;
use Thurly\Seo\Engine;
use Thurly\Seo\Service;

class ThurlyEngine extends Engine
{
	protected $engineId = 'thurly_generic';

	public function __construct()
	{
		parent::__construct();
	}

	public function getProxy()
	{
		return Service::getEngine();
	}

	public function getAuthSettings()
	{
		$proxy = $this->getProxy();
		if($proxy && $proxy->getAuthSettings())
		{
			return parent::getAuthSettings();
		}

		return null;
	}
}