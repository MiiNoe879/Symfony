<?php
namespace Thurly\ImBot\Bot;

use Thurly\Main\Localization\Loc;

abstract class Base
{
	const MODULE_ID = "imbot";
	const BOT_CODE = "";

	protected static $lastError = null;

	public static function getBotId()
	{
		$class = self::getClassName();
		if (!$class::BOT_CODE)
			return false;

		return \Thurly\Main\Config\Option::get(self::MODULE_ID, $class::BOT_CODE."_bot_id", 0);
	}

	public static function setBotId($id)
	{
		$class = self::getClassName();
		if (!$class::BOT_CODE)
			return false;

		\Thurly\Main\Config\Option::set(self::MODULE_ID, $class::BOT_CODE."_bot_id", $id);

		return true;
	}

	public static function getBotOption($userId, $name, $value = false)
	{
		$class = self::getClassName();
		if (!$class::BOT_CODE)
			return false;

		return \CUserOptions::GetOption(self::MODULE_ID, $class::BOT_CODE.'_'.$name, $value, $userId);
	}

	public static function setBotOption($userId, $name, $value)
	{
		$class = self::getClassName();
		if (!$class::BOT_CODE)
			return false;

		\CUserOptions::SetOption(self::MODULE_ID, $class::BOT_CODE.'_'.$name, $value, false, $userId);

		return true;
	}

	public static function onChatStart($dialogId, $joinFields)
	{
		return true;
	}

	public static function onMessageAdd($messageId, $messageFields)
	{
		return true;
	}

	public static function onAnswerAdd($command, $params)
	{
		return null;
	}

	public static function onCommandAdd($messageId, $messageFields)
	{
		return true;
	}

	public static function onCommandLang($command, $lang = null)
	{
		return false;
	}

	public static function onBotDelete($bodId)
	{
		return self::setBotId(0);
	}

	public static function uploadAvatar($lang = LANGUAGE_ID)
	{
		$avatarUrl = '';

		$class = self::getClassName();
		if (!$class::BOT_CODE)
			return $avatarUrl;

		if (\Thurly\Main\IO\File::isFileExists(\Thurly\Main\Application::getDocumentRoot().'/thurly/modules/imbot/install/avatar/'.$class::BOT_CODE.'/'.$lang.'.png'))
		{
			$avatarUrl = \Thurly\Main\Application::getDocumentRoot().'/thurly/modules/imbot/install/avatar/'.$class::BOT_CODE.'/'.$lang.'.png';
		}
		else if (\Thurly\Main\IO\File::isFileExists(\Thurly\Main\Application::getDocumentRoot().'/thurly/modules/imbot/install/avatar/'.$class::BOT_CODE.'/default.png'))
		{
			$avatarUrl = \Thurly\Main\Application::getDocumentRoot().'/thurly/modules/imbot/install/avatar/'.$class::BOT_CODE.'/default.png';
		}

		$avatarUrl = $avatarUrl? \CFile::MakeFileArray($avatarUrl): '';

		return $avatarUrl;
	}

	public static function uploadIcon($iconName)
	{
		if (strlen($iconName) <= 0)
			return false;
		
		$iconId = false;

		$class = self::getClassName();
		if (!$class::BOT_CODE)
			return $iconId;

		if (\Thurly\Main\IO\File::isFileExists(\Thurly\Main\Application::getDocumentRoot().'/thurly/modules/imbot/install/icon/'.$class::BOT_CODE.'/'.$iconName.'.png'))
		{
			$iconId = \Thurly\Main\Application::getDocumentRoot().'/thurly/modules/imbot/install/icon/'.$class::BOT_CODE.'/'.$iconName.'.png';
		}

		if ($iconId)
		{
			$iconId = \CFile::SaveFile(\CFile::MakeFileArray($iconId), 'imbot');
		}

		return $iconId;
	}

	/**
	 * @return \Thurly\ImBot\Bot\Base
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	public static function getError()
	{
		if (!self::$lastError)
		{
			self::$lastError = new \Thurly\ImBot\Error(null, '', '');
		}
		return self::$lastError;
	}
}