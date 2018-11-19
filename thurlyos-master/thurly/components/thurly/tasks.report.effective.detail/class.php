<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Thurly\Main\Localization\Loc;
use Thurly\Tasks\Util\Result;
use Thurly\Main\Grid;
use Thurly\Main\UI\Filter;
use Thurly\Main\Entity;
use Thurly\Main\Type\DateTime;
use Thurly\Tasks\Internals\Counter\EffectiveTable;

Loc::loadMessages(__FILE__);

CThurlyComponent::includeComponentClass("thurly:tasks.base");
CThurlyComponent::includeComponentClass("thurly:tasks.report.effective");

class TasksReportEffectiveDetailComponent extends TasksBaseComponent
{
	protected $pageSizes = array(
		array("NAME" => "5", "VALUE" => "5"),
		array("NAME" => "10", "VALUE" => "10"),
		array("NAME" => "20", "VALUE" => "20"),
		array("NAME" => "50", "VALUE" => "50"),
		array("NAME" => "100", "VALUE" => "100"),
		//Temporary limited by 100
		//array("NAME" => "200", "VALUE" => "200"),
	);

	protected $defaultPageSize = 50;

	protected function checkParameters()
	{
		// todo
		$arParams = &$this->arParams;
		static::tryParseStringParameter($arParams['GRID_ID'], \TasksReportEffectiveComponent::getFilterId());
		static::tryParseStringParameter($arParams['GROUP_ID'], $_GET['group_id']);

		static::tryParseStringParameter($arParams['PATH_TO_USER_PROFILE'], '/company/personal/user/#user_id#/');
		static::tryParseStringParameter($arParams['PATH_TO_TASK_DETAIL'], '/company/personal/user/#user_id#/tasks/task/view/#task_id#/');
		static::tryParseStringParameter($arParams['PATH_TO_GROUP_LIST'], '/workgroups/group/#group_id#/');

		static::tryParseStringParameter($arParams['USE_PAGINATION'], true);
		static::tryParseStringParameter($arParams['DEFAULT_PAGE_SIZE'], $this->defaultPageSize);
		static::tryParseArrayParameter($arParams['PAGE_SIZES'], $this->pageSizes);

		static::tryParseIntegerParameter($arParams['USER_ID'], $this->userId);

		return $this->errors->checkNoFatals();
	}

	protected function getData()
	{
		$this->arResult['VIOLATION_LIST'] = $this->getViolationList();
		$this->arParams['HEADERS'] = $this->getGridHeaders();
	}

	private function getGridHeaders()
	{
		return array(
			'TASK'=>array(
				'id'          => 'TASK',
				'name'        => GetMessage('TASKS_COLUMN_TASK'),
				'editable'    => false,
				'default'		=> true
			),
			'DATE'=>array(
				'id'          => 'DATE',
				'name'        => GetMessage('TASKS_COLUMN_CREATED_DATE'),
				'editable'    => false,
				'default' => true
			),
			'DATE_REPAIR' => array(
				'id' => 'DATE_REPAIR',
				'name' => GetMessage('TASKS_COLUMN_REPAIR_DATE'),
				'editable' => false,
				'default' => true
			),
			'USER_TYPE'=>array(
				'id'          => 'USER_TYPE',
				'name'        => GetMessage('TASKS_COLUMN_USER_TYPE'),
				'editable'    => false,
				'default'		=> false
			),
			'DEADLINE' => array(
				'id' => 'DEADLINE',
				'name' => GetMessage('TASKS_COLUMN_DEADLINE'),
				'editable' => false,
				'default' => false
			),
			'ORIGINATOR' => array(
				'id' => 'ORIGINATOR',
				'name' => GetMessage('TASKS_COLUMN_ORIGINATOR'),
				'editable' => false,
				'default' => false
			),
			'GROUP'=>array(
				'id'          => 'GROUP',
				'name'        => GetMessage('TASKS_COLUMN_GROUP'),
				'editable'    => false,
				'default'		=> false
			),
		);
	}

	/**
	 * @return Grid\Options
	 */
	private function getGridOptions()
	{
		static $instance = null;

		if(!$instance)
		{
			$instance = (new Grid\Options($this->arParams['GRID_ID']));
		}

		return $instance;
	}

	/**
	 * @return Filter\Options
	 */
	private static function getFilterOptions()
	{
		static $instance = null;

		if (!$instance)
		{
			$instance = new Filter\Options(\TasksReportEffectiveComponent::getFilterId(), \TasksReportEffectiveComponent::getPresetList());
		}

		return $instance;
	}

	/**
	 * @return array
	 */
	private function getFilterData()
	{
		$filters = \TasksReportEffectiveComponent::getFilterList();
		$filterOptions = $this->getFilterOptions();

		return $filterOptions->getFilter($filters);
	}


	private function getPageSize()
	{
		$navParams = $this->getGridOptions()
						  ->getNavParams(array(
						  		'nPageSize'=>$this->defaultPageSize
							));

		return (int)$navParams['nPageSize'];
	}

	private function getViolationList()
	{
		$filterData = $this->getFilterData();

		$violationFilter = array(
			'USER_ID' => $this->arParams['USER_ID'],

			'IS_VIOLATION' => 'Y',
			'>TASK.RESPONSIBLE_ID' => 0,

			array(
				'LOGIC' => 'OR',
				array(
					'>=DATETIME' => new Datetime($filterData['DATETIME_from']),
					'<=DATETIME' => new Datetime($filterData['DATETIME_to']),
				),
				array(
					'<=DATETIME' => new Datetime($filterData['DATETIME_to']),
					'=DATETIME_REPAIR' => false,
				),
				array(
					'<=DATETIME' => new Datetime($filterData['DATETIME_to']),
					'>=DATETIME_REPAIR' => new Datetime($filterData['DATETIME_from']),
				)
			)
		);

		if($filterData['GROUP_ID'] >0)
		{
			$violationFilter['GROUP_ID'] = $filterData['GROUP_ID'];
		}

		$navPageSize = $this->getPageSize();
		$nav = new \Thurly\Main\UI\PageNavigation("nav-effective");
		$nav->allowAllRecords(true)
			->setPageSize($navPageSize)
			->initFromUri();

		$violations = EffectiveTable::getList(array(
												  'count_total' => true,
												  'filter' => $violationFilter,
												  'offset' => $nav->getOffset(),
												  'limit' => $nav->getLimit(),
												  'order' => array('DATETIME' => 'DESC', 'TASK_TITLE' => 'ASC'),
												  'select' => array(
													  'TASK_ID',
													  'DATE' => 'DATETIME',
													  'DATE_REPAIR' => 'DATETIME_REPAIR',
													  'TASK_TITLE',
													  'TASK_DEADLINE',
													  'USER_TYPE',

													  'TASK_ORIGINATOR_ID' => 'TASK.CREATOR.ID',
													  'TASK_ZOMBIE' => 'TASK.ZOMBIE',

													  'GROUP_ID',
													  'GROUP_NAME' => 'GROUP.NAME',
			),
												  'group' => array('DATE'),
		));

		$nav->setRecordCount($violations->getCount());
		$this->arResult['NAV'] = $nav;

		$data = $violations->fetchAll();

		return $data;
	}
}
