<?php

namespace Thurly\Tasks\Helper;

use Thurly\Main\Context;
use Thurly\Main\Localization\Loc;
use Thurly\Tasks\Internals\Counter;
use Thurly\Tasks\Util\User;

class Filter extends Common
{
	protected static $instance = null;

	public function getDefaultRoleId()
	{
		static $roleId = null;

		if (!$roleId)
		{
			$request = Context::getCurrent()->getRequest();
			if ($request->isAjaxRequest())
			{
				return false;
			}

			$filterOptions = $this->getOptions();
			$filter = $filterOptions->getFilter();

			$fState = $request->get('F_STATE');
			if ($fState && !is_array($fState) && substr($fState, 0, 2) == 'sR')
			{
				$roleCode = $request->get('F_STATE');

				switch ($roleCode)
				{
					case 'sR400': // i do
						$roleId = Counter\Role::RESPONSIBLE;
						break;
					case 'sR800': // acc
						$roleId = Counter\Role::ACCOMPLICE;
						break;
					case 'sRc00': // au
						$roleId = Counter\Role::AUDITOR;
						break;
					case 'sRg00': // orig
						$roleId = Counter\Role::ORIGINATOR;
						break;
					default: // all
						$roleId = '';
						break;
				}

				$currentPresetId = $filterOptions->getCurrentFilterId();
				$filterSettings = $filterOptions->getFilterSettings($currentPresetId);

				if (!array_key_exists('ROLEID', $filterSettings['fields']) || !$filterSettings['fields']['ROLEID'])
				{
					if ($roleId)
					{
						$filterSettings['additional']['ROLEID'] = $roleId;
					}
					else
					{
						unset($filterSettings['additional']['ROLEID']);
					}
				}

				$filterOptions->setFilterSettings($currentPresetId, $filterSettings, true, false);
				$filterOptions->save();
			}
			else
			{
				$roleId = $filter['ROLEID'];
			}
		}

		return $roleId;
	}

	/**
	 * @return \Thurly\Main\UI\Filter\Options
	 */
	public function getOptions()
	{
		static $instance = null;

		if (!$instance)
		{
			$instance = new \Thurly\Main\UI\Filter\Options($this->getId(), static::getPresets());
		}

		return $instance;
	}

	/**
	 * @return array
	 */
	public static function getPresets()
	{
		$presets = array(
			'filter_tasks_in_progress' => array(
				'name' => Loc::getMessage('TASKS_PRESET_IN_PROGRESS'),
				'default' => true,
				'fields' => array(
					'STATUS' => array(
						\CTasks::STATE_PENDING,
						\CTasks::STATE_IN_PROGRESS
					)
				)
			),
			'filter_tasks_completed' => array(
				'name' => Loc::getMessage('TASKS_PRESET_COMPLETED'),
				'default' => false,
				'fields' => array(
					'STATUS' => array(
						\CTasks::STATE_COMPLETED
					)
				)
			),
			'filter_tasks_deferred' => array(
				'name' => Loc::getMessage('TASKS_PRESET_DEFERRED'),
				'default' => false,
				'fields' => array(
					'STATUS' => array(
						\CTasks::STATE_DEFERRED
					)
				)
			),
			'filter_tasks_expire' => array(
				'name' => Loc::getMessage('TASKS_PRESET_EXPIRED'),
				'default' => false,
				'fields' => array(
					'PROBLEM' => \CTaskListState::VIEW_TASK_CATEGORY_EXPIRED
				)
			),
			'filter_tasks_expire_candidate' => array(
				'name' => Loc::getMessage('TASKS_PRESET_EXPIRED_CAND'),
				'default' => false,
				'fields' => array(
					'PROBLEM' => \CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES
				)
			)
		);

		return $presets;
	}

	public function process()
	{
		$arrFilter = $this->processMainFilter();

		$arrFilter['ZOMBIE'] = 'N';
		$arrFilter['CHECK_PERMISSIONS'] = 'Y';
		$arrFilter['ONLY_ROOT_TASKS'] = 'Y';


		//hack, WHY? Dont know! ohhh, god!
		if (!isset($arrFilter['::SUBFILTER-ROLEID']) && isset($arrFilter['MEMBER']))
		{
			$arrFilter['::SUBFILTER-ROLEID']['MEMBER'] = $arrFilter['MEMBER'];
			unset($arrFilter['MEMBER']);
		}

		//hack, WHY? Dont know! ohhh, god!
		if (isset($arrFilter['::SUBFILTER-PROBLEM']['VIEWED_BY']) && $this->getUserId() != User::getId())
		{
			$arrFilter = array_merge($arrFilter, $arrFilter['::SUBFILTER-PROBLEM']);
			unset($arrFilter['::SUBFILTER-PROBLEM']);
		}

		if ($this->getGroupId() > 0)
		{
			$arrFilter['GROUP_ID'] = $this->getGroupId();
		}

		//		echo '<pre>'.print_r($arrFilter,true).'</pre>';

		return $arrFilter;
	}

	private function processMainFilter()
	{
		$filters = $this->getFilters();

		$this->getDefaultRoleId();

		$arrFilter = array();

		if ($this->getGroupId() > 0)
		{
			$arrFilter['GROUP_ID'] = $this->getGroupId();
		}

		if ($this->isFilterEmpty() && $this->getGroupId() == 0)
		{
			$arrFilter['MEMBER'] = $this->getUserId(); //TODO

			return $arrFilter;
		}

		if ($this->getFilterFieldData('FIND'))
		{
			$arrFilter['*%SEARCH_INDEX'] = $this->getFilterFieldData('FIND');
		}

		foreach ($filters as $fieldId => $filterRow)
		{
			$rawFilter = array();
			switch ($filterRow['type'])
			{
				default:
					$field = $this->getFilterFieldData($filterRow['id']);
					if ($field)
					{
						if (is_numeric($field) && $filterRow['id'] != 'TITLE')
						{
							$rawFilter[$filterRow['id']] = $field;
						}
						else
						{
							$rawFilter['%'.$filterRow['id']] = $field;
						}
					}
					break;
				case 'date':
					$rawFilter = $this->getDateFilterFieldData($filterRow);
					break;
				case 'number':
					$rawFilter = $this->getNumberFilterFieldData($filterRow);

					break;
				case 'list':
					$rawFilter = $this->getListFilterFieldData($filterRow);
					break;
			}

			if ($rawFilter)
			{
				$arrFilter['::SUBFILTER-'.$fieldId] = $rawFilter;
			}
		}

		if (isset($arrFilter['::SUBFILTER-PARAMS']['::REMOVE-MEMBER']))
		{
			unset($arrFilter['::SUBFILTER-ROLEID']['MEMBER']);
			unset($arrFilter['::SUBFILTER-PARAMS']['::REMOVE-MEMBER']);
		}

		return $arrFilter;
	}

	/**
	 * @return array
	 */
	public function getFilters()
	{
		static $filters = array();

		if (empty($filters))
		{
			$filters = $this->getFilterRaw();
		}

		return $filters;
	}

	/**
	 * @return array
	 */
	private function getFilterRaw()
	{
		$fields = $this->getAvailableFields();
		$filter = array();

		if (in_array('CREATED_BY', $fields))
		{
			$filter['CREATED_BY'] = array(
				'id' => 'CREATED_BY',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_CREATED_BY'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'CREATED_BY'
					)
				)
			);
		}

		if (in_array('RESPONSIBLE_ID', $fields))
		{
			$filter['RESPONSIBLE_ID'] = array(
				'id' => 'RESPONSIBLE_ID',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_RESPONSIBLE_ID'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'RESPONSIBLE_ID'
					)
				)
			);
		}

		if (in_array('STATUS', $fields))
		{
			$filter['STATUS'] = array(
				'id' => 'STATUS',
				'name' => Loc::getMessage('TASKS_FILTER_STATUS'),
				'type' => 'list',
				'params' => array(
					'multiple' => 'Y'
				),
				'items' => array(
					\CTasks::STATE_PENDING => Loc::getMessage('TASKS_STATUS_2'),
					\CTasks::STATE_IN_PROGRESS => Loc::getMessage('TASKS_STATUS_3'),
					\CTasks::STATE_SUPPOSEDLY_COMPLETED => Loc::getMessage('TASKS_STATUS_4'),
					\CTasks::STATE_COMPLETED => Loc::getMessage('TASKS_STATUS_5'),
					\CTasks::STATE_DEFERRED => Loc::getMessage('TASKS_STATUS_6')
				)
			);
		}

		if (in_array('DEADLINE', $fields))
		{
			$filter['DEADLINE'] = array(
				'id' => 'DEADLINE',
				'name' => Loc::getMessage('TASKS_FILTER_DEADLINE'),
				'type' => 'date'
			);
		}

		if (in_array('GROUP_ID', $fields))
		{
			$filter['GROUP_ID'] = array(
				'id' => 'GROUP_ID',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_GROUP'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'group',
					'DATA' => array(
						'ID' => 'group',
						'FIELD_ID' => 'GROUP_ID'
					)
				)
			);
		}

		if (in_array('PROBLEM', $fields))
		{
			$filter['PROBLEM'] = array(
				'id' => 'PROBLEM',
				'name' => Loc::getMessage('TASKS_FILTER_PROBLEM'),
				'type' => 'list',
				'items' => $this->getAllowedTaskCategories()
			);
		}

		if (in_array('PARAMS', $fields))
		{
			$filter['PARAMS'] = array(
				'id' => 'PARAMS',
				'name' => Loc::getMessage('TASKS_FILTER_PARAMS'),
				'type' => 'list',
				'params' => array(
					'multiple' => 'Y'
				),
				'items' => array(
					'MARKED' => Loc::getMessage('TASKS_FILTER_PARAMS_MARKED'),
					'IN_REPORT' => Loc::getMessage('TASKS_FILTER_PARAMS_IN_REPORT'),
					'OVERDUED' => Loc::getMessage('TASKS_FILTER_PARAMS_OVERDUED'),
					//					'SUBORDINATE'=>Loc::getMessage('TASKS_FILTER_PARAMS_SUBORDINATE'),
					'FAVORITE' => Loc::getMessage('TASKS_FILTER_PARAMS_FAVORITE'),
					'ANY_TASK' => Loc::getMessage('TASKS_FILTER_PARAMS_ANY_TASK')
				)
			);
		}

		if (in_array('ID', $fields))
		{
			$filter['ID'] = array(
				'id' => 'ID',
				'name' => Loc::getMessage('TASKS_FILTER_ID'),
				'type' => 'number'
			);
		}
		if (in_array('TITLE', $fields))
		{
			$filter['TITLE'] = array(
				'id' => 'TITLE',
				'name' => Loc::getMessage('TASKS_FILTER_TITLE'),
				'type' => 'string'
			);
		}
		if (in_array('PRIORITY', $fields))
		{
			$filter['PRIORITY'] = array(
				'id' => 'PRIORITY',
				'name' => Loc::getMessage('TASKS_PRIORITY'),
				'type' => 'list',
				'items' => array(
					1 => Loc::getMessage('TASKS_PRIORITY_1'),
					2 => Loc::getMessage('TASKS_PRIORITY_2'),
				)
			);
		}
		if (in_array('MARK', $fields))
		{
			$filter['MARK'] = array(
				'id' => 'MARK',
				'name' => Loc::getMessage('TASKS_FILTER_MARK'),
				'type' => 'list',
				'items' => array(
					'P' => Loc::getMessage('TASKS_MARK_P'),
					'N' => Loc::getMessage('TASKS_MARK_N')
				)
			);
		}
		if (in_array('ALLOW_TIME_TRACKING', $fields))
		{
			$filter['ALLOW_TIME_TRACKING'] = array(
				'id' => 'ALLOW_TIME_TRACKING',
				'name' => Loc::getMessage('TASKS_FILTER_ALLOW_TIME_TRACKING'),
				'type' => 'list',
				'items' => array(
					'Y' => Loc::getMessage('TASKS_ALLOW_TIME_TRACKING_Y'),
					'N' => Loc::getMessage('TASKS_ALLOW_TIME_TRACKING_N'),
				)
			);
		}
		if (in_array('CREATED_DATE', $fields))
		{
			$filter['CREATED_DATE'] = array(
				'id' => 'CREATED_DATE',
				'name' => Loc::getMessage('TASKS_FILTER_CREATED_DATE'),
				'type' => 'date'
			);
		}
		if (in_array('CLOSED_DATE', $fields))
		{
			$filter['CLOSED_DATE'] = array(
				'id' => 'CLOSED_DATE',
				'name' => Loc::getMessage('TASKS_FILTER_CLOSED_DATE'),
				'type' => 'date'
			);
		}
		if (in_array('DATE_START', $fields))
		{
			$filter['DATE_START'] = array(
				'id' => 'DATE_START',
				'name' => Loc::getMessage('TASKS_FILTER_DATE_START'),
				'type' => 'date'
			);
		}
		if (in_array('START_DATE_PLAN', $fields))
		{
			$filter['START_DATE_PLAN'] = array(
				'id' => 'START_DATE_PLAN',
				'name' => Loc::getMessage('TASKS_FILTER_START_DATE_PLAN'),
				'type' => 'date'
			);
		}
		if (in_array('END_DATE_PLAN', $fields))
		{
			$filter['END_DATE_PLAN'] = array(
				'id' => 'END_DATE_PLAN',
				'name' => Loc::getMessage('TASKS_FILTER_END_DATE_PLAN'),
				'type' => 'date'
			);
		}

		if (in_array('ACTIVE', $fields))
		{
			$filter['ACTIVE'] = array(
				'id' => 'ACTIVE',
				'name' => Loc::getMessage('TASKS_FILTER_ACTIVE'),
				'type' => 'date'
			);
		}

		if (in_array('ACCOMPLICE', $fields))
		{
			$filter['ACCOMPLICE'] = array(
				'id' => 'ACCOMPLICE',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_ACCOMPLICES'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'ACCOMPLICE'
					)
				)
			);
		}
		if (in_array('AUDITOR', $fields))
		{
			$filter['AUDITOR'] = array(
				'id' => 'AUDITOR',
				'name' => Loc::getMessage('TASKS_HELPER_FLT_AUDITOR'),
				'params' => array('multiple' => 'Y'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array(
						'ID' => 'user',
						'FIELD_ID' => 'AUDITOR'
					)
				)
			);
		}

		if (in_array('TAG', $fields))
		{
			$filter['TAG'] = array(
				'id' => 'TAG',
				'name' => Loc::getMessage('TASKS_FILTER_TAG'),
				'type' => 'string'
			);
		}

		if (in_array('ROLEID', $fields))
		{
			$items = array();
			foreach (Counter\Role::getRoles() as $roleCode => $roleName)
			{
				$items[$roleCode] = $roleName['TITLE'];
			}
			$filter['ROLEID'] = array(
				'id' => 'ROLEID',
				'name' => Loc::getMessage('TASKS_FILTER_ROLEID'),
				'type' => 'list',
				'default' => true,
				'items' => $items
			);
		}

		return $filter;
	}

	/**
	 * Get available fields in filter.
	 * @return array
	 */
	public function getAvailableFields()
	{
		$fields = array(
			'ID',
			'TITLE',
			'STATUS',
			'PROBLEM',
			'PARAMS',
			'PRIORITY',
			'MARK',
			'ALLOW_TIME_TRACKING',
			'DEADLINE',
			'CREATED_DATE',
			'CLOSED_DATE',
			'DATE_START',
			'START_DATE_PLAN',
			'END_DATE_PLAN',
			'RESPONSIBLE_ID',
			'CREATED_BY',
			'ACCOMPLICE',
			'AUDITOR',
			'TAG',
			'ACTIVE',
			'ROLEID',
		);

		if ($this->getGroupId() == 0)
		{
			$fields[] = 'GROUP_ID';
		}

		return $fields;
	}

	/**
	 * @return array
	 */
	private function getAllowedTaskCategories()
	{
		$list = array();

		$taskCategories = array(
			\CTaskListState::VIEW_TASK_CATEGORY_WO_DEADLINE,
			\CTaskListState::VIEW_TASK_CATEGORY_NEW,
			\CTaskListState::VIEW_TASK_CATEGORY_EXPIRED_CANDIDATES,
			\CTaskListState::VIEW_TASK_CATEGORY_EXPIRED,
			\CTaskListState::VIEW_TASK_CATEGORY_WAIT_CTRL,
			\CTaskListState::VIEW_TASK_CATEGORY_DEFERRED
		);

		foreach ($taskCategories as $categoryId)
		{
			$list[$categoryId] = \CTaskListState::getTaskCategoryName($categoryId);
		}

		return $list;
	}

	private function isFilterEmpty()
	{
		return !$this->getFilterFieldData('FILTER_APPLIED', false);
	}

	private function getFilterFieldData($field, $default = null)
	{
		$filterData = $this->getFilterData();

		return array_key_exists($field, $filterData) ? $filterData[$field] : $default;
	}

	/**
	 * @return array
	 */
	private function getFilterData()
	{
		$filters = $this->getFilters();
		$filterOptions = $this->getOptions();

		return $filterOptions->getFilter($filters);
	}

	private function getDateFilterFieldData($row)
	{
		$arrFilter = array();

		if ($row['id'] == 'ACTIVE' && !empty($filterData[$row['ACTIVE']]))
		{
			$arrFilter['START'] = $filterData[$row['id'].'_from'];
			$arrFilter['END'] = $filterData[$row['id'].'_to'];
		}

		if ($this->getFilterFieldData($row['id'].'_from'))
		{
			$arrFilter['>='.$row['id']] = $this->getFilterFieldData($row['id'].'_from');
		}

		if ($this->getFilterFieldData($row['id'].'_to'))
		{
			$arrFilter['<='.$row['id']] = $this->getFilterFieldData($row['id'].'_to');
		}

		return $arrFilter;
	}

	private function getNumberFilterFieldData($row)
	{
		$arrFilter = array();

		if ($this->getFilterFieldData($row['id'].'_from'))
		{
			$arrFilter['>='.$row['id']] = $this->getFilterFieldData($row['id'].'_from');
		}
		if ($this->getFilterFieldData($row['id'].'_to'))
		{
			$arrFilter['<='.$row['id']] = $this->getFilterFieldData($row['id'].'_to');
		}

		if (array_key_exists('>='.$row['id'], $arrFilter) &&
			array_key_exists('<='.$row['id'], $arrFilter) &&
			$arrFilter['>='.$row['id']] == $arrFilter['<='.$row['id']])
		{
			$arrFilter[$row['id']] = $arrFilter['>='.$row['id']];
			unset($arrFilter['>='.$row['id']], $arrFilter['<='.$row['id']]);
		}

		return $arrFilter;
	}

	private function getListFilterFieldData($row)
	{
		$arrFilter = array();
		$field = $this->getFilterFieldData($row['id'], array());

		switch ($row['id'])
		{
			case 'PARAMS':
				$subfilter = array();
				foreach ($field as $item)
				{
					switch ($item)
					{
						case 'FAVORITE':
							$subfilter["FAVORITE"] = 'Y';
							break;
						case 'MARKED':
							$subfilter["!MARK"] = false;
							break;
						case 'OVERDUED':
							$subfilter["OVERDUED"] = "Y";
							break;
						case 'IN_REPORT':
							$subfilter["ADD_IN_REPORT"] = "Y";
							break;
						case 'SUBORDINATE':
							// Don't set SUBORDINATE_TASKS for admin, it will cause all tasks to be showed
							if (!\Thurly\Tasks\Util\User::isSuper())
							{
								$subfilter["SUBORDINATE_TASKS"] = "Y";
							}
							break;
						case 'ANY_TASK':
							$arrFilter['::REMOVE-MEMBER'] = true; // hack
							break;
					}
				}
				if ($subfilter)
				{
					$arrFilter[$row['id']] = $subfilter;
				}
				break;
			case 'STATUS':
				$arrFilter['REAL_STATUS'] = $field; //TODO!!!
				break;
			case 'ROLEID':
				switch ($field)
				{
					default:
						if (!$this->getGroupId())
						{
							$arrFilter['MEMBER'] = $this->getUserId();
						}
						break;

					case 'view_role_responsible':
						$arrFilter['=RESPONSIBLE_ID'] = $this->getUserId();
						break;
					case 'view_role_accomplice':
						$arrFilter['=ACCOMPLICE'] = $this->getUserId();
						break;
					case 'view_role_auditor':
						$arrFilter['=AUDITOR'] = $this->getUserId();
						break;
					case 'view_role_originator':
						if (!$this->getGroupId())
						{
							$arrFilter['!REFERENCE:RESPONSIBLE_ID'] = 'CREATED_BY';
							$arrFilter['=CREATED_BY'] = $this->getUserId();
						}
						break;
				}
				break;
			case 'PROBLEM':
				$subfilter = array();
				$roleId = $this->getFilterFieldData('ROLEID');

				switch ($field)
				{
					default:
						break;
					case Counter\Type::TYPE_WO_DEADLINE:
						switch ($roleId)
						{
							case Counter\Role::RESPONSIBLE:
								$arrFilter['!CREATED_BY'] = $this->getUserId();
								break;
							case Counter\Role::ORIGINATOR:
								$arrFilter['!RESPONSIBLE_ID'] = $this->getUserId();
								break;
							default:
								$filter = array();

								if ($this->getGroupId() > 0)
								{
									$arrFilter['!REFERENCE:RESPONSIBLE_ID'] = 'CREATED_BY';
								}
								else
								{
									$filter['::LOGIC'] = 'OR';
									$filter['::SUBFILTER-R'] = array(
										'!CREATED_BY' => $this->getUserId(),
										'RESPONSIBLE_ID' => $this->getUserId()
									);
									$filter['::SUBFILTER-O'] = array(
										'CREATED_BY' => $this->getUserId(),
										'!RESPONSIBLE_ID' => $this->getUserId()
									);

									$arrFilter['::SUBFILTER-OR'] = $filter;
								}
								break;
						}

						$arrFilter['DEADLINE'] = '';
						break;
					case Counter\Type::TYPE_EXPIRED:
						$arrFilter['<=DEADLINE'] = Counter::getExpiredTime();
						break;
					case Counter\Type::TYPE_EXPIRED_CANDIDATES:
						$arrFilter['>=DEADLINE'] = Counter::getExpiredTime();
						$arrFilter['<=DEADLINE'] = Counter::getExpiredSoonTime();

						if (!$roleId && $this->getGroupId() == 0)
						{
							$filter = array();
							$filter['::LOGIC'] = 'OR';
							$filter['::SUBFILTER-R'] = array(
								'RESPONSIBLE_ID' => $this->getUserId()
							);
							$filter['::SUBFILTER-O'] = array(
								'ACCOMPLICE' => $this->getUserId()
							);

							$arrFilter['::SUBFILTER-OR'] = $filter;
						}
						break;
					case Counter\Type::TYPE_WAIT_CTRL:
						$arrFilter['REAL_STATUS'] = \CTasks::STATE_SUPPOSEDLY_COMPLETED;
						$arrFilter['!RESPONSIBLE_ID'] = $this->getUserId();
						$arrFilter['=CREATED_BY'] = $this->getUserId();
						break;
					case Counter\Type::TYPE_NEW:
						$arrFilter['VIEWED'] = 0;
						$arrFilter['VIEWED_BY'] = $this->getUserId();

						if (!$roleId)
						{
							$filter = array();
							$filter['::LOGIC'] = 'OR';
							$filter['::SUBFILTER-R'] = array(
								'RESPONSIBLE_ID' => $this->getUserId()
							);
							$filter['::SUBFILTER-O'] = array(
								'ACCOMPLICE' => $this->getUserId()
							);

							$arrFilter['::SUBFILTER-OR'] = $filter;
						}

						break;
					case Counter\Type::TYPE_DEFERRED:
						$arrFilter['REAL_STATUS'] = \CTasks::STATE_DEFERRED;
						break;
				}

				if ($subfilter)
				{
					$arrFilter[$row['id']] = $subfilter;
				}
				break;
		}

		return $arrFilter;
	}

	public function getDefaultPresetKey()
	{
		return $this->getOptions()->getDefaultFilterId();
	}
}