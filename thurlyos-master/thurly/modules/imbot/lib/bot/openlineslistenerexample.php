<?php
namespace Thurly\ImBot\Bot;

use Thurly\Main\Localization\Loc;
use Thurly\ImBot\Itr as Itr;

Loc::loadMessages(__FILE__);

class OpenlinesListenerExample
{
	const MODULE_ID = "imbot";
	const BOT_CODE = "openlinelistener";
	
	public static function register(array $params = Array())
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$agentMode = isset($params['AGENT']) && $params['AGENT'] == 'Y';

		if (self::getBotId())
			return $agentMode? "": self::getBotId();

		$botId = \Thurly\Im\Bot::register(Array(
			'CODE' => self::BOT_CODE,
			'TYPE' => \Thurly\Im\Bot::TYPE_OPENLINE,
			'MODULE_ID' => self::MODULE_ID,
			'CLASS' => __CLASS__,
			'METHOD_MESSAGE_ADD' => 'onMessageAdd',
			'METHOD_WELCOME_MESSAGE' => 'onChatStart',
			'METHOD_BOT_DELETE' => 'onBotDelete',
			'PROPERTIES' => Array(
				'NAME' => "Listener Bot for Open Channels (example)",
				'WORK_POSITION' => "Collect and process messages from your open channel",
			)
		));
		if ($botId)
		{
			self::setBotId($botId);
		}

		return $agentMode? "": $botId;
	}

	public static function unRegister()
	{
		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		$result = \Thurly\Im\Bot::unRegister(Array('BOT_ID' => self::getBotId()));
		if ($result)
		{
			self::setBotId(0);
		}

		return $result;
	}

	public static function onChatStart($dialogId, $joinFields)
	{
		if ($joinFields['MESSAGE_TYPE'] == IM_MESSAGE_PRIVATE)
			return false;
		
		\Thurly\ImBot\Log::write($joinFields, 'BOT: START CHAT');
		
		if (\CModule::IncludeModule('imopenlines'))
		{
			$chat = new \Thurly\Imopenlines\Chat($joinFields['CHAT_ID']);
			$chat->endBotSession();
		}
		
		return true;
	}

	public static function onMessageAdd($messageId, $messageFields)
	{
		if ($messageFields['SYSTEM'] == 'Y')
			return false;
		
		if (!\Thurly\Im\User::getInstance($messageFields['FROM_USER_ID'])->isConnector())
		{
			$messageId = self::getLastMessageId($messageFields['DIALOG_ID']);
			if ($messageId)
			{
				self::setLastMessageId($messageFields['DIALOG_ID'], 0);
				\CIMMessenger::DisableMessageCheck();
				\CIMMessenger::Delete($messageId, null, true);
				\CIMMessenger::EnableMessageCheck();
			}
			return false;
		}
		
		\Thurly\ImBot\Log::write($messageFields, 'BOT: RECEIVE MESSAGE');
		
		$answerMessage = strrev($messageFields['MESSAGE']);
		
		$messageId = self::getLastMessageId($messageFields['DIALOG_ID']);
		if ($messageId)
		{
			self::setLastMessageId($messageFields['DIALOG_ID'], 0);
			\CIMMessenger::DisableMessageCheck();
			\CIMMessenger::Delete($messageId, null, true);
			\CIMMessenger::EnableMessageCheck();
		}
		
		$messageId = \Thurly\Im\Bot::addMessage(Array('BOT_ID' => $messageFields['BOT_ID']), Array(
			'DIALOG_ID' => $messageFields['DIALOG_ID'],
			'MESSAGE' => "I`m prepare answer: [i][PUT=".$answerMessage."]".$answerMessage."[/PUT][/i] :)",
			'SYSTEM' => 'Y',
		));
		
		self::setLastMessageId($messageFields['DIALOG_ID'], $messageId);
		
		return true;
	}

	public static function onBotDelete($bodId)
	{
		return self::setBotId(0);
	}

	private static function prepareText($message)
	{
		$message = preg_replace("/\[s\].*?\[\/s\]/i", "-", $message);
		$message = preg_replace("/\[[bui]\](.*?)\[\/[bui]\]/i", "$1", $message);
		$message = preg_replace("/\\[url\\](.*?)\\[\\/url\\]/i".BX_UTF_PCRE_MODIFIER, "$1", $message);
		$message = preg_replace("/\\[url\\s*=\\s*((?:[^\\[\\]]++|\\[ (?: (?>[^\\[\\]]+) | (?:\\1) )* \\])+)\\s*\\](.*?)\\[\\/url\\]/ixs".BX_UTF_PCRE_MODIFIER, "$2", $message);
		$message = preg_replace("/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/i", "$2", $message);
		$message = preg_replace("/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/i", "$2", $message);
		$message = preg_replace("/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/i", "$2", $message);
		$message = preg_replace('#\-{54}.+?\-{54}#s', "", str_replace(array("#BR#"), Array(" "), $message));
		$message = strip_tags($message);

		return trim($message);
	}

	
	public static function getLastMessageId($dialogId)
	{
		$cacheName = "bot_listen_mid_".$dialogId;
		
		$app = \Thurly\Main\Application::getInstance();
		$managedCache = $app->getManagedCache();
		if ($result = $managedCache->read(86400, $cacheName))
		{
			$result = $managedCache->get($cacheName);
		}
		return $result;
	}
	
	public static function setLastMessageId($dialogId, $messageId)
	{
		$cacheName = "bot_listen_mid_".$dialogId;
		
		$app = \Thurly\Main\Application::getInstance();
		$managedCache = $app->getManagedCache();
		$managedCache->clean($cacheName);
		$managedCache->read(86400, $cacheName);
		$managedCache->set($cacheName, $messageId);

		return true;
	}

	public static function getBotId()
	{
		return \Thurly\Main\Config\Option::get(self::MODULE_ID, self::BOT_CODE."_bot_id", 0);
	}

	public static function setBotId($id)
	{
		\Thurly\Main\Config\Option::set(self::MODULE_ID, self::BOT_CODE."_bot_id", $id);
		return true;
	}
}


