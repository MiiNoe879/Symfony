<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\ImOpenLines;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Helper
{
	const ENUM_TEMPLATE_TRANSPARENT = 'transp';
	const ENUM_TEMPLATE_COLORED = 'colored';

	public static function getTemplateList()
	{
		return array(
			static::ENUM_TEMPLATE_TRANSPARENT => Loc::getMessage('IMOL_HELPER_TEMPLATE_TRANSPARENT'),
			static::ENUM_TEMPLATE_COLORED => Loc::getMessage('IMOL_HELPER_TEMPLATE_COLORED'),
		);
	}
	
	public static function getAddUrl()
	{
		return \Thurly\ImOpenLines\Common::getPublicFolder() . "list/edit.php?ID=0";
	}
	
	public static function getEditUrl($lineId = 0)
	{
		$lineId = intval($lineId);
		return \Thurly\ImOpenLines\Common::getPublicFolder() . 'list/' . ($lineId? "edit.php?ID=".$lineId: '');
	}
	
	public static function getListUrl()
	{
		return \Thurly\ImOpenLines\Common::getPublicFolder() . 'list/';
	}

	public static function getConnectorUrl($connectorId, $lineId = 0)
	{
		$lineId = intval($lineId);

		if(!empty($connectorId))
			return \Thurly\ImOpenLines\Common::getPublicFolder() . 'connector/?ID=' . $connectorId . ($lineId ? "&LINE=" . $lineId : '');
		else
			return \Thurly\ImOpenLines\Common::getPublicFolder() . 'list/edit.php?ID=' . ($lineId ? $lineId : '0');
	}
	
	public static function isAvailable()
	{
		return \Thurly\ImOpenLines\Config::available();
	}
	
	public static function isLiveChatAvailable()
	{
		return \Thurly\ImOpenLines\LiveChatManager::available();
	}
}