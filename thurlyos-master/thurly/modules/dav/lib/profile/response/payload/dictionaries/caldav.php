<?php

namespace Thurly\Dav\Profile\Response\Payload\Dictionaries;

use Thurly\Main\ModuleManager;


/**
 * Class CalDav
 * @package Thurly\Dav\Profile\Response\Payload\Dictionaries
 */
class CalDav extends ComponentBase
{
	const TEMPLATE_DICT_NAME = 'caldav';

	/**
	 * @return bool
	 */
	public function isAvailable()
	{
		return ModuleManager::isModuleInstalled('calendar');
	}

}