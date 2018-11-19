<?php
namespace Thurly\Im;

class Dialog
{
	public static function getRelation($userId1, $userId2, $params = array())
	{
		$userId1 = intval($userId1);
		$userId2 = intval($userId2);

		if ($userId1 <= 0 || $userId2 <= 0)
		{
			return false;
		}

		if ($userId1 == $userId2)
		{
			$chat = new \CIMChat();
			$chatId = $chat->GetPersonalChat();
		}
		else
		{
			$query = "
				SELECT R1.CHAT_ID ID
				FROM b_im_relation R1
				INNER JOIN b_im_relation R2 ON R2.CHAT_ID = R1.CHAT_ID AND R2.USER_ID = ".$userId2."
				WHERE R1.USER_ID = ".$userId1." and R1.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
			";
			$chat = \Thurly\Main\Application::getInstance()->getConnection()->query($query)->fetch();
			if ($chat)
			{
				$chatId = $chat['ID'];
			}
			else
			{
				$chatId = \CIMMessage::GetChatId($userId1, $userId2);
			}
		}

		return Chat::getRelation($chatId, $params);
	}
}