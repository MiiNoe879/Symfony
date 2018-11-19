<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Thurly\Main\Localization\Loc;
use Thurly\Main\UI\Filter;
use Thurly\Tasks\Internals\Counter\EffectiveTable;
use Thurly\Tasks\Util\User;
use Thurly\Tasks\Internals\Effective;
use Thurly\Tasks\Util\Type\DateTime;
use Thurly\Main\Config;

Loc::loadMessages(__FILE__);

CThurlyComponent::includeComponentClass("thurly:tasks.base");

class TasksReportEffectiveComponent extends TasksBaseComponent
{
	protected $userId;
	protected $groupId;

	public static function getAllowedMethods()
	{
		return array(
			'getStat'
		);
	}

	protected function checkParameters()
	{
		// todo
		$arParams = &$this->arParams;
		static::tryParseStringParameter($arParams['FILTER_ID'], 'GRID_EFFECTIVE');

		static::tryParseStringParameter($arParams['PATH_TO_USER_PROFILE'], '/company/personal/user/#user_id#/');

		static::tryParseStringParameter(
			$arParams['PATH_TO_EFFECTIVE_DETAIL'],
			'/company/personal/user/#user_id#/tasks/effective/show/'
		);

		static::tryParseStringParameter(
			$arParams['PATH_TO_TASK_ADD'],
			'/company/personal/user/'.User::getId().'/tasks/task/edit/0/'
		);

		static::tryParseStringParameter($arParams['USE_PAGINATION'], true);
		static::tryParseStringParameter($arParams['DEFAULT_PAGE_SIZE'], $this->defaultPageSize);
		static::tryParseArrayParameter($arParams['PAGE_SIZES'], $this->pageSizes);

		$this->userId = $this->arParams['USER_ID'] ? $this->arParams['USER_ID'] : User::getId();
		$this->groupId = $this->arParams['GROUP_ID'] ? $this->arParams['GROUP_ID'] : 0;

		return $this->errors->checkNoFatals();
	}

	protected function getData()
	{
		$this->arResult['FILTERS'] = self::getFilterList();
		$this->arResult['PRESETS'] = self::getPresetList();

		$this->arResult['EFFECTIVE_DATE_START'] = $this->getEffectiveDate();

		$this->arResult['JS_DATA']['userId'] = $this->arParams['USER_ID'];
		$this->arResult['JS_DATA']['stat'] = self::getStat($this->arParams['USER_ID']);
	}

	private function getEffectiveDate()
	{
		$defaultDate = new Datetime();
		$format='Y-m-d H:i:s';
		$dateFromDb = Config\Option::get('tasks', 'effective_date_start', $defaultDate->format($format));
		$date = new DateTime($dateFromDb, $format);

		$dateFormatted = GetMessage('TASKS_EFFECTIVE_DATE_FORMAT', array(
			'#DAY#'=>$date->format('d'),
			'#MONTH_NAME#'=>GetMessage('TASKS_MONTH_'.$date->format('m')),
			'#YEAR_IF_DIFF#'=>$date->format('Y') != date('Y') ? $date->format('Y') : ''
		));

		return $dateFormatted;
	}

	public static function getFilterList()
	{
		return array(
			'GROUP_ID' => array(
				'id' => 'GROUP_ID',
				'name' => Loc::getMessage('TASKS_FILTER_COLUMN_GROUP_ID'),
				//				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'default' => true,
				'selector' => array(
					'TYPE' => 'group',
					'DATA' => array(
						'ID' => 'group',
						'FIELD_ID' => 'GROUP_ID'
					)
				)
			),
			'DATETIME' => array(
				'id' => 'DATETIME',
				'name' => Loc::getMessage('TASKS_FILTER_COLUMN_DATE'),
				'type' => 'date',
				"exclude" => array(
					\Thurly\Main\UI\Filter\DateType::TOMORROW,
					\Thurly\Main\UI\Filter\DateType::PREV_DAYS,
					\Thurly\Main\UI\Filter\DateType::NEXT_DAYS,
					\Thurly\Main\UI\Filter\DateType::NEXT_WEEK,
					\Thurly\Main\UI\Filter\DateType::NEXT_MONTH
				),
				'default' => true,
			),
		);
	}

	public static function getPresetList()
	{
		return \Thurly\Tasks\Internals\Effective::getPresetList();
	}

	public static function getStat($userId)
	{
		$filter = self::processFilter();

		$groupByHour = false;
		if (isset($filter['::']) && $filter['::'] == 'BY_DAY')
		{
			unset($filter['::']);
			$groupByHour = true;
		}
		$groupId = array_key_exists('GROUP_ID', $filter) && $filter['GROUP_ID'] > 0? $filter['GROUP_ID'] : 0;

		$dt = array_key_exists('>=DATETIME', $filter) ? $filter['>=DATETIME'] : null;
		$dateFrom = $dt ? new DateTime($dt) : null;

		$dt = array_key_exists('<=DATETIME', $filter) ? $filter['<=DATETIME'] : null;
		$dateTo = $dt ? new DateTime($dt) : null;

		$middleRatio = round(Effective::getByRange(new Datetime($dateFrom), new DateTime($dateTo), $userId, $groupId));
		$middleRatio = (int)$middleRatio < 0 ? 0 : $middleRatio;

		$counters = self::getCountersByRange(new Datetime($dateFrom), new DateTime($dateTo), $userId, $groupId);
		if (($counters['CLOSED'] + $counters['OPENED']) == 0)
		{
			$myRatioNew = 100;
		}
		else
		{
			$myRatioNew = round(100 - ($counters['VIOLATIONS'] / ($counters['CLOSED'] + $counters['OPENED'])) * 100);
		}

		$graphDataRes = Effective::getStatByRange(
			new Datetime($dateFrom),
			new DateTime($dateTo),
			$userId,
			$groupId,
			$groupByHour ? 'HOUR' : ''
		);

		$graphData = array();
		foreach ($graphDataRes as $row)
		{
			if ($groupByHour)
			{
				$row['DATE'] = $row['HOUR'];
			}
			else
			{
				$row['DATE'] = $row['DATE']->format('Y-m-d');
			}

			$row['KPI'] = round($row['EFFECTIVE']);
			$row['AVG'] = $middleRatio;

			$graphData[$row['DATE']] = $row;
		}

		//TODO REFACTOR MOTHER...!!!
		if (!isset($graphData[date('Y-m-d')]) && !$groupByHour)
		{
			$graphData[date('Y-m-d')] = array(
				'KPI' => $myRatioNew,
				'EFFECTIVE' => $myRatioNew,
				'AVG' => $middleRatio,
				'DATE' => date('Y-m-d')
			);
		}

		ksort($graphData);
		$graphDataOut = array();
		foreach ($graphData as $row)
		{
			$graphDataOut[] = $row;
		}


		return array(
			'MY_RATIO' => (int)$myRatioNew,
			'CLOSED' => (int)$counters['CLOSED'],
			'VIOLATION' => (int)$counters['VIOLATIONS'],
			'IN_PROGRESS' => (int)$counters['CLOSED'] + $counters['OPENED'],
			'GRAPH_DATA' => $graphDataOut,
			'GRAPH_MIN_PERIOD' => $groupByHour ? 'hh' : 'DD'
		);
	}

	private static function getCountersByRange(Datetime $dateFrom, Datetime $dateTo, $userId, $groupId = 0)
	{
		$out = array();

		$violationFilter = array(
			'USER_ID'=>$userId,
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

		if($groupId > 0)
		{
			$violationFilter['GROUP_ID'] = $groupId;
		}

		//TODO: refactor this!!
		$violations = EffectiveTable::getList(array(
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
		));

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
				
				". ($groupId>0 ? "AND t.GROUP_ID = {$groupId}" : '')."
				
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

	private static function processFilter()
	{
		static $arrFilter = array();

		if(!$arrFilter)
		{
			$rawFilter = self::getFilterOptions()->getFilter(self::getFilterList());

			if (!array_key_exists('FILTER_APPLIED', $rawFilter) || $rawFilter['FILTER_APPLIED'] != true)
			{
				return array();
			}

			foreach (self::getFilterList() as $item)
			{
				switch ($item['type'])
				{
					case 'custom_entity':
						$arrFilter[$item['id']] = $rawFilter[$item['id']];
						break;
					case 'date':
						if (array_key_exists($item['id'].'_from', $rawFilter) &&
							!empty($rawFilter[$item['id'].'_from']))
						{
							$arrFilter['>='.$item['id']] = $rawFilter[$item['id'].'_from'];
						}
						if (array_key_exists($item['id'].'_to', $rawFilter) && !empty($rawFilter[$item['id'].'_to']))
						{
							$arrFilter['<='.$item['id']] = $rawFilter[$item['id'].'_to'];
						}

						if ($rawFilter[$item['id'].'_datesel'] == \Thurly\Main\UI\Filter\DateType::CURRENT_DAY)
						{
							$arrFilter['::'] = 'BY_DAY';
						}
						break;
				}
			}
		}

		return $arrFilter;
	}

	public static function getFilterId()
	{
		return \Thurly\Tasks\Internals\Effective::getFilterId();
	}

	private static function getFilterOptions()
	{
		static $instance = null;

		if (!$instance)
		{
			$instance = new Filter\Options(self::getFilterId(), self::getPresetList());
		}

		return $instance;
	}
}
