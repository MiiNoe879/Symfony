<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Thurly\Main\Localization\Loc;
use \Thurly\Main\Type\DateTime;

use \Thurly\Tasks\Ui\Filter;
use \Thurly\Tasks\Internals\Task\SortingTable;

\CThurlyComponent::includeComponentClass('thurly:tasks.kanban');

class TasksTimelineComponent extends TasksKanbanComponent
{
	/**
	 * Get stages for Timeline.
	 * @return array
	 */
	protected function getStages()
	{
		static $stages = null;

		if ($stages !== null)
		{
			return $stages;
		}

		$ltDeadline = array(
			'::LOGIC' => 'OR',
			'DEADLINE' => false,
			'<REFERENCE:START_DATE_PLAN' => 'DEADLINE'
		);

		$dateVal = array(
			'G' => date('G', time() + $this->timeOffset),
			'i' => date('i', time() + $this->timeOffset),
			's' => date('s', time() + $this->timeOffset)
		);

		// first border of +10 days
		$date = new DateTime;
		$date10D1 = $date->add('+10 days')
						->add('-' . $dateVal['G'] . ' hours')
						->add('- ' . $dateVal['i'] . ' minutes')
						->add('- ' . $dateVal['s'] . ' seconds');


		// first border of +7 days
		$date = new DateTime;
		$date7D1 = $date->add('+7 days')
						->add('-' . $dateVal['G'] . ' hours')
						->add('- ' . $dateVal['i'] . ' minutes')
						->add('- ' . $dateVal['s'] . ' seconds');

		// first border of tomorrow
		$date = new DateTime;
		$date2D1 = $date->add('+1 days')
						->add('-' . $dateVal['G'] . ' hours')
						->add('- ' . $dateVal['i'] . ' minutes')
						->add('- ' . $dateVal['s'] . ' seconds');

		// second border of tomorrow
		$date = new DateTime;
		$date2D2 = $date->add('+1 days')
						->add('+' . (24 - $dateVal['G'] - 1) . ' hours')
						->add('+ ' . (60 - $dateVal['i'] - 1) . ' minutes')
						->add('+ ' . (60 - $dateVal['s'] - 1) . ' seconds');

		// first border of today
		$date = new DateTime;
		$date1D1 = $date->add('-' . $dateVal['G'] . ' hours')
						->add('- ' . $dateVal['i'] . ' minutes')
						->add('- ' . $dateVal['s'] . ' seconds');

		// second border of today
		$date = new DateTime;
		$date1D2 = $date->add('+' . (24 - $dateVal['G'] - 1) . ' hours')
						->add('+ ' . (60 - $dateVal['i'] - 1) . ' minutes')
						->add('+ ' . (60 - $dateVal['s'] - 1) . ' seconds');

		$stages = array(
			'FAR' => array(
				'color' => '468ee5',
				'filter' => array(
					array(
						'::LOGIC' => 'OR',
						array(
							'START_DATE_PLAN' => false,
							$ltDeadline
						),
						array(
							'>START_DATE_PLAN' => $date7D1,
							$ltDeadline
						),
						array(
							'START_DATE_PLAN' => false,
							'>DEADLINE' => $date7D1,
						)
					),
					'!REAL_STATUS' => array(
						\CTasks::STATE_COMPLETED,
						\CTasks::STATE_SUPPOSEDLY_COMPLETED
					)
				),
				'overdue_until' => $date10D1->getTimeStamp(),
				'update' => array(
					'START_DATE_PLAN' => $date10D1
				),
				'update_right' => \CTaskItem::ACTION_CHANGE_DEADLINE
			),
			'NEAR' => array(
				'color' => '00c4fb',
				'filter' => array(
					array(
						'::LOGIC' => 'OR',
						array(
							'<=START_DATE_PLAN' => $date7D1,
							'>START_DATE_PLAN' => $date2D2,
							$ltDeadline
						),
						array(
							'START_DATE_PLAN' => false,
							'<=DEADLINE' => $date7D1,
							'>DEADLINE' => $date2D2
						)
					),
					'!REAL_STATUS' => array(
						\CTasks::STATE_COMPLETED,
						\CTasks::STATE_SUPPOSEDLY_COMPLETED
					)
				),
				'overdue_until' => $date7D1->getTimeStamp(),
				'update' => array(
					'START_DATE_PLAN' => $date7D1
				),
				'update_right' => \CTaskItem::ACTION_CHANGE_DEADLINE
			),
			'TOMORROW' => array(
				'color' => '47d1e2',
				'filter' => array(
					array(
						'::LOGIC' => 'OR',
						array(
							'<=START_DATE_PLAN' => $date2D2,
							'>START_DATE_PLAN' => $date1D2,
							$ltDeadline
						),
						array(
							'START_DATE_PLAN' => false,
							'<=DEADLINE' => $date2D2,
							'>DEADLINE' => $date1D2
						)
					),
					'!REAL_STATUS' => array(
						\CTasks::STATE_COMPLETED,
						\CTasks::STATE_SUPPOSEDLY_COMPLETED
					)
				),
				'overdue_until' => $date2D1->getTimeStamp(),
				'update' => array(
					'START_DATE_PLAN' => $date2D1
				),
				'update_right' => \CTaskItem::ACTION_CHANGE_DEADLINE
			),
			'TODAY' => array(
				'color' => '1eae43',
				'filter' => array(
					array(
						'::LOGIC' => 'OR',
						array(
							'!START_DATE_PLAN' => false,
							'<=START_DATE_PLAN' => $date1D2,
							array(
								'::LOGIC' => 'OR',
								'DEADLINE' => false,
								array(
									'<REFERENCE:START_DATE_PLAN' => 'DEADLINE',
									'>=DEADLINE' => $date1D1,
								)
							)
						),
						array(
							'START_DATE_PLAN' => false,
							'<=DEADLINE' => $date1D2,
							'>=DEADLINE' => $date1D1
						)
					),
					'!REAL_STATUS' => array(
						\CTasks::STATE_COMPLETED,
						\CTasks::STATE_SUPPOSEDLY_COMPLETED
					)
				),
				'overdue_until' => $date1D1->getTimeStamp(),
				'update' => array(
					'START_DATE_PLAN' => $date1D1
				),
				'update_right' => \CTaskItem::ACTION_CHANGE_DEADLINE
			),
			'OVERDUE' => array(
				'color' => 'ff5752',
				'filter' => array(
					'<DEADLINE' => $date1D1,
					'!REAL_STATUS' => array(
						\CTasks::STATE_COMPLETED,
						\CTasks::STATE_SUPPOSEDLY_COMPLETED
					)
				),
				'extra' => array(
					'canAddItem' => false
				),
				'update' => array()
			),
			'COMPLETE' => array(
				'color' => '75d900',
				'filter' => array(
					'REAL_STATUS' => array(
						\CTasks::STATE_COMPLETED,
						\CTasks::STATE_SUPPOSEDLY_COMPLETED
					),
					'<=CLOSED_DATE' => $date1D2,
					'>=CLOSED_DATE' => $date1D1
				),
				'update' => array(
					'META:COMPLETE' => 'Y'
				),
				'extra' => array(
					'canAddItem' => false
				)
			)
		);

		return $stages;
	}

	/**
	 * Get __FILE__.
	 * @return string
	 */
	protected function getFile()
	{
		return __FILE__;
	}

	/**
	 * Get filter array.
	 * @return array
	 */
	protected function getFilter()
	{
		static $filling = false;

		if ($filling)
		{
			return $this->filter;
		}
		else
		{
			$filling = true;
		}

		$params =& $this->arParams;
		$filter =& $this->filter;

		if ($this->taskType == static::TASK_TYPE_GROUP)
		{
			Filter\Task::setGroupId($params['GROUP_ID']);
		}
		else
		{
			Filter\Task::setUserId($params['USER_ID']);
		}

		$uiFilter = Filter\Task::processFilter();
		$filter = array_merge($filter, $uiFilter);

		// by default
		if (!array_key_exists('CHECK_PERMISSIONS', $filter))
		{
			$filter['CHECK_PERMISSIONS'] = 'Y';
		}
		if (!array_key_exists('ZOMBIE', $filter))
		{
			$filter['ZOMBIE'] = 'N';
		}
		if ($this->taskType == static::TASK_TYPE_GROUP)
		{
			$filter['GROUP_ID'] = $params['GROUP_ID'];
		}
		$filter['ONLY_ROOT_TASKS'] = 'N';

		// timeline always in work ?
		/*$filter['REAL_STATUS'] = array(
			\CTasks::STATE_NEW,
			\CTasks::STATE_PENDING,
			\CTasks::STATE_IN_PROGRESS
		);*/

		return $filter;
	}

	/**
	 * Base method for getting columns.
	 * @param bool $assoc Return as associative.
	 * @return array
	 */
	protected function getColumns($assoc = false)
	{
		$columns = array();
		$counts = array();
		$select = $this->getSelect();
		$order = $this->getOrder();
		$filter = $this->getFilter();

		$sort = 1;
		foreach ($this->getStages() as $code => $stage)
		{
			if (isset($stage['filter']))
			{
				/*if (
					isset($stage['filter']['REAL_STATUS']) &&
					isset($filter['REAL_STATUS'])
				)
				{
					unset($stage['filter']['REAL_STATUS']);
				}*/
			}
			else
			{
				$stage['filter'] = array();
			}
			// need to optimize may be
			$rows = $this->getList(array(
				'select' => $select,
				'filter' => array_merge($filter, $stage['filter']),
				'order' => $order
			));
			$columns[$code] = array(
				'id' => $code,
				'name' => Loc::getMessage('TASK_LIST_TASK_TL_STAGE_' . $code),
				'color' => isset($stage['color']) ? $stage['color'] : '',
				'sort' => $sort++,
				'total' => count($rows),
				'data' => array(
					'overdue_until' => isset($stage['overdue_until']) ? $stage['overdue_until'] : 0
				)
			);
			if (isset($stage['extra']) && is_array($stage['extra']))
			{
				$columns[$code] += $stage['extra'];
			}
		}

		$columns = $this->sendEvent('TimelineComponentGetColumns', $columns);

		return $assoc ? $columns : array_values($columns);
	}

	/**
	 * Get one task item.
	 * @param int $id Item id.
	 * @param string|int $columnId Code of stage (if know).
	 * @param bool $bCheckPermission Check permissions.
	 * @return array
	 */
	protected function getRawData($taskId, $columnId = false, $bCheckPermission = true)
	{
		if ($columnId === null)
		{
			$columnId = false;
		}
		$row = $this->getList(array(
			'select' => $this->getSelect(),
			'filter' => array(
				'ID' => $taskId,
				'CHECK_PERMISSIONS' => $bCheckPermission ? 'Y' : 'N'
			),
			'order' => $this->getOrder()
		));
		if (!empty($row))
		{
			$row = array_pop($row);
			if (($task = $this->fillData($row)))
			{
				$task = array(
					$task['id'] => array(
						'id' => $task['id'],
						'columnId' => $columnId,
						'data' => $task
					)
				);

				// we don't know in which column this item exist
				foreach ($this->getStages() as $code => $column)
				{
					if (!isset($column['filter']))
					{
						$column['filter'] = array();
					}
					$column['filter']['ID'] = $taskId;

					$row = $this->getList(array(
						'select' => array('ID'),
						'filter' => $column['filter'],
						'order' => $this->getOrder()
					));

					if ($row)
					{
						$task[$taskId]['columnId'] = $code;
						// change sort, if changed stage
						if ($code != $columnId)
						{
							// and get before id
							unset($column['filter']['ID']);
							$rows = $this->getList(array(
								'select' => array('ID'),
								'filter' => array_merge($this->getFilter(), $column['filter']),
								'order' => $this->getOrder()
							));
							foreach ($rows as $row)
							{
								if ($row->getId() != $taskId)
								{
									$task[$taskId]['targetId'] = $row->getId();
									$this->setSorting($taskId, $row->getId());
									break;
								}
							}
							break;
						}
					}
				}

				// get other data
				$task = $this->getUsers($task);
				$task = $this->getNewLog($task);
				$task = $this->getFiles($task);
				$task = $this->getCheckList($task);

				$task = $this->sendEvent('TimelineComponentGetItems', $task);

				return array_pop($task);
			}
		}

		return array();
	}

	/**
	 * Base method for getting data.
	 * @return array
	 */
	protected function getData()
	{
		$items = array();
		$order = $this->getOrder();
		$filter = $this->getFilter();
		$listParams = $this->getListParams();
		$select = $this->getSelect();

		// get tasks by stages
		foreach ($this->getStages() as $code => $column)
		{
			if (isset($column['filter']))
			{
				$filterTmp = array_merge(
					$filter,
					$column['filter']
				);
			}
			else
			{
				$filterTmp = $filter;
			}

			$rows = $this->getList(array(
				'select' => $select,
				'filter' => $filterTmp,
				'order' => $order,
				'navigate' => $listParams
			));
			foreach ($rows as $row)
			{
				$item = $this->fillData($row);
				if ($this->isDebug() && isset($items[$item['id']]))
				{
					echo $item['id'].'#'.$items[$item['id']]['columnId'].'#'.$code.'<br/>';
				}
				$items[$item['id']] = array(
					'id' => $item['id'],
					'columnId' => $code,
					'data' => $item
				);
				if (
					isset($filterTmp['ID']) &&
					$filterTmp['ID'] == $item['ID']
				)
				{
					break 2;
				}
			}
		}

		// get other data
		$items = $this->getUsers($items);
		$items = $this->getNewLog($items);
		$items = $this->getFiles($items);
		$items = $this->getCheckList($items);

		$items = $this->sendEvent('TimelineComponentGetItems', $items);

		return array_values($items);
	}

	/**
	 * Check views for current Kanban. Ask admin for create default.
	 * @return boolean
	 */
	protected function checkViews()
	{
		// we don't need int stages
		return true;
	}

	/**
	 * Move item from one stage to another.
	 * @return array
	 */
	protected function actionMoveTask()
	{
		if (
			($taskId = $this->request('itemId')) &&
			($columnId = $this->request('columnId'))
		)
		{
			$stages = $this->getStages();
			if (isset($stages[$columnId]))
			{
				if (isset($stages[$columnId]['update']))
				{
					// if column is meta-stage
					$update = $stages[$columnId]['update'];
					if (isset($update['META:COMPLETE']))
					{
						$task = \CTaskItem::getInstance($taskId, $this->userId);
						if ($task->isActionAllowed(\CTaskItem::ACTION_COMPLETE))
						{
							$task->complete();
						}
						unset($update['META:COMPLETE']);
						if (($e = $this->application->GetException()))
						{
							$this->addError($e->getString());
							return array();
						}
					}
					// else update
					if (!empty($update))
					{
						$task = \CTaskItem::getInstance($taskId, $this->userId);
						if (isset($stages[$columnId]['update_right']))
						{
							if (!$task->isActionAllowed($stages[$columnId]['update_right']))
							{
								// set sorting and exit
								$this->setSorting(
									$taskId,
									$this->request('beforeItemId'),
									$this->request('afterItemId')
								);
								return array();
							}
						}
						// if column id is equal
						$oldData = $this->getRawData($taskId, $columnId);
						if ($oldData['columnId'] == $columnId)
						{
							// set sorting and exit
							$this->setSorting(
								$taskId,
								$this->request('beforeItemId'),
								$this->request('afterItemId')
							);
							return array();
						}
						// update, check errors
						$task->update($update);
						if (($e = $this->application->GetException()))
						{
							$this->addError($e->getString());
							return array();
						}
						// correct deadline
						$fields = $task->getData();
						if ($fields['DEADLINE'] != '')
						{
							$fields['DEADLINE'] = new DateTime($fields['DEADLINE']);
							$fields['START_DATE_PLAN'] = new DateTime($fields['START_DATE_PLAN']);
							if ($fields['START_DATE_PLAN']->getTimestamp() >= $fields['DEADLINE']->getTimestamp())
							{
								$fields['START_DATE_PLAN'] = new DateTime($fields['DEADLINE']);
								$fields['START_DATE_PLAN']->add('-1 hour');
								$task->update(array(
									'START_DATE_PLAN' => $fields['START_DATE_PLAN']
								));
								if (($e = $this->application->GetException()))
								{
									$this->addError($e->getString());
									return array();
								}
							}
						}
					}
					// set sorting
					$this->setSorting(
						$taskId,
						$this->request('beforeItemId'),
						$this->request('afterItemId')
					);
					// output
					return $this->getRawData($taskId, $columnId);
				}
				else
				{
					$this->addError('TASK_LIST_UNKNOWN_ACTION');
				}
			}
		}

		return array();
	}

	/**
	 * Add new task.
	 * @return array
	 */
	protected function actionAddTask()
	{
		if (
			($columnId = $this->request('columnId')) &&
			($taskName = $this->request('taskName'))
		)
		{
			if (!$this->canCreateTasks())
			{
				$this->addError('TASK_LIST_TASK_CREATE_DENIED');
				return array();
			}

			$stages = $this->getStages();
			$fields = array(
				'TITLE' => $this->convertUtf($taskName, true),
				'CREATED_BY' => $this->userId,
				'RESPONSIBLE_ID' => $this->arParams['USER_ID'],
				'GROUP_ID' => $this->arParams['GROUP_ID']
			);
			if (
				isset($stages[$columnId]['update']) &&
				is_array($stages[$columnId]['update']) &&
				(isset($stages[$columnId]['extra']['canAddItem']) &&
				$stages[$columnId]['extra']['canAddItem'] ||
				!isset($stages[$columnId]['extra']['canAddItem']))
			)
			{
				$fields = array_merge($fields, $stages[$columnId]['update']);
			}
			$task = \CTaskItem::add($fields, $this->userId);
			if ($task->getId() > 0)
			{
				$newId = $task->getId();
				// set sort
				$this->setSorting(
					$newId,
					$this->request('beforeItemId'),
					$this->request('afterItemId')
				);
				// output
				return $this->getRawData($newId, $columnId);
			}
			else
			{
				if (($e = $this->application->GetException()))
				{
					$this->addError($e->getString());
				}
			}
		}

		return array();
	}

	/**
	 * Modify stage in Kanban.
	 * @return array
	 */
	protected function actionModifyColumn()
	{
		//disable
	}

	/**
	 * Move column.
	 * @return array
	 */
	protected function actionMoveColumn()
	{
		//disable
	}
}