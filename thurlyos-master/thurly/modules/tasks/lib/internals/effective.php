<?php

namespace Thurly\Tasks\Internals;

use Thurly\Main\Application;
use Thurly\Main\Entity;
use Thurly\Tasks\Util\Type\DateTime;
use Thurly\Tasks\Internals\Counter\EffectiveTable;
use Thurly\Tasks\Item\Task;
use Thurly\Main\Localization\Loc;
use Thurly\Main\UI\Filter;

Loc::loadMessages(__FILE__);

class Effective
{
	public static function getFilterId()
	{
		return 'TASKS_REPORT_EFFECTIVE_GRID';
	}

	public static function getPresetList()
	{
		return array(
			'filter_tasks_range_day' => array(
				'name' => Loc::getMessage('TASKS_PRESET_CURRENT_DAY'),
				'default' => false,
				'fields' => array(
					"DATETIME_datesel" => \Thurly\Main\UI\Filter\DateType::CURRENT_DAY
				)
			),
			'filter_tasks_range_month' => array(
				'name' => Loc::getMessage('TASKS_PRESET_CURRENT_MONTH'),
				'default' => true,
				'fields' => array(
					"DATETIME_datesel" => \Thurly\Main\UI\Filter\DateType::CURRENT_MONTH
				)
			),
			'filter_tasks_range_quarter' => array(
				'name' => Loc::getMessage('TASKS_PRESET_CURRENT_QUARTER'),
				'default' => false,
				'fields' => array(
					"DATETIME_datesel" => \Thurly\Main\UI\Filter\DateType::CURRENT_QUARTER
				)
			)
		);
	}

	public static function modify($userId, $userType, Task $task, $groupId = 0, $isViolation = null)
	{
		if (!$userId ||
			!$task->responsibleId ||
			!$task->createdBy ||
			($userType == 'R' && $task->responsibleId == $task->createdBy))
		{
			return false;
		}

		$violations = self::calcViolations($userId, $groupId);
		$inProgress = self::calcInProgress($userId, $groupId);

		$effective = 100;
		if($inProgress > 0)
		{
			$effective = round(
				100 - ($violations / $inProgress) * 100
			);
		}

		if($isViolation === null)
		{
			$isViolation = self::isViolation($task);
		}

		$dateTime = new Datetime();

		EffectiveTable::add(
			array(
				'DATETIME' => $dateTime,
				'USER_ID' => $userId,
				'USER_TYPE' => $userType,
				'GROUP_ID' => (int)$groupId,
				'EFFECTIVE' => $effective,
				'TASK_ID' => $task->getId(),

				'TASK_TITLE' => $task->title,
				'TASK_DEADLINE' => $task->deadline,

				'IS_VIOLATION'=>$isViolation  ? 'Y' : 'N'
			)
		);

		return '\Thurly\Tasks\Internals\Effective::agent();';
	}

	public static function repair($taskId, $userId = null, $userType = 'R')
	{
		$taskId = (int)$taskId;
		$sql = "
		UPDATE b_tasks_effective SET DATETIME_REPAIR = NOW() WHERE 
			TASK_ID = {$taskId} AND IS_VIOLATION='Y' AND DATETIME_REPAIR IS NULL
		";

		if ($userId > 0)
		{
			$userType = $userType == 'A' ? 'A' : 'R';
			$sql .= ' AND USER_ID = '.$userId.' AND USER_TYPE = \''.$userType.'\'';
		}

		Application::getConnection()->queryExecute($sql);

		return true;
	}

	private static function isViolation(Task $task)
	{
		if(!$task->deadline)
		{
			return false;
		}

		$deadline = DateTime::createFrom($task->deadline);
		$now = new Datetime();

		return $deadline->checkLT($now);
	}

	private static function getDefaultTimeRangeFilter()
	{
		$filterOptions = new Filter\Options(
			Effective::getFilterId(), Effective::getPresetList()
		);

		$defId = $filterOptions->getDefaultFilterId();
		$settings = $filterOptions->getFilterSettings($defId);
		$filtersRaw = Filter\Options::fetchFieldValuesFromFilterSettings($settings);

		$dateFrom = DateTime::createFrom($filtersRaw['DATETIME_from']);
		$dateTo = DateTime::createFrom($filtersRaw['DATETIME_to']);

		if (!$dateFrom || !$dateTo)
		{
			$currentDate = new Datetime();

			$dateFrom = DateTime::createFromTimestamp(
				strtotime($currentDate->format('01.m.Y 00:00:01'))
			);

			$dateTo = DateTime::createFromTimestamp(
				strtotime($currentDate->format('28.m.Y 23:59:59'))
			);
		}

		return array(
			'FROM' => $dateFrom,
			'TO' => $dateTo
		);
	}

	public static function getByRange(DateTime $timeFrom = null, Datetime $timeTo = null, $userId = null, $groupId = 0)
	{
		if(!$timeFrom || !$timeTo)
		{
			$times = self::getDefaultTimeRangeFilter();
			$timeFrom = $times['FROM'];
			$timeTo = $times['TO'];
		}

		$params = array(
			'filter' => array(
				'>=DATETIME' => $timeFrom,
				'<=DATETIME' => $timeTo
			),
			'select' => array('EFFECTIVE'),
			'runtime' => array(
				new Entity\ExpressionField('EFFECTIVE', 'AVG(EFFECTIVE)')
			)
		);

		if ($userId > 0)
		{
			$params['filter']['USER_ID'] = $userId;
			$params['group'][]='USER_ID';
		}

		if ($groupId > 0)
		{
			$params['filter']['GROUP_ID'] = $groupId;
			$params['group'][]='GROUP_ID';
		}

		$result = EffectiveTable::getRow($params);

		return $result ? $result['EFFECTIVE'] : 100;
	}

	public static function getStatByRange(DateTime $timeFrom = null, Datetime $timeTo = null, $userId = null,
										  $groupId = 0, $groupBy = 'DATE')
	{
		$availGroupsBy = array('DATE', 'HOUR');
		if (!in_array($groupBy, $availGroupsBy))
		{
			$groupBy = 'DATE';
		}

		$params = array(
			'filter' => array(
				'>=DATETIME' => $timeFrom,
				'<=DATETIME' => $timeTo,
				'USER_ID' => $userId
			),
			'select' => array('EFFECTIVE', $groupBy == 'DATE' ? 'DATE' : 'HOUR'),
			'runtime' => array(
				new Entity\ExpressionField('EFFECTIVE', 'AVG(EFFECTIVE)'),
				new Entity\ExpressionField('DATE', 'DATE(DATETIME)'),
				new Entity\ExpressionField('HOUR', 'DATE_FORMAT(DATETIME, "%%Y-%%m-%%d %%H:00:01")'),
			),
			'group'=>array(
				$groupBy
			)
		);

		if ($userId > 0)
		{
			$params['filter']['USER_ID'] = $userId;
			$params['group'][]='USER_ID';
		}

		if ($groupId > 0)
		{
			$params['filter']['GROUP_ID'] = $groupId;
			$params['group'][]='GROUP_ID';
		}

		//echo '<pre>'.print_r($params,true).'</pre>';

		$result = EffectiveTable::getList($params);

		return $result->fetchAll();
	}

	private static function calcInProgress($userId, $groupId = 0)
	{
		$deffered = \CTasks::STATE_DEFERRED;

		$sql = "
			SELECT 
				COUNT(t.ID) as COUNT,
				t.GROUP_ID
			FROM 
				b_tasks AS t
				JOIN b_tasks_member as tm ON 
					tm.TASK_ID = t.ID AND 
					tm.USER_ID = {$userId} AND
					tm.TYPE IN('A', 'R') 
			WHERE
				(
					(tm.USER_ID = {$userId} AND tm.TYPE='R' AND t.CREATED_BY != t.RESPONSIBLE_ID)
					OR 
					(tm.USER_ID = {$userId} AND tm.TYPE='A' AND (t.CREATED_BY != {$userId} AND t.RESPONSIBLE_ID != {$userId}))
				) AND 
				t.ZOMBIE = 'N'
				
				".($groupId > 0 ? "AND t.GROUP_ID = {$groupId}" : "")."
				
				AND 
				(
					(t.CLOSED_DATE IS NULL AND STATUS != {$deffered})
					OR 
					DATE(t.CLOSED_DATE) = DATE(NOW())
				)
			GROUP BY 
				t.GROUP_ID
		";

		$counters = Application::getConnection()->query($sql)->fetch();
		return $counters['COUNT'];
	}

	private static function calcViolations($userId, $groupId = 0)
	{
		$expiredTime = Counter::getExpiredTime()->format('Y-m-d H:i:s');

		$sql = "
			SELECT 
				COUNT(t.ID) as COUNT,
				t.GROUP_ID
			FROM 
				b_tasks as t
				INNER JOIN b_tasks_member as tm 
					ON tm.TASK_ID = t.ID AND tm.TYPE IN ('R', 'A')
			WHERE 
				(
					(tm.USER_ID = {$userId} AND tm.TYPE='R' AND t.CREATED_BY != t.RESPONSIBLE_ID)
					OR 
					(tm.USER_ID = {$userId} AND tm.TYPE='A' AND (t.CREATED_BY != {$userId} AND t.RESPONSIBLE_ID != {$userId}))
				)  
				
				AND STATUS  < 4
				AND STATUS  != 6
				
				AND t.DEADLINE < '{$expiredTime}'
				AND t.ZOMBIE = 'N'
				
				".($groupId > 0 ? "AND t.GROUP_ID = {$groupId}" : "")."
				
				AND t.CLOSED_DATE IS NULL
			GROUP BY 
				t.GROUP_ID
		";

		$res = Application::getConnection()->query($sql);
		if(!$res)
		{
			return 0;
		}

		$counters = $res->fetch();
		return $counters['COUNT'];
	}

	public static function agent($date = '')
	{
		$date = $date ? (new DateTime($date, 'Y-m-d')) : new DateTime();

		$sql = "
			SELECT DISTINCT ef1.USER_ID FROM b_tasks_effective ef1 WHERE NOT EXISTS (
				SELECT 
					ef2.ID 
				FROM 
					b_tasks_effective ef2 
				WHERE 
					DATE(ef2.DATETIME) = DATE('{$date->format('Y-m-d')}') AND 
					ef2.USER_ID = ef1.USER_ID
			)
		";

		$users = Application::getConnection()->query($sql)->fetchAll();
		if (!empty($users))
		{
			foreach ($users as $user)
			{
				$userId = $user['USER_ID'];
				$violations = self::calcViolations($userId);
				$inProgress = self::calcInProgress($userId);

				$effective = 100;
				if ($inProgress > 0)
				{
					$effective = round(
						100 - ($violations / $inProgress) * 100
					);
				}

				EffectiveTable::add(
					array(
						'DATETIME' => $date,
						'USER_ID' => $userId,
						'USER_TYPE' => '',
						'GROUP_ID' => 0,
						'EFFECTIVE' => $effective,
						'TASK_ID' => '',
						'IS_VIOLATION' => 'N'
					)
				);
			}
		}

		$date->addDay(1);

		return '\Thurly\Tasks\Internals\Effective::agent("'.$date->format('Y-m-d').'");';
	}

	public static function getMiddleCounter($userId, $groupId = 0, $dateFrom = null, $dateTo = null)
	{
		if (!$dateTo || !$dateFrom)
		{
			$times = self::getDefaultTimeRangeFilter();
			$dateTo = $times['TO'];
			$dateFrom = $times['FROM'];
		}

		$counters = self::getCountersByRange($dateFrom, $dateTo, $userId, $groupId = 0);

		if (($counters['CLOSED'] + $counters['OPENED']) == 0)
		{
			$kpi = 100;
		}
		else
		{
			$kpi = round(100 - ($counters['VIOLATIONS'] / ($counters['CLOSED'] + $counters['OPENED'])) * 100);
		}

		return $kpi < 0 ? 0 : $kpi;
	}

	private static function getCountersByRange(Datetime $dateFrom, Datetime $dateTo, $userId, $groupId = 0)
	{
		$out = array();

		$violationFilter = array(
			'USER_ID' => $userId,
			'IS_VIOLATION' => 'Y',
			'>TASK.RESPONSIBLE_ID' => 0,

			array(
				'LOGIC' => 'OR',
				array(
					'>=DATETIME' => $dateFrom,
					'<=DATETIME' => $dateTo,
				),
				array(
					'<=DATETIME' => $dateTo,
					'=DATETIME_REPAIR' => false,
				),
				array(
					'<=DATETIME' => $dateTo,
					'>=DATETIME_REPAIR' => $dateFrom,
				)
			)
		);

		if ($groupId > 0)
		{
			$violationFilter['GROUP_ID'] = $groupId;
		}

		//TODO: refactor this!!
		$violations = EffectiveTable::getList(
			array(
				'count_total' => true,
				'filter' => $violationFilter,
				'order' => array('DATETIME' => 'DESC', 'TASK_TITLE' => 'ASC'),
				'select' => array(
					'TASK_ID',
					'DATE' => 'DATETIME',
					'TASK_TITLE',
					'TASK_DEADLINE',
					'USER_TYPE',

					'TASK_ORIGINATOR_ID' => 'TASK.CREATOR.ID',

					'GROUP_ID'
				),
				'group' => array('DATE'),
			)
		);

		$out['VIOLATIONS'] = (int)$violations->getCount();

		$sql = "
			SELECT 
				COUNT(t.ID) as COUNT
			FROM 
				b_tasks as t
				JOIN b_tasks_member as tm ON tm.TASK_ID = t.ID AND tm.TYPE IN ('R', 'A')
			WHERE
				(
					(tm.USER_ID = {$userId} AND tm.TYPE='R' AND t.CREATED_BY != t.RESPONSIBLE_ID)
					OR 
					(tm.USER_ID = {$userId} AND tm.TYPE='A' AND (t.CREATED_BY != {$userId} AND t.RESPONSIBLE_ID != {$userId}))
				)
				
				".($groupId > 0 ? "AND t.GROUP_ID = {$groupId}" : '')."
				
				AND 
					t.CLOSED_DATE >= '".$dateFrom->format('Y-m-d H:i:s')."'
					AND t.CLOSED_DATE <= '".$dateTo->format('Y-m-d H:i:s')."'
			";

		$res = \Thurly\Main\Application::getConnection()->query($sql)->fetch();
		$out['CLOSED'] = (int)$res['COUNT'];

		$sql = "
            SELECT 
                COUNT(t.ID) as COUNT
            FROM 
                b_tasks as t
                JOIN b_tasks_member as tm ON tm.TASK_ID = t.ID  AND tm.TYPE IN ('R', 'A')
            WHERE
                (
                    (tm.USER_ID = {$userId} AND tm.TYPE='R' AND t.CREATED_BY != t.RESPONSIBLE_ID)
                    OR 
                    (tm.USER_ID = {$userId} AND tm.TYPE='A' AND (t.CREATED_BY != {$userId} AND t.RESPONSIBLE_ID != {$userId}))
                )
                
                ".($groupId > 0 ? "AND t.GROUP_ID = {$groupId}" : '')."
                
                AND t.CREATED_DATE <= '".$dateTo->format('Y-m-d H:i:s')."'
				AND 
				(
					t.CLOSED_DATE >= '".$dateFrom->format('Y-m-d H:i:s')."'
					OR
					CLOSED_DATE is null
				)
				
                AND t.ZOMBIE = 'N'
                AND t.STATUS != 6
            ";

		$res = \Thurly\Main\Application::getConnection()->query($sql)->fetch();
		$out['OPENED'] = (int)$res['COUNT'];

		return $out;
	}
}