<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage main
 * @copyright 2001-2014 Thurly
 */

namespace Thurly\Main\Authentication;

use Thurly\Main;

class Application
{
	protected $validUrls = array();

	public function __construct()
	{
	}

	/**
	 * Checks the valid scope for the applicaton.
	 *
	 * @return bool
	 */
	public function checkScope()
	{
		/** @var Main\HttpRequest $request */
		$request = Main\Context::getCurrent()->getRequest();
		$realPath = $request->getScriptFile();

		foreach($this->validUrls as $url)
		{
			if(strpos($realPath, $url) === 0)
			{
				return true;
			}
		}

		return false;
	}
}
