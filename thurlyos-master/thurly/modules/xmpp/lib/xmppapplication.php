<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage xmpp
 * @copyright 2001-2014 Thurly
 */

namespace Thurly\Xmpp;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class XmppApplication extends \Thurly\Main\Authentication\Application
{
	/**
	 * Event handler for application passwords.
	 * @return array
	 */
	public static function onApplicationsBuildList()
	{
		return array(
			"ID" => "xmpp",
			"NAME" => Loc::getMessage("xmpp_app_name"),
			"DESCRIPTION" => Loc::getMessage("xmpp_app_desc"),
			"SORT" => 2000,
			"CLASS" => '\Thurly\Xmpp\XmppApplication',
		);
	}
}
