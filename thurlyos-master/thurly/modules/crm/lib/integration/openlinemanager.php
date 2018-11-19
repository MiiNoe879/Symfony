<?php
namespace Thurly\Crm\Integration;
use Thurly\Main;
use Thurly\Main\Loader;
use Thurly\Main\ModuleManager;
use Thurly\Main\Localization\Loc;
use Thurly\Imopenlines;
use Thurly\Im;

Loc::loadMessages(__FILE__);

class OpenLineManager
{
	/** @var bool|null  */
	private static $isEnabled = null;
	private static $supportedTypes = array(
		'IM' => array(
			'IMOL' => true,
			'OPENLINE' => true,
			'THURLY24' => true,
			'FACEBOOK' => true,
			'TELEGRAM' => true,
			'VK' => true,
			'VIBER' => true,
			'INSTAGRAM' => true
		)
	);

	/**
	 * Check if current manager enabled.
	 * @return bool
	 */
	public static function isEnabled()
	{
		if(self::$isEnabled === null)
		{
			self::$isEnabled = ModuleManager::isModuleInstalled('imopenlines')
				&& Loader::includeModule('imopenlines');
		}
		return self::$isEnabled;
	}

	public static function prepareMultiFieldLinkAttributes($typeName, $valueTypeID, $value)
	{
		if(!(isset(self::$supportedTypes[$typeName]) && isset(self::$supportedTypes[$typeName][$valueTypeID])))
		{
			return null;
		}

		$items = explode('|', $value);
		if(!(is_array($items) && count($items) > 2 && $items[0] === 'imol'))
		{
			return null;
		}

		$typeID = $items[1];
		$suffix = strtoupper(preg_replace('/[^a-z0-9]/i', '', $typeID));
		$text = Loc::getMessage("CRM_OPEN_LINE_{$suffix}");
		if($text === null)
		{
			$text = Loc::getMessage('CRM_OPEN_LINE_SEND_MESSAGE');
		}

		return array(
			'HREF' => '#',
			'ONCLICK' => "if(typeof(top.BXIM)!=='undefined') top.BXIM.openMessengerSlider('{$value}', {RECENT: 'N', MENU: 'N'}); return BX.PreventDefault(event);",
			'TEXT' => $text,
			'TITLE' => $text
		);
	}

	public static function getSessionMessages($sessionID, $limit = 20)
	{
		if(!(ModuleManager::isModuleInstalled('im')
			&& ModuleManager::isModuleInstalled('imopenlines')
			&& Loader::includeModule('im')
			&& Loader::includeModule('imopenlines'))
		)
		{
			return array();
		}

		$dbResult = Imopenlines\Model\SessionTable::getList(
			array(
				'filter' => array('ID' => $sessionID),
				'select' => array('ID', 'CHAT_ID', 'START_ID', 'END_ID')
			)
		);
		$sessionFields = $dbResult->fetch();
		if(!is_array($sessionFields))
		{
			return array();
		}

		$filter =  array('=CHAT_ID' => $sessionFields['CHAT_ID'], '>AUTHOR_ID' => 0);

		if(isset($sessionFields['START_ID']) && $sessionFields['START_ID'] > 0)
		{
			$filter['>ID'] = $sessionFields['START_ID'] - 1;
		}

		if(isset($sessionFields['END_ID']) && $sessionFields['END_ID'] > 0)
		{
			$filter['<ID'] = $sessionFields['END_ID'] + 1;
		}

		if($limit <= 0)
		{
			$limit = 20;
		}

		$dbResult = Im\Model\MessageTable::getList(
			array(
				'select' => array('MESSAGE', 'AUTHOR_ID'),
				'filter' => $filter,
				'limit' => $limit,
				'order' => array('CHAT_ID' => 'ASC', 'ID' => 'ASC')
			)
		);

		$results = array();
		while ($messageFields = $dbResult->fetch())
		{
			$messageFields['MESSAGE'] = Im\Text::removeBbCodes($messageFields['MESSAGE']);
			$messageFields['IS_EXTERNAL'] = Im\User::getInstance($messageFields['AUTHOR_ID'])->isConnector();

			$results[] = $messageFields;
		}
		return $results;
	}
}