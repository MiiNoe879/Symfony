<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 * @access private
 */

namespace Thurly\Tasks\Util\Notification;

use Thurly\Main\Localization\Loc;
use Thurly\Main\Entity\Query;

use Thurly\Tasks\Integration\IM;
use Thurly\Tasks\Internals\Runtime;
use Thurly\Tasks\Internals\Task\MemberTable;
use Thurly\Main\Entity\ReferenceField;
use Thurly\Tasks\Internals\TaskTable;
use Thurly\Tasks\Util\Type\DateTime;

Loc::loadMessages(__FILE__);

final class Task
{
	public static function createOverdueChats()
	{
		if($GLOBALS['__TASKS_DEVEL_ENV__'])
		{
			return;
		}

		if(IM\Task::includeModule())
		{
			$tasks = static::getOverdueTasks();
			foreach($tasks as $task)
			{
				$chatId = IM\Task::openChat($task);
				if($chatId)
				{
					$gender = \Thurly\Im\User::getInstance($task['RESPONSIBLE_ID'])->getGender(); // todo: move to bindings
					if(!$gender)
					{
						$gender = 'N';
					}

					IM\Task::postMessage(
						$chatId,
						Loc::getMessage('TASKS_IM_MESSAGE_OVERDUE_'.$gender)." [ICON=/thurly/images/emoji/1f625.svg]",
						$task
					);
				}
			}
		}
	}

	private static function getOverdueTasks()
	{
		$query = new Query(TaskTable::getEntity());

		Runtime::apply($query, array(
			\Thurly\Tasks\Integration\IM\Internals\RunTime::applyChatNotExist(),
		));

		$query->registerRuntimeField('', new ReferenceField('TM', MemberTable::getEntity(), array(
				'=ref.TASK_ID' => 'this.ID',
			)
		));

		$query->setFilter($query->getFilter() + array(
			'!DEADLINE' => null,
			'>=DEADLINE' => static::getDayStartDateTime(), // day start
			'<=DEADLINE' => new DateTime(), // current time
			'=STATUS' => array(
				1, // new
				2, // pending
				3, // in progress
			),
		));
		$query->setSelect(array(
			'ID',
			'TITLE',
			'DESCRIPTION',
			'DEADLINE',
			'STATUS',
			'TM_USER_ID' => 'TM.USER_ID',
			'TM_TYPE' => 'TM.TYPE',
		));

		$res = $query->exec();
		$merged = array();
		$uniqueMembers = array();
		while($item = $res->fetch())
		{
			$userId = $item['TM_USER_ID'];
			$type = $item['TM_TYPE'];
			unset($item['TM_USER_ID']);
			unset($item['TM_TYPE']);

			if(!array_key_exists($item['ID'], $merged))
			{
				$item['SE_MEMBER'] = array($userId => array(
					'USER_ID' => $userId,
					'TYPE' => $type,
				));
				$merged[$item['ID']] = $item;
			}
			else
			{
				$merged[$item['ID']]['SE_MEMBER'][$userId] = array(
					'USER_ID' => $userId,
					'TYPE' => $type,
				);
			}

			if(!array_key_exists($item['ID'], $uniqueMembers))
			{
				$uniqueMembers[$item['ID']] = array();
			}
			if(!array_key_exists($userId, $uniqueMembers[$item['ID']]))
			{
				$uniqueMembers[$item['ID']][$userId] = 1;
			}
			$uniqueMembers[$item['ID']][$userId]++;

			if($type == 'O')
			{
				$merged[$item['ID']]['CREATED_BY'] = $userId;
			}
			if($type == 'R')
			{
				$merged[$item['ID']]['RESPONSIBLE_ID'] = $userId;
			}
		}

		return array_filter($merged, function($item) use($uniqueMembers) {
			// creating chat for one person is meaningless
			return count($uniqueMembers[$item['ID']]) > 1;
		});
	}

	private static function getDayStartDateTime()
	{
		$now = new DateTime();
		$structure = $now->getTimeStruct();
		$now->add('-T'.($structure['SECOND'] + 60*$structure['MINUTE'] + 3600*$structure['HOUR']).'S');

		return $now;
	}
}