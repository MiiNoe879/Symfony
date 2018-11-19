<?php

namespace Sale\Handlers\PaySystem;

use Thurly\Main\Config;
use Thurly\Main\Loader;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\PaySystem;

Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses('sale', array(PaySystem\Manager::getClassNameFromPath('Yandex') => 'handlers/paysystem/yandex/handler.php'));

class YandexReferrerHandler extends YandexHandler
{
	/**
	 * @return array
	 */
	public static function getIndicativeFields()
	{
		return array('BX_HANDLER' => 'YANDEX_REFERRER');
	}
}