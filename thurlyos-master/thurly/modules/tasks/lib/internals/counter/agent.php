<?php

namespace Thurly\Tasks\Internals\Counter;

use Thurly\Main\Event;
use Thurly\Tasks\Util\Type\DateTime;
use Thurly\Tasks\Internals\Counter;
use Thurly\Tasks\Internals\Effective;
use Thurly\Tasks\Item\Task;

class Agent
{
	const EVENT_TASK_EXPIRED = 'OnTaskExpired';
	const EVENT_TASK_EXPIRED_SOON = 'OnTaskExpiredSoon';

	public static function add($taskId, DateTime $deadline, $forceExpired = false)
	{
		$task = Task::getInstance($taskId, 1);
		if (!$task || in_array($task->status, array(\CTasks::STATE_COMPLETED, \CTasks::STATE_SUPPOSEDLY_COMPLETED)))
		{
			return false;
		}

		$expired = Counter::getExpiredTime();
		$expiredSoon = Counter::getExpiredSoonTime();
		$now = new Datetime();

		if ($now->checkLT($expired))
		{
			$soon = '';
			$agentStart = $now;
		}
		else if ($forceExpired || ($expired->checkLT($deadline) && $expiredSoon->checkGT($deadline)))
		{
			$soon = '';
			$agentStart = $deadline;
		}
		else
		{
			$soon = 'Soon';
			$agentStart = $expiredSoon->checkLT($deadline) ? $deadline : $expired;
		}

		$agentName = self::getClass()."::expired{$soon}({$taskId});";

		self::remove($taskId);
		\CAgent::AddAgent($agentName, 'tasks', 'Y', 0, '', 'Y', $agentStart);
	}

	private static function recountSoon($taskId)
	{
		$task = Task::getInstance($taskId, 1);
		if (!$task || in_array($task->status, array(\CTasks::STATE_COMPLETED, \CTasks::STATE_SUPPOSEDLY_COMPLETED)))
		{
			return false;
		}

		$controllerDefault = $task->getAccessController();
		$controller = $controllerDefault->spawn();
		$controller->disable();
		$task->setAccessController($controller);

		$responsible = Counter::getInstance($task->responsibleId, $task->groupId);
		$responsible->recount(Counter\Name::MY_EXPIRED);
		$responsible->recount(Counter\Name::MY_EXPIRED_SOON);

		if ($task->accomplices)
		{
			foreach ($task->accomplices as $userId)
			{
				$responsible = Counter::getInstance($userId, $task->groupId);
				$responsible->recount(Counter\Name::ACCOMPLICES_EXPIRED);
				$responsible->recount(Counter\Name::ACCOMPLICES_EXPIRED_SOON);
			}
		}
		return true;
	}

	public static function remove($taskId)
	{
		\CAgent::RemoveAgent(self::getClass()."::expired({$taskId});", 'tasks');
		\CAgent::RemoveAgent(self::getClass()."::expiredSoon({$taskId});", 'tasks');
	}

	public static function getClass()
	{
		return get_called_class();
	}

	public static function expired($taskId)
	{
		$task = Task::getInstance($taskId, 1);
		if (!$task ||
			!$task->responsibleId ||
			in_array($task->status, array(\CTasks::STATE_COMPLETED, \CTasks::STATE_SUPPOSEDLY_COMPLETED)))
		{
			return false;
		}

		$controllerDefault = $task->getAccessController();
		$controller = $controllerDefault->spawn();
		$controller->disable();
		$task->setAccessController($controller);

		$responsible = Counter::getInstance($task->responsibleId, $task->groupId);
		$responsible->recount(Counter\Name::MY_EXPIRED_SOON);
		$responsible->recount(Counter\Name::MY_EXPIRED);

		$originator = Counter::getInstance($task->createdBy, $task->groupId);
		$originator->recount(Counter\Name::ORIGINATOR_EXPIRED);

		Effective::modify($task->responsibleId, 'R', $task, $task->groupId);

		if ($task->auditors)
		{
			foreach ($task->auditors as $userId)
			{
				$auditor = Counter::getInstance($userId, $task->groupId);
				$auditor->recount(Counter\Name::AUDITOR_EXPIRED);
			}
		}

		if ($task->accomplices)
		{
			foreach ($task->accomplices as $userId)
			{
				$accomplice = Counter::getInstance($userId, $task->groupId);
				$accomplice->recount(Counter\Name::ACCOMPLICES_EXPIRED);
				$accomplice->recount(Counter\Name::ACCOMPLICES_EXPIRED_SOON);

				if ($task->responsibleId != $userId)
				{
					Effective::modify($userId, 'A', $task, $task->groupId);
				}
			}
		}

		$event = new Event(
			"tasks", self::EVENT_TASK_EXPIRED, array('TASK_ID' => $task->getId(), 'TASK' => $task->getData())
		);
		$event->send();

		return '';
	}

	public static function expiredSoon($taskId)
	{
		if(!self::recountSoon($taskId))
		{
			return '';
		}

		$task = Task::getInstance($taskId, 1);
		if (!$task || in_array($task->status, array(\CTasks::STATE_COMPLETED, \CTasks::STATE_SUPPOSEDLY_COMPLETED)))
		{
			return false;
		}

		$event = new Event(
			"tasks", self::EVENT_TASK_EXPIRED_SOON, array('TASK_ID' => $task->getId(), 'TASK' => $task->getData())
		);
		$event->send();

		if ($task->deadline)
		{
			self::add($task->getId(), $task->deadline, true);
		}
		return '';
	}

	/**
	 * @deprecated not used
	 */
	public static function start()
	{
		return '';
	}

	public static function install()
	{
		$res = \CAgent::GetList(array(), array('MODULE_ID' => 'tasks', 'NAME' => '%Agent::expired%'));
		while ($t = $res->Fetch())
		{
			\CAgent::Delete($t['ID']);
		}

		\CTimeZone::Disable();
		\CAgent::AddAgent(
			'\Thurly\Tasks\Internals\Counter\Agent::installNextStep(0);',
			'tasks',
			'N',
			30,
			'',
			'Y',
			ConvertTimeStamp(time()+10, "FULL")
		);
		\CTimeZone::Enable();

		return '';
	}

	public static function installNextStep($lastId = 0)
	{
		global $DB;
		$limit = 50;
		$lastId = (int)$lastId;
		$found = false;

		$res = $DB->Query(
			"
			SELECT ID, DEADLINE FROM b_tasks 
			WHERE 
			  ZOMBIE='N' AND 
			  STATUS < 4 AND
			  DEADLINE <> '' AND
              ID > {$lastId}
			LIMIT {$limit}
		");

		while ($t = $res->Fetch())
		{
			self::add($t['ID'], DateTime::createFrom($t['DEADLINE']));
			$lastId = $t['ID'];
			$found = true;
		}

		if($found)
		{
			return "\\Thurly\\Tasks\\Internals\\Counter\\Agent::installNextStep({$lastId});";
		}
		else
		{
			return '';
		}
	}
}
