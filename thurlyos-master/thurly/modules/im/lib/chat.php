<?php
namespace Thurly\Im;

class Chat
{
	const LIMIT_SEND_EVENT = 30;

	public static function getRelation($chatId, $params = Array())
	{
		$chatId = intval($chatId);
		if ($chatId <= 0)
		{
			return false;
		}

		$connection = \Thurly\Main\Application::getInstance()->getConnection();

		$selectFields = '';
		if (isset($params['SELECT']))
		{
			$params['SELECT'][] = 'ID';
			$params['SELECT'][] = 'USER_ID';
			$map = \Thurly\Im\Model\RelationTable::getMap();
			foreach ($params['SELECT'] as $key => $value)
			{
				if (is_int($key) && isset($map[$value]))
				{
					$selectFields .= "R.{$value}, ";
					unset($map[$value]);
				}
				else if (!is_int($key) && isset($map[$key]))
				{
					$selectFields .= "R.{$key} {$connection->getSqlHelper()->forSql($value)}, ";
					unset($map[$value]);
				}
			}
		}
		if (!$selectFields)
		{
			$selectFields = 'R.*, ';
		}

		$withUserFields = false;
		if (isset($params['USER_DATA']) && $params['USER_DATA'] == 'Y')
		{
			$withUserFields = true;
			$list = Array('ACTIVE', 'EXTERNAL_AUTH_ID');
			foreach ($list as $key)
			{
				$selectFields .= "U.{$key} USER_DATA_{$key}, ";
			}
		}

		$whereFields = '';
		if (isset($params['FILTER']))
		{
			$map = \Thurly\Im\Model\RelationTable::getMap();
			foreach ($params['FILTER'] as $key => $value)
			{
				if (!isset($map[$key]))
				{
					continue;
				}

				$whereFields .= " AND R.{$key} = {$connection->getSqlHelper()->forSql($value)}";
			}
		}

		$skipUnmodifiedRecords = false;
		if (isset($params['SKIP_RELATION_WITH_UNMODIFIED_COUNTERS']) && $params['SKIP_RELATION_WITH_UNMODIFIED_COUNTERS'] == 'Y')
		{
			$skipUnmodifiedRecords = true;
		}

		if (isset($params['REAL_COUNTERS']) && $params['REAL_COUNTERS'] != 'N' || $skipUnmodifiedRecords)
		{
			$lastId = "R.LAST_ID";
			if (is_array($params['REAL_COUNTERS']))
			{
				if (isset($params['REAL_COUNTERS']['LAST_ID']) && intval($params['REAL_COUNTERS']['LAST_ID']) > 0)
				{
					$lastId = intval($params['REAL_COUNTERS']['LAST_ID']);
				}
			}

			$sqlSelectCounter = "R.COUNTER PREVIOUS_COUNTER, (
				SELECT COUNT(1) FROM b_im_message M WHERE M.CHAT_ID = R.CHAT_ID AND M.ID > $lastId
			) COUNTER";
		}
		else
		{
			$sqlSelectCounter = 'R.COUNTER, R.COUNTER PREVIOUS_COUNTER';
		}

		$sql = "
			SELECT {$selectFields} {$sqlSelectCounter}
			FROM b_im_relation R
			".($withUserFields? "LEFT JOIN b_user U ON R.USER_ID = U.ID": "")."
			WHERE R.CHAT_ID = {$chatId} {$whereFields}
			".($skipUnmodifiedRecords? ' HAVING COUNTER <> PREVIOUS_COUNTER': '')."
		";
		$relations = array();
		$query = \Thurly\Main\Application::getInstance()->getConnection()->query($sql);
		while ($row = $query->fetch())
		{
			$row['COUNTER'] = $row['COUNTER']>99? 100: intval($row['COUNTER']);
			$row['PREVIOUS_COUNTER'] = intval($row['PREVIOUS_COUNTER']);

			if ($skipUnmodifiedRecords && $row['COUNTER'] == $row['PREVIOUS_COUNTER'])
			{
				continue;
			}

			foreach ($row as $key => $value)
			{
				if (strpos($key, 'USER_DATA_') === 0)
				{
					$row['USER_DATA'][substr($key, 10)] = $value;
					unset($row[$key]);
				}
			}

			$relations[$row['USER_ID']] = $row;
		}

		return $relations;
	}

	public static function mute($chatId, $action, $userId = null)
	{
		$userId = \Thurly\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$chatId = intval($chatId);
		if (!$chatId)
		{
			return false;
		}

		$action = $action === true? 'Y': 'N';

		$relation = self::getRelation($chatId, Array(
			'SELECT' => Array('ID', 'NOTIFY_BLOCK'),
			'FILTER' => Array(
				'USER_ID' => $userId
			),
		));
		if (!$relation)
		{
			return false;
		}

		if ($relation[$userId]['NOTIFY_BLOCK'] == $action)
		{
			return true;
		}

		\Thurly\Im\Model\RelationTable::update($relation[$userId]['ID'], array('NOTIFY_BLOCK' => $action));

		Recent::clearCache($userId);

		if (\Thurly\Main\Loader::includeModule('pull'))
		{
			\Thurly\Pull\Event::add($userId, Array(
				'module_id' => 'im',
				'command' => 'chatMuteNotify',
				'params' => Array(
					'dialogId' => 'chat'.$chatId,
					'mute' => $action == 'Y'
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
		}

		return true;
	}
}