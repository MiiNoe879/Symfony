<?php
namespace Thurly\Im\Replica;

class StartWritingHandler extends \Thurly\Replica\Client\BaseHandler
{
	protected $moduleId = "im";

	public function initDataManagerEvents()
	{
		\Thurly\Main\EventManager::getInstance()->addEventHandler(
			"im",
			"OnStartWriting",
			array($this, "OnStartWriting")
		);
		\Thurly\Main\EventManager::getInstance()->addEventHandler(
			"replica",
			"OnExecuteStartWriting",
			array($this, "OnExecuteStartWriting")
		);
	}

	function onStartWriting($userId, $dialogId)
	{
		if (\Thurly\Im\User::getInstance($userId)->isBot())
		{
			return true;
		}

		$operation = new \Thurly\Replica\Db\Execute();
		if (substr($dialogId, 0, 4) === "chat")
		{
			$chatId = substr($dialogId, 4);
			$operation->writeToLog(
				"StartWriting",
				array(
					array(
						"relation" => "b_user.ID",
						"value" => $userId,
					),
					array(
						"value" => "chat",
					),
					array(
						"relation" => "b_im_chat.ID",
						"value" => $chatId,
					),
				)
			);
		}
		else
		{
			$operation->writeToLog(
				"StartWriting",
				array(
					array(
						"relation" => "b_user.ID",
						"value" => $userId,
					),
					array(
						"value" => "",
					),
					array(
						"relation" => "b_user.ID",
						"value" => $dialogId,
					),
				)
			);
		}

		return true;
	}

	function onExecuteStartWriting(\Thurly\Main\Event $event)
	{
		$parameters = $event->getParameters();
		$userId = intval($parameters[0]);
		$dialogId = $parameters[1].$parameters[2];

		if ($userId > 0)
		{
			if (!\Thurly\Main\Loader::includeModule('pull'))
				return;

			\CPushManager::DeleteFromQueueBySubTag($userId, 'IM_MESS');

			$userName = \Thurly\Im\User::getInstance($userId)->getFullName();


			if (substr($dialogId, 0, 4) == 'chat')
			{
				$chatId = substr($dialogId, 4);
				$arRelation = \CIMChat::GetRelationById($chatId);
				unset($arRelation[$userId]);

				$chat = \Thurly\Im\Model\ChatTable::getById($chatId);
				$chatData = $chat->fetch();

				$pullMessage = Array(
					'module_id' => 'im',
					'command' => 'startWriting',
					'expiry' => 60,
					'params' => Array(
						'dialogId' => $dialogId,
						'userId' => $userId,
						'userName' => $userName
					),
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
				);
				if ($chatData['ENTITY_TYPE'] == 'LINES')
				{
					foreach ($arRelation as $rel)
					{
						if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
						{
							unset($arRelation[$rel["USER_ID"]]);
						}
					}
				}
				\Thurly\Pull\Event::add(array_keys($arRelation), $pullMessage);

				$orm = \Thurly\Im\Model\ChatTable::getById($chatId);
				$chat = $orm->fetch();
				if ($chat['TYPE'] == IM_MESSAGE_OPEN || $chat['TYPE'] == IM_MESSAGE_OPEN_LINE)
				{
					\CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pullMessage);
				}
			}
			else if (intval($dialogId) > 0)
			{
				\Thurly\Pull\Event::add($dialogId, Array(
					'module_id' => 'im',
					'command' => 'startWriting',
					'expiry' => 60,
					'params' => Array(
						'dialogId' => $userId,
						'userId' => $userId,
						'userName' => $userName
					),
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
				));
			}
		}
	}
}
