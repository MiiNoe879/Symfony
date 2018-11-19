<?php

use Thurly\Main;
use Thurly\Main\Localization\Loc;
use Thurly\MessageService;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MessageServiceConfigSenderSmsComponent extends CThurlyComponent
{
	public function executeComponent()
	{

		if (!Main\Loader::includeModule('messageservice'))
		{
			ShowError(Loc::getMessage('MESSAGESERVICE_MODULE_NOT_INSTALLED'));
			return;
		}

		if (!\Thurly\MessageService\Context\User::isAdmin())
		{
			ShowError(Loc::getMessage('MESSAGESERVICE_PERMISSION_DENIED'));
			return;
		}

		$senderId = $this->getSenderId();

		/** @var MessageService\Sender\BaseConfigurable $sender */
		if ($senderId)
		{
			$sender = MessageService\Sender\SmsManager::getSenderById($senderId);
		}
		else
		{
			$sender = MessageService\Sender\SmsManager::getDefaultSender();
		}

		if (!$sender || !$sender->isConfigurable())
		{
			ShowError(Loc::getMessage('MESSAGESERVICE_SENDER_NOT_FOUND'));
			return;
		}

		//Sync sender remote state.
		$sender->sync();

		$this->arResult['sender'] = $sender;
		$this->IncludeComponentTemplate($sender->getId());
	}

	protected function getSenderId()
	{
		return isset($this->arParams['SENDER_ID']) ? (string) $this->arParams['SENDER_ID'] : '';
	}
}