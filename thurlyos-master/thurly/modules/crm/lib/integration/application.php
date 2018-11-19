<?php

namespace Thurly\Crm\Integration;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Application extends \Thurly\Main\Authentication\Application
{
	protected $validUrls = array(
		"/crm/1c_exchange.php",
		"/thurly/components/thurly/crm.config.external_sale.edit/bus.php"
	);

	public static function OnApplicationsBuildList()
	{
		$result = array(
			"ID" => "ws_crmintegration",
			"NAME" => Loc::getMessage("WS_CRMINTEGRATION_APP_TITLE"),
			"DESCRIPTION" => Loc::getMessage("WS_CRMINTEGRATION_APP_DESC"),
			"SORT" => 150,
			"CLASS" => '\Thurly\Crm\Integration\Application',
			"OPTIONS_CAPTION" => Loc::getMessage('WS_CRMINTEGRATION_APP_OPTIONS_CAPTION'),
			"OPTIONS" => array(
				Loc::getMessage("WS_CRMINTEGRATION_APP_OPTIONS_TITLE_SALE")
			)
		);

		if ("ru" === LANGUAGE_ID)
		{
			$result["OPTIONS"][] = Loc::getMessage("WS_CRMINTEGRATION_APP_OPTIONS_TITLE_1C");
		}
		return $result;
	}
}
