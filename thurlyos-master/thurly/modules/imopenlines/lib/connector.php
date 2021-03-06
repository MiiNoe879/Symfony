<?php

namespace Thurly\ImOpenLines;

use \Thurly\Main,
	\Thurly\ImConnector\Output,
	\Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Connector
{
	const TYPE_LIVECHAT = 'livechat';
	const TYPE_NETWORK = 'network';
	const TYPE_CONNECTOR = 'connector';

	private $error = null;
	private $moduleLoad = false;

	public function __construct()
	{
		$imLoad = \Thurly\Main\Loader::includeModule('im');
		$pullLoad = \Thurly\Main\Loader::includeModule('pull');
		$connectorLoad = \Thurly\Main\Loader::includeModule('imconnector');
		if ($imLoad && $pullLoad && $connectorLoad)
		{
			$this->error = new Error(null, '', '');
			$this->moduleLoad = true;
		}
		else
		{
			if (!$imLoad)
			{
				$this->error = new Error(__METHOD__, 'IM_LOAD_ERROR', Loc::getMessage('IMOL_CHAT_ERROR_IM_LOAD'));
			}
			elseif (!$pullLoad)
			{
				$this->error = new Error(__METHOD__, 'PULL_LOAD_ERROR', Loc::getMessage('IMOL_CHAT_ERROR_PULL_LOAD'));
			}
			elseif (!$connectorLoad)
			{
				$this->error = new Error(__METHOD__, 'CONNECTOR_LOAD_ERROR', Loc::getMessage('IMOL_CHAT_ERROR_CONNECTOR_LOAD'));
			}
		}
	}

	private function isModuleLoad()
	{
		return $this->moduleLoad;
	}

	public function addMessage($params)
	{
		if (empty($params))
		{
			return false;
		}

		$voteSession = false;
		if (
			(
				strpos($params['message']['text'], '1') === 0
				|| strpos($params['message']['text'], '0') === 0
			)
			&&
			(
				strlen(trim($params['message']['text'])) == 1
				|| substr($params['message']['text'], 1, 1) == " "
			)
		)
		{
			$voteSession = true;
		}

		$addMessage = Array(
			"FROM_USER_ID" => $params['message']['user_id'],
			"PARAMS" => isset($params['message']['params'])? $params['message']['params']: Array()
			//"SKIP_COMMAND" => "Y"
		);
		//if (is_object($params['message']['date']))
		//{
		//	$addMessage["MESSAGE_DATE"] = $params['message']['date']->toString();
		//}
		if (!empty($params['message']['text']) || $params['message']['text'] === '0')
		{
			$addMessage["MESSAGE"] = $params['message']['text'];
		}

		if (!empty($params['message']['attach']))
		{
			if ($params['connector']['connector_id'] == self::TYPE_LIVECHAT)
			{
				$addMessage["ATTACH"] = $params['message']['attach'];
			}
			else
			{
				$addMessage["ATTACH"] = \CIMMessageParamAttach::GetAttachByJson($params['message']['attach']);
			}
		}
		if (!empty($params['message']['keyboard']))
		{
			if ($params['connector']['connector_id'] == self::TYPE_LIVECHAT)
			{
				$addMessage["KEYBOARD"] = $params['message']['keyboard'];
			}
			else
			{
				$keyboard = Array();
				if (!isset($params['message']['keyboard']['BUTTONS']))
				{
					$keyboard['BUTTONS'] = $params['message']['keyboard'];
				}
				else
				{
					$keyboard = $params['message']['keyboard'];
				}
				$addMessage["KEYBOARD"] = \Thurly\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
			}
		}
		if (!empty($params['message']['fileLinks']))
		{
			if (!$addMessage["ATTACH"])
			{
				$addMessage["ATTACH"] = new \CIMMessageParamAttach(null, \CIMMessageParamAttach::CHAT);
			}
			foreach ($params['message']['fileLinks'] as $key => $value)
			{
				$addMessage["ATTACH"]->AddFiles(array(
					array(
						"NAME" => $value['name'],
						"LINK" => $value['link'],
						"SIZE" => $value['size'],
					)
				));
			}
		}

		if (strlen($addMessage["MESSAGE"]) <= 0 && empty($params['message']['files']) && empty($addMessage["ATTACH"]))
		{
			return false;
		}

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => self::getUserCode($params['connector']),
			'CONNECTOR' => $params,
			'DEFERRED_JOIN' => 'Y',
			'VOTE_SESSION' => $voteSession? 'Y': 'N'
		));
		if (!$result)
		{
			return false;
		}

		$addMessage["TO_CHAT_ID"] = $session->getData('CHAT_ID');
		if (!empty($params['message']['files']))
		{
			$params['message']['files'] = \CIMDisk::UploadFileFromMain(
				$session->getData('CHAT_ID'),
				$params['message']['files']
			);

			if ($params['message']['files'])
			{
				$addMessage["PARAMS"]['FILE_ID'] = $params['message']['files'];
			}
			else if (strlen($addMessage["MESSAGE"]) <= 0 && empty($addMessage["ATTACH"]))
			{
				$addMessage["MESSAGE"] = '[FILE]';
			}
		}

		if ($session->isNowCreated())
		{
			if ($params['connector']['connector_id'] == self::TYPE_LIVECHAT)
			{
				$orm = \Thurly\Im\Model\ChatTable::getById($params['chat']['id']);
				$guestChatData = $orm->fetch();

				$chat = new \Thurly\ImOpenLines\Chat($params['chat']['id']);
				$chat->updateFieldData(\Thurly\ImOpenLines\Chat::FIELD_LIVECHAT, Array(
					'SESSION_ID' => $session->getData('ID'),
					'SHOW_FORM' => 'Y',
				));
			}
			else
			{
				$guestChatData = $session->getChat()->getData();
			}
			if ($guestChatData && strlen($guestChatData['DESCRIPTION']))
			{
				Im::addMessage(Array(
					"TO_CHAT_ID" => $session->getData('CHAT_ID'),
					'MESSAGE' => '[B]'.Loc::getMessage('IMOL_CONNECTOR_RECEIVED_DATA').'[/B][BR] '.$guestChatData['DESCRIPTION'],
					'SYSTEM' => 'Y',
					'SKIP_COMMAND' => 'Y',
					"PARAMS" => Array(
						"CLASS" => "bx-messenger-content-item-system"
					),
				));
			}
		}

		$session->joinUser();

		$addVoteResult = false;
		$userViewChat = \CIMContactList::InRecent($session->getData('OPERATOR_ID'), IM_MESSAGE_OPEN_LINE, $session->getData('CHAT_ID'));
		if ($voteSession && $session->getData('WAIT_VOTE') == 'Y')
		{
			$voteValue = 0;
			if (strlen($addMessage["MESSAGE"]) > 1)
			{
				$userViewChat = true;
			}
			if (strpos($addMessage["MESSAGE"], '1') === 0)
			{
				$voteValue = 5;
				$addMessage["MESSAGE"] = '[like]'.substr($addMessage["MESSAGE"], 1);
				$addVoteResult = $session->getConfig('VOTE_MESSAGE_2_LIKE');
			}
			else if (strpos($addMessage["MESSAGE"], '0') === 0)
			{
				$voteValue = 1;
				$addMessage["MESSAGE"] = '[dislike]'.substr($addMessage["MESSAGE"], 1);
				$addVoteResult = $session->getConfig('VOTE_MESSAGE_2_DISLIKE');
			}
			if ($addVoteResult)
			{
				$addMessage['RECENT_ADD'] = $userViewChat? 'Y': 'N';
				$session->update(Array('VOTE' => $voteValue, 'WAIT_VOTE' => 'N'));

				\Thurly\ImOpenLines\Chat::sendRatingNotify(\Thurly\ImOpenLines\Chat::RATING_TYPE_CLIENT, $session->getData('ID'), $voteValue, $session->getData('OPERATOR_ID'), $session->getData('USER_ID'));
			}
		}

		$messageId = Im::addMessage($addMessage);
		if (!$messageId)
		{
			return false;
		}

		if ($addMessage["MESSAGE"])
		{
			$tracker = new \Thurly\ImOpenLines\Tracker();
			$tracker->message(Array(
				'SESSION' => $session,
				'MESSAGE' => Array(
					'ID' => $messageId,
					'TEXT' => $addMessage["MESSAGE"]
				)
			));
		}
		if ($params['message']['id'])
		{
			if ($params['connector']['connector_id'] == self::TYPE_LIVECHAT)
			{
				\CIMMessageParam::Set($params['message']['id'], Array(
					'CONNECTOR_MID' => $messageId,
					'IMOL_SID' => $session->isNowCreated()? $session->getData('ID'): 0,
					"IMOL_FORM" => $session->isNowCreated()? "welcome": 0
				));
				\CIMMessageParam::SendPull($params['message']['id'], Array('CONNECTOR_MID', 'IMOL_SID', 'IMOL_FORM'));

				\Thurly\ImOpenLines\Mail::removeSessionFromMailQueue($session->getData('ID'), false);
			}

			\CIMMessageParam::Set($messageId, Array('CONNECTOR_MID' => $params['message']['id']));
			if (!in_array($params['connector']['connector_id'], Array(self::TYPE_LIVECHAT, self::TYPE_NETWORK)))
			{
				\CIMMessageParam::SendPull($messageId, Array('CONNECTOR_MID'));
			}
		}

		if ($addVoteResult)
		{
			Im::addMessage(Array(
				"TO_CHAT_ID" => $session->getData('CHAT_ID'),
				"MESSAGE" => $addVoteResult,
				"SYSTEM" => 'Y',
				"IMPORTANT_CONNECTOR" => 'Y',
				"PARAMS" => Array(
					"CLASS" => "bx-messenger-content-item-ol-output"
				),
				"RECENT_ADD" => $userViewChat? 'Y': 'N'
			));
		}

		$session->execAutoAction(Array(
			'MESSAGE_ID' => $messageId
		));

		$updateSession = array(
			'MESSAGE_COUNT' => true,
			'DATE_LAST_MESSAGE' => new \Thurly\Main\Type\DateTime()
		);

		if (isset($params['extra']))
		{
			foreach	($params['extra'] as $field => $value)
			{
				$oldValue = $session->getData($field);
				if ($oldValue != $value)
				{
					$updateSession[$field] = $value;
				}
			}
		}

		$session->update($updateSession);

		if (
			$session->getConfig('AGREEMENT_MESSAGE') == 'Y' &&
			!$session->chat->isNowCreated() &&
			$session->getUser() && $session->getUser('USER_ID') == $params['message']['user_id'] &&
			$session->getUser('USER_CODE') && $session->getUser('AGREES') == 'N'
		)
		{
			\Thurly\ImOpenLines\Common::setUserAgrees(Array(
				'AGREEMENT_ID' => $session->getConfig('AGREEMENT_ID'),
				'CRM_ACTIVITY_ID' => $session->getData('CRM_ACTIVITY_ID'),
				'SESSION_ID' => $session->getData('SESSION_ID'),
				'CONFIG_ID' => $session->getData('CONFIG_ID'),
				'USER_CODE' => $session->getUser('USER_CODE'),
			));
		}

		return Array(
			'SESSION_ID' => $session->isNowCreated()? $session->getData('ID'): 0,
			'MESSAGE_ID' => $messageId
		);
	}

	public function updateMessage($params)
	{
		if (empty($params))
			return false;

		$chat = new Chat();
		$result = $chat->load(Array(
			'USER_CODE' => self::getUserCode($params['connector']),
			'ONLY_LOAD' => 'Y',
		));
		if (!$result)
			return false;

		$messageIds = \CIMMessageParam::GetMessageIdByParam('CONNECTOR_MID', $params['message']['id'], $chat->getData('ID'));
		if (empty($messageIds))
			return false;

		\CIMMessenger::DisableMessageCheck();
		foreach($messageIds as $messageId)
		{
			\CIMMessenger::Update($messageId, $params['message']['text'], true, true, null, true);
		}
		\CIMMessenger::EnableMessageCheck();

		return true;
	}

	public function deleteMessage($params)
	{
		if (empty($params))
			return false;

		$chat = new Chat();
		$result = $chat->load(Array(
			'USER_CODE' => self::getUserCode($params['connector']),
			'ONLY_LOAD' => 'Y',
		));
		if (!$result)
			return false;

		$messageIds = \CIMMessageParam::GetMessageIdByParam('CONNECTOR_MID', $params['message']['id'], $chat->getData('ID'));
		if (empty($messageIds))
			return false;

		\CIMMessenger::DisableMessageCheck();
		foreach($messageIds as $messageId)
		{
			\CIMMessenger::Delete($messageId, null, false, true);
		}
		\CIMMessenger::EnableMessageCheck();

		return true;
	}

	public function sendMessage($params)
	{
		if (!$this->isModuleLoad())
			return false;

		Log::write($params, 'SEND MESSAGE');

		if($params['no_session'] == 'Y')
		{
			$fields = array(
				'im' => array(
					'chat_id' => $params['message']['chat_id'],
					'message_id' => $params['message']['id']
				),
				'message' => array(
					'user_id' => $params['message']['user_id'],
					'text' => $params['message']['text'],
					'files' => $params['message']['files'],
					'attachments' => $params['message']['attachments'],
					'params' => $params['message']['params'],
				),
				'chat' => array(
					'id' => $params['connector']['chat_id']
				),
			);

			if ($params['connector']['connector_id'] == self::TYPE_NETWORK)
			{
				$network = new Network();
				$network->sendMessage($params['connector']['line_id'], $fields);
			}
			else
			{
				$connector = new Output($params['connector']['connector_id'], $params['connector']['line_id']);
				$result = $connector->sendMessage(Array($fields));
				if (!$result->isSuccess())
				{
					$this->error = new Error(__METHOD__, 'CONNECTOR_SEND_ERROR', $result->getErrorMessages());

					return false;
				}
			}
		}
		else
		{
			$session = new Session();
			$result = $session->load(Array(
				'USER_CODE' => self::getUserCode($params['connector']),
				'MODE' => Session::MODE_OUTPUT,
				'OPERATOR_ID' => $params['message']['user_id']
			));
			if (!$result)
			{
				return false;
			}

			$session->execAutoAction(Array(
				'MESSAGE_ID' => $params['message']['id']
			));

			if ($session->getConfig('ACTIVE') == 'Y')
			{
				$fields = array(
					'im' => array(
						'chat_id' => $params['message']['chat_id'],
						'message_id' => $params['message']['id']
					),
					'message' => array(
						'user_id' => $params['message']['user_id'],
						'text' => $params['message']['text'],
						'files' => $params['message']['files'],
						'attachments' => $params['message']['attachments'],
						'params' => $params['message']['params'],
					),
					'chat' => array(
						'id' => $params['connector']['chat_id']
					),
				);

				if ($params['message']['system'] != 'Y')
				{
					$updateSession = array(
						'MESSAGE_COUNT' => true,
						'DATE_LAST_MESSAGE' => new \Thurly\Main\Type\DateTime()
					);

					if (!$session->getData('DATE_FIRST_ANSWER') && !\Thurly\Im\User::getInstance($session->getData('OPERATOR_ID'))->isBot())
					{
						$currentTime = new \Thurly\Main\Type\DateTime();
						$updateSession['DATE_FIRST_ANSWER'] = $currentTime;
						$updateSession['TIME_FIRST_ANSWER'] = $currentTime->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp();
					}

					$session->update($updateSession);
				}

				if ($params['connector']['connector_id'] == self::TYPE_NETWORK)
				{
					$network = new Network();
					$network->sendMessage($params['connector']['line_id'], $fields);
				}
				else
				{
					$connector = new Output($params['connector']['connector_id'], $params['connector']['line_id']);
					$result = $connector->sendMessage(Array($fields));
					if (!$result->isSuccess())
					{
						$this->error = new Error(__METHOD__, 'CONNECTOR_SEND_ERROR', $result->getErrorMessages());

						return false;
					}
				}
			}
		}

		return true;
	}

	public function sendStatusWriting($fields, $user)
	{
		Log::write(array($fields, $user), 'STATUS WRITING');
		if ($fields['connector']['connector_id'] == 'network')
		{
			$network = new Network();
			$network->sendStatusWriting($fields['connector']['line_id'], $fields);
		}
		else if ($fields['connector']['connector_id'] == 'livechat')
		{
			\CIMMessenger::StartWriting('chat'.$fields['connector']['chat_id'], $user['id'], "", true);
		}

		return false;
	}

	public function sendStatusRead($connector, $messages, $event)
	{
		if (empty($messages))
			return false;

		if ($connector['connector_id'] == 'network')
		{

		}
		else if ($connector['connector_id'] == 'lines')
		{
			Log::write(array($connector, $messages, $event), 'STATUS READ');

			$maxId = 0;
			foreach ($messages as $messageId)
			{
				$maxId = $maxId < $messageId? $messageId: $maxId;
			}

			$chat = new \CIMChat();
			$chat->SetReadMessage($connector['chat_id'], $maxId, true);
		}
		else if ($connector['connector_id'] == 'livechat')
		{
			Log::write(array($connector, $messages, $event), 'STATUS READ');

			$maxId = 0;
			foreach ($messages as $messageId)
			{
				$maxId = $maxId < $messageId? $messageId: $maxId;
			}

			$chat = new \Thurly\ImOpenLines\Chat($connector['chat_id']);
			$chat->updateFieldData(\Thurly\ImOpenLines\Chat::FIELD_LIVECHAT, Array(
				'READED' => 'Y',
				'READED_ID' => $maxId,
				'READED_TIME' => new \Thurly\Main\Type\DateTime()
			));
		}
		else
		{
			$sendMessages = Array();
			foreach ($messages as $messageId)
			{
				$sendMessages[] = Array(
					'chat' => Array(
						'id' => $connector['chat_id']
					),
					'message' => Array(
						'id' => $messageId
					)
				);
			}

			$connector = new Output($connector['connector_id'], $connector['line_id']);
			$connector->setStatusReading($sendMessages);
		}

		return false;
	}

	public static function getUserCode($params)
	{
		return $params['connector_id'].'|'.$params['line_id'].'|'.$params['chat_id'].'|'.$params['user_id'];
	}

	public static function onBeforeMessageSend($fields, $chat)
	{
		if ($chat['CHAT_ENTITY_TYPE'] != 'LINES')
			return true;

		if ($fields['FROM_USER_ID'] <= 0)
			return true;

		if (\Thurly\Im\User::getInstance($fields['FROM_USER_ID'])->isConnector())
			return true;

		if (!\Thurly\Main\Loader::includeModule('imconnector'))
			return false;

		$result = true;
		list($connectorId, $lineId) = explode('|', $chat['CHAT_ENTITY_ID']);

		if ($connectorId == self::TYPE_NETWORK)
		{}
		else if ($connectorId == self::TYPE_NETWORK)
		{}
		else if (\Thurly\Main\Loader::includeModule('imconnector'))
		{
			$status = \Thurly\ImConnector\Status::getInstance($connectorId, $lineId);
			if (!$status->isStatus())
			{
				$result = Array(
					'result' => false,
					'reason' => Loc::getMessage('IMOL_CONNECTOR_STATUS_ERROR')
				);
			}
		}

		return $result;
	}

	public static function onMessageUpdate($messageId, $messageFields, $flags)
	{
		if ($flags['BY_EVENT'] || !isset($messageFields['PARAMS']['CONNECTOR_MID']))
		{
			return false;
		}

		list($connectorId, $lineId, $connectorChatId) = explode('|', $messageFields['CHAT_ENTITY_ID']);

		if ($messageFields['CHAT_ENTITY_TYPE'] == 'LINES')
		{
		}
		else if ($messageFields['CHAT_ENTITY_TYPE'] == 'LIVECHAT')
		{
			$connectorId = self::TYPE_LIVECHAT;
		}
		else
		{
			return false;
		}

		if ($connectorId == self::TYPE_LIVECHAT)
		{
			\CIMMessenger::DisableMessageCheck();
			foreach($messageFields['PARAMS']['CONNECTOR_MID'] as $mid)
			{
				\CIMMessenger::Update($mid, $flags['TEXT'], $flags['URL_PREVIEW'], $flags['EDIT_FLAG'], $flags['USER_ID'], true);
			}
			\CIMMessenger::EnableMessageCheck();
		}
		else if (
			isset($lineId) && isset($connectorChatId)
			&& !empty($messageFields['PARAMS']['CONNECTOR_MID']) && is_array($messageFields['PARAMS']['CONNECTOR_MID'])
		)
		{
			if ($messageFields['SYSTEM'] != 'Y' && self::isEnableSendMessageWithSignature($connectorId) && $messageFields['AUTHOR_ID'] > 0)
			{
				$flags['TEXT'] = '[b]' . htmlspecialchars_decode(self::getOperatorName($lineId, $messageFields['AUTHOR_ID'])) . ':[/b]'.(strlen($flags['TEXT'])>0? '[br] '.$flags['TEXT']: '');
			}

			$fields = array(
				'im' => array(
					'chat_id' => $messageFields['CHAT_ID'],
					'message_id' => $messageFields['ID']
				),
				'message' => array(
					'id' => $messageFields['PARAMS']['CONNECTOR_MID'],
					'text' => $flags['TEXT'],
				),
				'chat' => array(
					'id' => $connectorChatId
				),
			);

			if ($connectorId == self::TYPE_NETWORK)
			{
				$network = new Network();
				$network->updateMessage($lineId, $fields);
			}
			else if (\Thurly\Main\Loader::includeModule('imconnector'))
			{
				$connector = new Output($connectorId, $lineId);
				$connector->updateMessage(Array($fields));
			}
		}

		return true;
	}

	public static function onMessageDelete($messageId, $messageFields, $flags)
	{
		if ($flags['BY_EVENT'] || !isset($messageFields['PARAMS']['CONNECTOR_MID']))
		{
			return false;
		}

		if ($messageFields['CHAT_ENTITY_TYPE'] == 'LINES')
		{
			list($connectorType, $lineId, $connectorChatId) = explode("|", $messageFields['CHAT_ENTITY_ID']);
		}
		else if ($messageFields['CHAT_ENTITY_TYPE'] == 'LIVECHAT')
		{
			$connectorType = self::TYPE_LIVECHAT;
		}
		else
		{
			return false;
		}

		if ($connectorType == self::TYPE_LIVECHAT)
		{
			\CIMMessenger::DisableMessageCheck();
			foreach($messageFields['PARAMS']['CONNECTOR_MID'] as $mid)
			{
				\CIMMessenger::Delete($mid, $flags['USER_ID'], $flags['COMPLETE_DELETE'], true);
			}
			\CIMMessenger::EnableMessageCheck();
		}
		else if(isset($lineId) && isset($connectorChatId))
		{
			$fields = array();
			foreach($messageFields['PARAMS']['CONNECTOR_MID'] as $mid)
			{
				$fields[] = array(
					'im' => array(
						'chat_id' => $messageFields['CHAT_ID'],
						'message_id' => $messageFields['ID']
					),
					'message' => array(
						'id' => $mid
					),
					'chat' => array(
						'id' => $connectorChatId
					),
				);
			}
			if (!empty($fields))
			{
				if ($connectorType == self::TYPE_NETWORK)
				{
					$network = new Network();
					$network->deleteMessage($lineId, $fields[0]);
				}
				else if(\Thurly\Main\Loader::includeModule('imconnector'))
				{
					$connector = new Output($connectorType, $lineId);
					$connector->deleteMessage($fields);
				}
			}
		}

		return true;
	}

	public static function onMessageSend($messageId, $messageFields)
	{
		if ($messageFields['CHAT_ENTITY_TYPE'] != 'LINES')
			return false;

		$messageFields['MESSAGE_ID'] = $messageId;
		Log::write($messageFields, 'CONNECTOR MESSAGE SEND');

		if ($messageFields['AUTHOR_ID'] > 0)
		{
			$user = \Thurly\Im\User::getInstance($messageFields['AUTHOR_ID']);
			if ($user->isConnector())
				return false;
		}

		if (
			($messageFields['SILENT_CONNECTOR'] == 'Y' || $messageFields['CHAT_'.Chat::getFieldName(Chat::FIELD_SILENT_MODE)] == 'Y')
			&& $messageFields['IMPORTANT_CONNECTOR'] != 'Y'
		)
		{
			\CIMMessageParam::Set($messageId, Array('CLASS' => "bx-messenger-content-item-system"));
			\CIMMessageParam::SendPull($messageId, Array('CLASS'));
			return false;
		}

		if ($messageFields['SKIP_CONNECTOR'] == 'Y')
			return false;

		if ($messageFields['IMPORTANT_CONNECTOR'] != 'Y' && $messageFields['SYSTEM'] == 'Y')
			return false;

		list($connectorId, $lineId, $connectorChatId, $connectorUserId) = explode('|', $messageFields['CHAT_ENTITY_ID']);

		if ($connectorId == self::TYPE_LIVECHAT)
		{
			if (isset($messageFields['PARAMS']['CLASS']))
			{
				$messageFields['PARAMS']['CLASS'] = str_replace("bx-messenger-content-item-ol-output", "", $messageFields['PARAMS']['CLASS']);
			}

			$params = Array();
			foreach ($messageFields['PARAMS'] as $key => $value)
			{
				if (in_array($key, Array('CLASS')))
				{
					$params[$key] = $value;
				}
				else if (strpos($key, 'IMOL_') === 0)
				{
					$params[$key] = $value;
				}
				else if (strpos($key, 'IS_') === 0)
				{
					$params[$key] = $value;
				}
			}

			$message = Array(
				"TO_CHAT_ID" => $connectorChatId,
				"FROM_USER_ID" => $messageFields['AUTHOR_ID'],
				"SYSTEM" => $messageFields['SYSTEM'],
				"URL_PREVIEW" => $messageFields['URL_PREVIEW'],
				"ATTACH" => $messageFields['ATTACH'],
				"PARAMS" => $params,
				"SKIP_USER_CHECK" => "Y",
				"SKIP_COMMAND" => "Y",
				"SKIP_CONNECTOR" => "Y",
				"EXTRA_PARAMS" => Array(
					"CONTEXT" => "LIVECHAT",
					"LINE_ID" => $lineId
				),
			);
			if (array_key_exists('MESSAGE', $messageFields))
			{
				$message["MESSAGE"] = $messageFields['MESSAGE'];
			}

			if($messageFields['NO_SESSION_OL'] !== 'Y')
			{
				$session = new Session();
				$session->load(Array(
					'MODE' => Session::MODE_OUTPUT,
					'USER_CODE' => $messageFields['CHAT_ENTITY_ID'],
					'OPERATOR_ID' => $messageFields['AUTHOR_ID']
				));
				$updateSession = array(
					'MESSAGE_COUNT' => true,
					'DATE_LAST_MESSAGE' => new \Thurly\Main\Type\DateTime(),
					'DATE_MODIFY' => new \Thurly\Main\Type\DateTime(),
					'USER_ID' => $messageFields['AUTHOR_ID'],
				);
				if (!$session->getData('DATE_FIRST_ANSWER') && !\Thurly\Im\User::getInstance($session->getData('OPERATOR_ID'))->isBot())
				{
					$currentTime = new \Thurly\Main\Type\DateTime();
					$updateSession['DATE_FIRST_ANSWER'] = $currentTime;
					$updateSession['TIME_FIRST_ANSWER'] = $currentTime->getTimestamp()-$session->getData('DATE_CREATE')->getTimestamp();
				}

				$session->update($updateSession);
			}

			$mid = Im::addMessage($message);
			if ($messageId && $mid)
			{
				\CIMMessageParam::Set($messageId, Array('CONNECTOR_MID' => $mid));
				\CIMMessageParam::Set($mid, Array('CONNECTOR_MID' => $messageId));
			}
			if($messageFields['NO_SESSION_OL'] !== 'Y')
				\Thurly\ImOpenLines\Mail::addSessionToMailQueue($session->getData('ID'), false);
		}
		else
		{
			if (!self::isEnableSendSystemMessage($connectorId) && $messageFields['SYSTEM'] == 'Y')
			{
				return false;
			}

			$params = Array();
			foreach ($messageFields['PARAMS'] as $key => $value)
			{
				if (in_array($key, Array('CLASS')))
				{
					$params[$key] = $value;
				}
				else if (strpos($key, 'IMOL_') === 0)
				{
					$params[$key] = $value;
				}
				else if (strpos($key, 'IS_') === 0)
				{
					$params[$key] = $value;
				}
			}

			$attaches = Array();
			if (isset($messageFields['PARAMS']['ATTACH']))
			{
				foreach ($messageFields['PARAMS']['ATTACH'] as $attach)
				{
					if ($attach instanceof \CIMMessageParamAttach)
					{
						$attaches[] = $attach->GetJSON();
					}
				}
			}

			$files = Array();
			if (isset($messageFields['FILES']) && \Thurly\Main\Loader::includeModule('disk'))
			{
				foreach ($messageFields['FILES'] as $file)
				{
					$fileModel = \Thurly\Disk\File::loadById($file['id']);
					if (!$fileModel)
						continue;

					$extModel = $fileModel->addExternalLink(array(
						'CREATED_BY' => $messageFields['FROM_USER_ID'],
						'TYPE' => \Thurly\Disk\Internals\ExternalLinkTable::TYPE_MANUAL,
					));
					if (!$extModel)
						continue;

					$file['link'] = \Thurly\Disk\Driver::getInstance()->getUrlManager()->getShortUrlExternalLink(array(
						'hash' => $extModel->getHash(),
						'action' => 'default',
					), true);

					if (!$file['link'])
						continue;

					$files[] = array(
						'name' => $file['name'],
						'type' => $file['type'],
						'link' => $file['link'],
						'size' => $file['size']
					);
				}
			}
			if (empty($attaches) && empty($files) && empty($messageFields['MESSAGE']) && $messageFields['MESSAGE'] !== "0")
				return false;

			if ($messageFields['SYSTEM'] != 'Y' && self::isEnableSendMessageWithSignature($connectorId) && $messageFields['AUTHOR_ID'] > 0)
			{
				$messageFields['MESSAGE'] = '[b]' . htmlspecialchars_decode(self::getOperatorName($lineId, $messageFields['AUTHOR_ID'])) . ':[/b]'.(strlen($messageFields['MESSAGE'])>0? '[br] '.$messageFields['MESSAGE']: '');
			}

			$fields = array(
				'connector' => Array(
					'connector_id' => $connectorId,
					'line_id' => $lineId,
					'user_id' => $connectorUserId,
					'chat_id' => $connectorChatId,
				),
				'message' => Array(
					'id' => $messageId,
					'chat_id' => $messageFields['TO_CHAT_ID'],
					'user_id' => $messageFields['FROM_USER_ID'],
					'text' => $messageFields['MESSAGE'],
					'files' => $files,
					'attachments' => $attaches,
					'params' => $params,
					'system' => $messageFields['SYSTEM']
				),
				'no_session' => $messageFields['NO_SESSION_OL']
			);

			if (in_array($connectorId, self::getListShowDeliveryStatus()))
			{
				\CIMMessageParam::Set($messageId, Array('SENDING' => 'Y', 'SENDING_TS' => time()));
				\CIMMessageParam::SendPull($messageId, Array('SENDING', 'SENDING_TS'));
			}

			$manager = new self();
			if (!$manager->sendMessage($fields))
			{
				Im::addMessage(Array(
					"TO_CHAT_ID" => $messageFields['TO_CHAT_ID'],
					"MESSAGE" => Loc::getMessage('IMOL_CHAT_ERROR_CONNECTOR_SEND'),
					"SYSTEM" => 'Y',
				));
				return false;
			}
		}

		return true;
	}

	public static function onStartWriting($params)
	{
		if (empty($params['CHAT']) || !in_array($params['CHAT']['ENTITY_TYPE'], Array('LINES', 'LIVECHAT')) || $params['BY_EVENT'])
			return true;

		if ($params['CHAT']['ENTITY_TYPE'] == 'LINES')
		{
			list($connectorId, $lineId, $connectorChatId, $connectorUserId) = explode('|', $params['CHAT']['ENTITY_ID']);
		}
		else // LIVECHAT
		{
			$connectorChatId = 0;
			$connectorId = self::TYPE_LIVECHAT;
			list($lineId, $connectorUserId) = explode('|', $params['CHAT']['ENTITY_ID']);

			$orm = Model\SessionTable::getList(array(
				'select' => Array('CHAT_ID'),
				'filter' => array(
					'=USER_CODE' => $connectorId.'|'.$lineId.'|'.$params['CHAT']['ID'].'|'.$params['USER_ID'],
					'=CLOSED' => 'N'
				)
			));
			if ($session = $orm->fetch())
			{
				$connectorChatId = $session['CHAT_ID'];
			}
		}

		if ($connectorChatId <= 0)
			return true;

		$chat = new Chat($params['CHAT']['ID']);
		if ($chat->isSilentModeEnabled() || $params['LINES_SILENT_MODE'])
			return true;

		$userData = \Thurly\Im\User::getInstance($params['USER_ID']);
		if ($userData->isBot())
			return false;

		$fields = array(
			'connector' => Array(
				'connector_id' => $connectorId,
				'line_id' => $lineId,
				'user_id' => $connectorUserId,
				'chat_id' => $connectorChatId,
			),
			'chat' => Array('id' => $connectorChatId),
			'user' => $params['USER_ID']
		);

		$manager = new self();
		return $manager->sendStatusWriting($fields, $userData->getFields());
	}

	public static function onChatRead($params)
	{
		if (!in_array($params['CHAT_ENTITY_TYPE'], Array('LINES', 'LIVECHAT')) || $params['BY_EVENT'])
			return true;

		if ($params['CHAT_ENTITY_TYPE'] == 'LINES')
		{
			list($connectorId, $lineId, $connectorChatId, $connectorUserId) = explode('|', $params['CHAT_ENTITY_ID']);
		}
		else // LIVECHAT
		{
			$chatId = $params['CHAT_ID'];
			$connectorChatId = 0;
			$connectorId = self::TYPE_LIVECHAT;
			list($lineId, $connectorUserId) = explode('|', $params['CHAT_ENTITY_ID']);

			$orm = Model\SessionTable::getList(array(
				'select' => Array('ID', 'CHAT_ID'),
				'filter' => array(
					'=USER_CODE' => $connectorId.'|'.$lineId.'|'.$chatId.'|'.$connectorUserId,
					'=CLOSED' => 'N'
				)
			));
			if ($session = $orm->fetch())
			{
				$connectorChatId = $session['CHAT_ID'];
				\Thurly\ImOpenLines\Mail::removeSessionFromMailQueue($session['ID'], false);
			}
			$connectorId = 'lines';
		}

		$event = $params;

		$connector = Array(
			'connector_id' => $connectorId,
			'line_id' => $lineId,
			'chat_id' => $connectorChatId,
		);

		$application = \Thurly\Main\Application::getInstance();
		$connection = $application->getConnection();

		$params['END_ID'] = intval($params['END_ID']);

		$messages = Array();
		$query = $connection->query("
			SELECT M.ID, MP.PARAM_VALUE
			FROM b_im_message M
			LEFT JOIN b_im_message_param MP ON MP.MESSAGE_ID = M.ID AND MP.PARAM_NAME = 'CONNECTOR_MID'
			WHERE
			M.CHAT_ID = ".intval($params['CHAT_ID'])." AND
			M.ID > ".intval($params['START_ID']).($params['END_ID']? " AND M.ID < ".(intval($params['END_ID'])+1): "")."
		");
		while($row = $query->fetch())
		{
			$messages[] = $row['PARAM_VALUE'];
		}

		$manager = new self();
		return $manager->sendStatusRead($connector, $messages, $event);
	}

	public static function onReceivedEntity($params)
	{
		$userId = intval($params['user']);

		global $USER;
		if($userId > 0 && !$USER->IsAuthorized() && $USER->Authorize($userId, false, false))
		{
			setSessionExpired(true);
		}

		if (!isset($params['message']['user_id']))
		{
			$params['message']['user_id'] = $params['user'];
		}

		$fields = array(
			'connector' => Array(
				'connector_id' => $params['connector'],
				'line_id' => $params['line'],
				'chat_id' => $params['chat']['id'],
				'user_id' => $params['user'],
			),
			'chat' => $params['chat'],
			'message' => $params['message'],
			'extra' => isset($params['extra'])? $params['extra']: Array()
		);

		Log::write($fields, 'CONNECTOR - ENTITY ADD');

		$manager = new self();
		return $manager->addMessage($fields);
	}

	public static function onReceivedMessage(\Thurly\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		return static::onReceivedEntity($params);
	}

	public static function onReceivedPost(\Thurly\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		$params['message']['id'] = '';

		return static::onReceivedEntity($params);
	}

	public static function onReceivedMessageUpdate(\Thurly\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		$userId = intval($params['user']);

		global $USER;
		if($userId > 0 && !$USER->IsAuthorized() && $USER->Authorize($userId, false, false))
		{
			setSessionExpired(true);
		}

		if (!isset($params['message']['user_id']))
		{
			$params['message']['user_id'] = $params['user'];
		}

		$fields = array(
			'connector' => Array(
				'connector_id' => $params['connector'],
				'line_id' => $params['line'],
				'chat_id' => $params['chat']['id'],
				'user_id' => $params['user'],
			),
			'chat' => $params['chat'],
			'message' => $params['message']
		);

		Log::write($fields, 'CONNECTOR - ENTITY UPDATE');
		$manager = new self();
		return $manager->updateMessage($fields);
	}

	public static function onReceivedPostUpdate(\Thurly\Main\Event $event)
	{
		return self::onReceivedMessageUpdate($event);
	}

	public static function onReceivedMessageDelete(\Thurly\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		$userId = intval($params['user']);

		global $USER;
		if($userId > 0 && !$USER->IsAuthorized() && $USER->Authorize($userId, false, false))
		{
			setSessionExpired(true);
		}

		if (!isset($params['message']['user_id']))
		{
			$params['message']['user_id'] = $params['user'];
		}

		$fields = array(
			'connector' => Array(
				'connector_id' => $params['connector'],
				'line_id' => $params['line'],
				'chat_id' => $params['chat']['id'],
				'user_id' => $params['user'],
			),
			'chat' => $params['chat'],
			'message' => $params['message']
		);

		Log::write($fields, 'CONNECTOR - ENTITY DELETE');
		$manager = new self();
		return $manager->deleteMessage($fields);
	}

	public static function onReceivedStatusError(\Thurly\Main\Event $event)
	{
		return true;
	}

	public static function onReceivedStatusDelivery(\Thurly\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		Log::write($params, 'CONNECTOR - STATUS DELIVERY');

		if (!\Thurly\Main\Loader::includeModule('im'))
			return false;

		\CIMMessageParam::Set($params['im']['message_id'], Array('CONNECTOR_MID' => $params['message']['id'], 'SENDING' => 'N', 'SENDING_TS' => 0));
		\CIMMessageParam::SendPull($params['im']['message_id'], Array('CONNECTOR_MID', 'SENDING', 'SENDING_TS'));

		return true;
	}

	public static function onReceivedStatusReading(\Thurly\Main\Event $event)
	{
		return true;
	}

	public static function onReceivedStatusWrites(\Thurly\Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return false;

		if (!isset($params['message']['user_id']))
		{
			$params['message']['user_id'] = $params['user'];
		}

		$fields = array(
			'connector' => Array(
				'connector_id' => $params['connector'],
				'line_id' => $params['line'],
				'chat_id' => $params['chat']['id'],
				'user_id' => $params['user'],
			),
			'chat' => $params['chat'],
			'message' => $params['message']
		);

		$skipCreate = true;
		/* TODO temporaty comment - becouse not support listener bot
		$configManager = new Config();
		$config = $configManager->get($params['line']);
		if (
			$config &&
			$config['WELCOME_BOT_ENABLE'] == 'Y' &&
			$config['WELCOME_BOT_ID'] > 0 &&
			$config['WELCOME_BOT_JOIN'] == \Thurly\ImOpenLines\Config::BOT_JOIN_ALWAYS
		)
		{
			$skipCreate = false;
		}*/

		$session = new Session();
		$result = $session->load(Array(
			'USER_CODE' => self::getUserCode($fields['connector']),
			'CONNECTOR' => $fields,
			'SKIP_CREATE' => $skipCreate? 'Y': 'N'
		));
		if (!$result)
		{
			return false;
		}

		$chat = $session->getChat();
		$chatId = $chat->getData('ID');

		if (\CModule::IncludeModule('im'))
		{
			\CIMMessenger::StartWriting('chat'.$chatId, $params['user'], "", true);
		}

		return true;
	}

	public static function getListCanDeleteMessage()
	{
		$connectorList = array();
		if (\Thurly\Main\Loader::includeModule('imconnector'))
		{
			$connectorList = \Thurly\ImConnector\Connector::getListConnectorDelExternalMessages();
		}

		return $connectorList;
	}

	public static function getListShowDeliveryStatus()
	{
		$connectorList = array();
		if (\Thurly\Main\Loader::includeModule('imconnector'))
		{
			//$connectorList = \Thurly\ImConnector\Connector::getListConnectorShowDeliveryStatus();
			$connectorList = array_keys(\Thurly\ImConnector\Connector::getListConnector()); // TODO change method to getListConnectorShowDeliveryStatus()
			foreach ($connectorList as $key => $connectorId)
			{
				if ($connectorId == 'network' || $connectorId == 'livechat')
				{
					unset($connectorList[$key]);
				}
			}
		}

		if (!defined('IMOL_NETWORK_UPDATE'))
		{
			$connectorList[] = self::TYPE_NETWORK;
		}

		return $connectorList;
	}

	public static function getListCanUpdateOwnMessage()
	{
		$connectorList = array();
		if (\Thurly\Main\Loader::includeModule('imconnector'))
		{
			$connectorList = \Thurly\ImConnector\Connector::getListConnectorEditInternalMessages();
		}

		$connectorList[] = self::TYPE_LIVECHAT;

		if (!defined('IMOL_NETWORK_UPDATE'))
		{
			$connectorList[] = self::TYPE_NETWORK;
		}

		return $connectorList;
	}

	public static function getListCanDeleteOwnMessage()
	{
		$connectorList = array();
		if (\Thurly\Main\Loader::includeModule('imconnector'))
		{
			$connectorList = \Thurly\ImConnector\Connector::getListConnectorDelInternalMessages();
		}
		$connectorList[] = self::TYPE_LIVECHAT;

		if (!defined('IMOL_NETWORK_UPDATE'))
		{
			$connectorList[] = self::TYPE_NETWORK;
		}

		return $connectorList;
	}

	public static function getOperatorName($lineId, $userId, $array = false)
	{
		$user = \Thurly\Im\User::getInstance($userId);

		$userArray = Array(
			'ID' => $user->getId(),
			'NAME' => $user->getName(),
			'LAST_NAME' => $user->getLastName(),
			'PERSONAL_GENDER' => $user->getGender(),
			'PERSONAL_PHOTO' => ''
		);

		if (function_exists('customImopenlinesOperatorNames') && !$user->isExtranet()) // Temporary hack :(
		{
			$userArray = customImopenlinesOperatorNames($lineId, $userArray);
		}

		$nameTemplateSite = \CSite::GetNameFormat(false);
		$userName = \CUser::FormatName($nameTemplateSite, $userArray, true, false);

		if ($array)
		{
			$userFields = $user->getFields();
			$userFields['first_name'] = $userArray['NAME'];
			$userFields['last_name'] = $userArray['LAST_NAME'];
			$userFields['name'] = $userName;

			return $userFields;
		}
		else
		{
			return $userName;
		}
	}

	public static function isEnableSendSystemMessage($connectorId)
	{
		if (in_array($connectorId, array(self::TYPE_LIVECHAT, self::TYPE_NETWORK)))
		{
			$result = true;
		}
		else if (\Thurly\Main\Loader::includeModule('imconnector'))
		{
			$result = \Thurly\ImConnector\Connector::isNeedSystemMessages($connectorId);
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	public static function isEnableSendMessageWithSignature($connectorId)
	{
		if (in_array($connectorId, array(self::TYPE_LIVECHAT, self::TYPE_NETWORK)))
		{
			$result = false;
		}
		else if (\Thurly\Main\Loader::includeModule('imconnector'))
		{
			$result = \Thurly\ImConnector\Connector::isNeedSignature($connectorId);
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	public static function isEnableGroupByChat($connectorId)
	{
		if (\Thurly\Main\Loader::includeModule('imconnector'))
		{
			$result = \Thurly\ImConnector\Connector::isChatGroup($connectorId);
		}
		else
		{
			$result = false;
		}
		return $result;
	}

	public function getError()
	{
		return $this->error;
	}
}