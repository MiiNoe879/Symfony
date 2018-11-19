<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 *
 *
 *
 *
 */

namespace Thurly\Tasks\Util\Replicator\Task;

use Thurly\Main\Localization\Loc;

use Thurly\Tasks\Internals\Task\CheckListTable;
use Thurly\Tasks\Internals\Task\MemberTable;
use Thurly\Tasks\Internals\Task\RelatedTable;
use Thurly\Tasks\Internals\Task\TagTable;
use Thurly\Tasks\Internals\TaskTable;
use Thurly\Tasks\Item\Result;
use Thurly\Tasks\Item\Task;
use Thurly\Tasks\Internals\RunTime;
use Thurly\Tasks\Util\User;
use Thurly\Tasks\Util\Collection;

Loc::loadMessages(__FILE__);

class FromTask extends \Thurly\Tasks\Util\Replicator\Task
{
	protected static function getSourceClass()
	{
		return '\\Thurly\\Tasks\\Item\\Task';
	}

	protected static function getConverterClass()
	{
		return '\\Thurly\\Tasks\\Item\\Converter\\Task\\ToTask';
	}

	/**
	 * Note: multitasking is not implemented here
	 *
	 * @param int|mixed[]|\Thurly\Tasks\Item\Task $source
	 * @param int|mixed[]|\Thurly\Tasks\Item\Task $destination
	 * @param array $parameters
	 * @param int $userId
	 * @return Result
	 */
	public function produceSub($source, $destination, array $parameters = array(), $userId = 0)
	{
		$result = new Result();

		Task::enterBatchState();

		if(!$userId)
		{
			$userId = User::getId();
		}

		$source = $this->getSourceInstance($source, $userId);
		$destination = $this->getDestinationInstance($destination, $userId);
		$created = new Collection();

		$data = $this->getSubItemData($source->getId(), $result, $userId);
		if($result->isSuccess() && !empty($data)) // has sub-tasks
		{
			$srcToDst = array($source->getId() => $destination->getId());
			$notSaved = array();

			foreach($data['TREE'] as $childId => $parentId)
			{
				if(!$parameters['COPY_DESTINATION_ITEM'] && $childId == $destination->getId()) // do not copy self one more time as a sub-task
				{
					continue;
				}

				if(isset($notSaved[$parentId])) // if parent was not saved, child can not be saved too
				{
					$notSaved[$childId] = true;
					continue;
				}

				$saveResult = $this->saveItemFromSource($data['DATA'][$childId], array(
					'PARENT_ID' => $srcToDst[$parentId]
				));

				if($saveResult->isSuccess())
				{
					$srcToDst[$childId] = $saveResult->getInstance()->getId();
				}
				else
				{
					$notSaved[$childId] = true;
				}

				$created->push($saveResult);
			}

			if(!empty($notSaved))
			{
				$result->addError('SUB_ITEMS_CREATION_FAILURE', 'Some of the sub-tasks was not properly created');
			}
		}

		Task::leaveBatchState();

		$result->setData($created);

		return $result;
	}

	private function getSubItemData($id, Result $result, $userId = 0)
	{
		$queue = array($id);
		$met = array();
		$times = 0;

		$data = array();
		$flatTree = array();

		$select = array_keys(\Thurly\Tasks\Util\UserField\Task::getScheme(0, $userId));
		$select[] = '*';

		$parameters = Runtime::apply(
			array(
				'filter' => array(
					'=PARENT_ID' => $id,
					'=ZOMBIE' => 'N', // todo: remove ZOMBIE mechanism, it is nasty
				),
				'select' => $select,
			),
			array(RunTime\Task::getAccessCheck(array( // have to check rights...
				'USER_ID' => $userId
			)))
		);

		// todo: use \Thurly\Tasks\Internals\Helper\Task\Dependence here, instead of recursive calls
		// todo: or you can use \Thurly\Tasks\Item\Task::find() with an appropriate runtime mixin applied on top
		while(!empty($queue))
		{
			if($times > 10000)
			{
				$result->addError('ILLEGAL_STRUCTURE.DEPTH', 'Insane iteration count faced');
				break;
			}

			$nextId = array_shift($queue);
			if($met[$nextId])
			{
				$result->addError('ILLEGAL_STRUCTURE.LOOP', Loc::getMessage('TASKS_REPLICATOR_SUBTREE_LOOP'));
				break;
			}
			$met[$nextId] = true;

			$parameters['filter']['=PARENT_ID'] = $nextId;

			$res = TaskTable::getList(Runtime::cloneFields($parameters));
			while($item = $res->fetch())
			{
				$data[$item['ID']] = $item;
				$flatTree[$item['ID']] = $nextId;

				$queue[] = $item['ID'];
			}

			$times++;
		}

		if($result->isSuccess())
		{
			$ids = array_keys($data);

			// get checklist data
			$res = CheckListTable::getList(array('filter' => array('=TASK_ID' => $ids)));
			while($item = $res->fetch())
			{
				if(array_key_exists($item['TASK_ID'], $data))
				{
					$data[$item['TASK_ID']]['SE_CHECKLIST'][] = $item;
				}
			}

			// get member data
			$res = MemberTable::getList(array('filter' => array('=TASK_ID' => $ids, '=TYPE' => array('A', 'U'))));
			while($item = $res->fetch())
			{
				if(array_key_exists($item['TASK_ID'], $data))
				{
					$data[$item['TASK_ID']][$item['TYPE'] == 'A' ? 'ACCOMPLICES' : 'AUDITORS'][] = $item['USER_ID'];
				}
			}

			// get tag data
			$res = TagTable::getList(array('filter' => array('=TASK_ID' => $ids)));
			while($item = $res->fetch())
			{
				if(array_key_exists($item['TASK_ID'], $data))
				{
					$data[$item['TASK_ID']]['TAGS'][] = $item['NAME'];
				}
			}

			// get depends on data
			$res = RelatedTable::getList(array('filter' => array('=TASK_ID' => $ids)));
			while($item = $res->fetch())
			{
				if(array_key_exists($item['TASK_ID'], $data))
				{
					$data[$item['TASK_ID']]['DEPENDS_ON'][] = $item['DEPENDS_ON_ID'];
				}
			}
		}

		return array(
			'DATA' => $data,
			'TREE' => $flatTree,
		);
	}
}