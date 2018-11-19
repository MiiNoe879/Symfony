<?php

namespace Thurly\Disk\Volume;

use Thurly\Main\Entity;
use Thurly\Disk\Volume;
use Thurly\Disk\Internals\Error\Error;
use Thurly\Disk\Internals\Error\ErrorCollection;
use Thurly\Disk\Internals\Error\IErrorable;


/**
 * Disk cleanlier class.
 * @package Thurly\Disk\Volume
 */
class Cleaner implements IErrorable, Volume\IVolumeTimeLimit
{
	/** @var ErrorCollection */
	private $errorCollection;

	/** @var Volume\Timer */
	private $timer;

	/** @var Volume\Task */
	private $task;

	/** @var int Owner id */
	private $ownerId;

	/** @var \Thurly\Disk\User */
	private $owner;

	// interval agent start
	const AGENT_INTERVAL = 10;

	// fix every n interaction
	const STATUS_FIX_INTERVAL = 20;

	// limit maximum number selected files
	const MAX_FILE_PER_INTERACTION = 1000;

	// limit maximum number selected folders
	const MAX_FOLDER_PER_INTERACTION = 1000;


	/**
	 * @param int $ownerId Whom will mark as deleted by.
	 */
	public function __construct($ownerId = \Thurly\Disk\SystemUser::SYSTEM_USER_ID)
	{
		$this->ownerId = $ownerId;
	}


	/**
	 * Gets task.
	 * @return Volume\Task
	 */
	public function instanceTask()
	{
		if (!($this->task instanceof Volume\Task))
		{
			$this->task = new Volume\Task();
		}

		return $this->task;
	}

	/**
	 * Loads task.
	 * @param int $filterId Id of saved indicator result from b_disk_volume.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return boolean
	 */
	public function loadTask($filterId, $ownerId = \Thurly\Disk\SystemUser::SYSTEM_USER_ID)
	{
		$this->instanceTask();
		if ($filterId > 0)
		{
			if(!$this->instanceTask()->loadTaskById($filterId, ($ownerId > 0 ? $ownerId : $this->ownerId)))
			{
				$this->collectError(new Error('Cleaner task not found', 'CLEANER_TASK_NOT_FOUND'));
				return false;
			}
		}

		if ($this->instanceTask()->getOwnerId() > 0)
		{
			$this->ownerId = $this->instanceTask()->getOwnerId();
		}

		return ($this->instanceTask()->getId() > 0);
	}


	/**
	 * Returns the fully qualified name of this class.
	 * @return string
	 */
	public static function className()
	{
		return get_called_class();
	}


	/**
	 * Returns agent's name.
	 * @param int|string $filterId Id of saved indicator result from b_disk_volume.
	 * @return string
	 */
	public static function agentName($filterId)
	{
		return static::className()."::runWorker({$filterId});";
	}

	/**
	 * Determines if a script is loaded via cron/command line.
	 * @return bool
	 */
	public static function isCronRun()
	{
		$isCronRun = false;
		if (
			!\Thurly\Main\ModuleManager::isModuleInstalled('thurlyos') &&
			(php_sapi_name() === 'cli')
		)
		{
			$isCronRun = true;
		}

		return $isCronRun;
	}


	/**
	 * Runs clean process.
	 * @param int $filterId Id of saved indicator result from b_disk_volume.
	 * @return string
	 */
	public static function runWorker($filterId)
	{
		// only one interaction per hit
		if (self::isCronRun() === false)
		{
			if (defined(__NAMESPACE__ . '\\CLEANER_RUN_WORKER_LOCK'))
			{
				// do nothing, repeat
				return static::agentName($filterId);
			}
		}

		$cleaner = new static();
		if ($cleaner->loadTask($filterId) === false)
		{
			return '';// task not found
		}

		if (Volume\Task::isRunningMode($cleaner->instanceTask()->getStatus()) === false)
		{
			return '';// non running state
		}

		$cleaner->startTimer();

		$indicator = $cleaner->instanceTask()->getIndicator();
		if (!$indicator instanceof Volume\IVolumeIndicator)
		{
			return '';
		}

		if (!defined(__NAMESPACE__ . '\\CLEANER_RUN_WORKER_LOCK'))
		{
			define(__NAMESPACE__ . '\\CLEANER_RUN_WORKER_LOCK', true);
		}

		if ($cleaner->instanceTask()->getStatus() != Volume\Task::TASK_STATUS_RUNNING)
		{
			$cleaner->instanceTask()->setStatus(Volume\Task::TASK_STATUS_RUNNING);
		}

		// subTask to run
		$subTask = '';
		if (Volume\Task::isRunningMode($cleaner->instanceTask()->getStatusSubTask(Volume\Task::DROP_TRASHCAN)))
		{
			$subTask = Volume\Task::DROP_TRASHCAN;
		}
		elseif (Volume\Task::isRunningMode($cleaner->instanceTask()->getStatusSubTask(Volume\Task::EMPTY_FOLDER)))
		{
			$subTask = Volume\Task::EMPTY_FOLDER;
		}
		elseif (Volume\Task::isRunningMode($cleaner->instanceTask()->getStatusSubTask(Volume\Task::DROP_FOLDER)))
		{
			$subTask = Volume\Task::DROP_FOLDER;
		}
		elseif (Volume\Task::isRunningMode($cleaner->instanceTask()->getStatusSubTask(Volume\Task::DROP_UNNECESSARY_VERSION)))
		{
			$subTask = Volume\Task::DROP_UNNECESSARY_VERSION;
		}

		$repeatMeasure = function () use ($cleaner, $indicator)
		{
			// reset offset
			$cleaner->instanceTask()->setLastFileId(0);
			$cleaner->instanceTask()->fixState();

			// check final result repeat measure
			\Thurly\Disk\Volume\Cleaner::repeatMeasure($indicator);

			// reload task
			$cleaner->instanceTask()->loadTaskById($indicator->getFilterId(), $cleaner->instanceTask()->getOwnerId());
		};

		// run subTask
		$taskDone = false;
		switch ($subTask)
		{
			case Volume\Task::DROP_TRASHCAN:
			{
				if ($cleaner->instanceTask()->getStatusSubTask($subTask) != Volume\Task::TASK_STATUS_RUNNING)
				{
					$cleaner->instanceTask()->setStatusSubTask($subTask, Volume\Task::TASK_STATUS_RUNNING);
				}

				if($cleaner->deleteTrashcanByFilter($indicator))
				{
					$repeatMeasure();
					$taskDone = $cleaner->instanceTask()->hasTaskFinished($subTask);
				}
				elseif ($cleaner->instanceTask()->hasFatalError())
				{
					$taskDone = true;
				}

				break;
			}

			case Volume\Task::EMPTY_FOLDER:
			case Volume\Task::DROP_FOLDER:
			{
				if ($cleaner->instanceTask()->getStatusSubTask($subTask) != Volume\Task::TASK_STATUS_RUNNING)
				{
					$cleaner->instanceTask()->setStatusSubTask($subTask, Volume\Task::TASK_STATUS_RUNNING);
				}

				$folderId = $cleaner->instanceTask()->getParam('FOLDER_ID');
				$folder = \Thurly\Disk\Folder::getById($folderId);
				if ($folder instanceof \Thurly\Disk\Folder)
				{
					if ($cleaner->deleteFolder($folder, ($subTask === Volume\Task::EMPTY_FOLDER)))
					{
						$repeatMeasure();
						$taskDone = $cleaner->instanceTask()->hasTaskFinished($subTask);
					}
					elseif ($cleaner->instanceTask()->hasFatalError())
					{
						$taskDone = true;
					}
				}

				break;
			}

			case Volume\Task::DROP_UNNECESSARY_VERSION:
			{
				if ($cleaner->instanceTask()->getStatusSubTask($subTask) != Volume\Task::TASK_STATUS_RUNNING)
				{
					$cleaner->instanceTask()->setStatusSubTask($subTask, Volume\Task::TASK_STATUS_RUNNING);
				}

				if($cleaner->deleteUnnecessaryVersionByFilter($indicator))
				{
					$repeatMeasure();
					$taskDone = $cleaner->instanceTask()->hasTaskFinished($subTask);
				}
				elseif ($cleaner->instanceTask()->hasFatalError())
				{
					$taskDone = true;
				}

				break;
			}

			default:
			{
				$taskDone = true;
			}
		}

		if($taskDone)
		{
			// finish
			$cleaner->instanceTask()->setStatusSubTask($subTask, Volume\Task::TASK_STATUS_DONE);
			$cleaner->instanceTask()->setStatus(Volume\Task::TASK_STATUS_DONE);
		}

		// Fix task state
		$cleaner->instanceTask()->fixState();

		// count statistic for progress bar
		self::countWorker($cleaner->instanceTask()->getOwnerId());

		if($taskDone)
		{
			return '';
		}

		return static::agentName($filterId);
	}


	/**
	 * Adds delayed delete worker agent.
	 * @param array $params Named parameters:
	 * 		int ownerId - who is owner,
	 * 		int filterId - as row private id from b_disk_volume as filter id,
	 * 		int storageId - limit only one storage
	 * 		bool DROP_UNNECESSARY_VERSION - set job to delete unused version,
	 * 		bool DROP_TRASHCAN - set job to empty trashcan.
	 * 		bool DROP_FOLDER - set job to drop everything.
	 * 		bool EMPTY_FOLDER - set job to empty folder structure.
	 * @return boolean
	 */
	public static function addWorker($params)
	{
		$ownerId = (int)$params['ownerId'];
		$filterId = (int)$params['filterId'];

		$task = new Volume\Task();
		if ($filterId > 0)
		{
			if(!$task->loadTaskById($filterId, $ownerId))
			{
				return false;
			}
			$ownerId = $task->getOwnerId();
		}

		$task->setStatus(Volume\Task::TASK_STATUS_WAIT);

		$subTaskCommands = array(
			Volume\Task::DROP_UNNECESSARY_VERSION,
			Volume\Task::DROP_TRASHCAN,
			Volume\Task::DROP_FOLDER,
			Volume\Task::EMPTY_FOLDER,
		);
		foreach ($subTaskCommands as $command)
		{
			if (isset($params[$command]))
			{
				$task->setStatusSubTask(
					$command,
					(($params[$command] === true) ? Volume\Task::TASK_STATUS_WAIT : Volume\Task::TASK_STATUS_NONE)
				);
			}
		}

		if ($filterId > 0)
		{
			if (isset($params['manual']))
			{
				$task->resetFail();
			}
			$agentParamsAdded = $task->fixState();
		}
		else
		{
			$task->setIndicatorType(Volume\Storage\Storage::className());
			$task->setParam('STORAGE_ID', (int)$params['storageId']);
			$task->setOwnerId($ownerId);

			$agentParamsAdded = $task->fixState();
			$filterId = $task->getId();
		}

		$agentAdded = false;
		if ($agentParamsAdded && $filterId > 0)
		{
			$agentAdded = true;
			$agents = \CAgent::GetList(
				array('ID' => 'DESC'),
				array('=NAME' => static::agentName($filterId))
			);
			if (!$agents->Fetch())
			{
				$agentAdded = (bool)(\CAgent::AddAgent(
										static::agentName($filterId),
										'disk',
										(self::canAgentUseCrontab() ? 'N' : 'Y'),
										self::AGENT_INTERVAL
									) !== false);
			}
		}

		// count statistic for progress bar
		self::countWorker($ownerId);

		return $agentAdded;
	}


	/**
	 * Checks ability agent to use Crontab.
	 * @return bool
	 */
	public static function canAgentUseCrontab()
	{
		$canAgentsUseCrontab = false;
		$agentsUseCrontab = \Thurly\Main\Config\Option::get('main', 'agents_use_crontab', 'N');
		if (
			!\Thurly\Main\ModuleManager::isModuleInstalled('thurlyos') &&
			($agentsUseCrontab === 'Y' || (defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT === true))
		)
		{
			$canAgentsUseCrontab = true;
		}

		return $canAgentsUseCrontab;
	}

	/**
	 * Cancels all agent process.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return void
	 */
	public static function cancelWorkers($ownerId)
	{
		$workerResult = \Thurly\Disk\Internals\VolumeTable::getList(array(
			'select' => array(
				'ID',
			),
			'filter' => array(
				'=OWNER_ID' => $ownerId,
				'=AGENT_LOCK' => array(Volume\Task::TASK_STATUS_WAIT, Volume\Task::TASK_STATUS_RUNNING),
			)
		));
		foreach ($workerResult as $row)
		{
			\Thurly\Disk\Internals\VolumeTable::update($row['ID'], array('AGENT_LOCK' => Volume\Task::TASK_STATUS_CANCEL));
		}

		self::clearProgressInfo($ownerId);
	}


	/**
	 * Count worker agent for user.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return int
	 */
	public static function countWorker($ownerId)
	{
		$workerResult = \Thurly\Disk\Internals\VolumeTable::getList(array(
			'runtime' => array(
				new Entity\ExpressionField('CNT', 'COUNT(*)'),
				new Entity\ExpressionField('FILE_COUNT', 'SUM(FILE_COUNT)'),
				new Entity\ExpressionField('UNNECESSARY_VERSION_COUNT', 'SUM(UNNECESSARY_VERSION_COUNT)'),
				new Entity\ExpressionField('DROPPED_FILE_COUNT', 'SUM(DROPPED_FILE_COUNT)'),
				new Entity\ExpressionField('DROPPED_VERSION_COUNT', 'SUM(DROPPED_VERSION_COUNT)'),
				new Entity\ExpressionField('FAIL_COUNT', 'SUM(FAIL_COUNT)'),
			),
			'select' => array(
				'CNT',
				'FILE_COUNT',
				'UNNECESSARY_VERSION_COUNT',
				'DROPPED_FILE_COUNT',
				'DROPPED_VERSION_COUNT',
				'FAIL_COUNT',
				\Thurly\Disk\Volume\Task::DROP_UNNECESSARY_VERSION,
				\Thurly\Disk\Volume\Task::DROP_TRASHCAN,
				\Thurly\Disk\Volume\Task::EMPTY_FOLDER,
				\Thurly\Disk\Volume\Task::DROP_FOLDER,
			),
			'group' => array(
				\Thurly\Disk\Volume\Task::DROP_UNNECESSARY_VERSION,
				\Thurly\Disk\Volume\Task::DROP_TRASHCAN,
				\Thurly\Disk\Volume\Task::EMPTY_FOLDER,
				\Thurly\Disk\Volume\Task::DROP_FOLDER,
			),
			'filter' => array(
				'=OWNER_ID' => $ownerId,
				'=AGENT_LOCK' => array(Volume\Task::TASK_STATUS_WAIT, Volume\Task::TASK_STATUS_RUNNING),
			)
		));

		$totalFilesToDrop = 0;
		$droppedFilesCount = 0;
		$workerCount = 0;
		$failCount = 0;

		if ($workerResult->getSelectedRowsCount() > 0)
		{
			foreach ($workerResult as $row)
			{
				$workerCount += $row['CNT'];
				$failCount += $row['FAIL_COUNT'];
				if (Volume\Task::isRunningMode($row[\Thurly\Disk\Volume\Task::DROP_UNNECESSARY_VERSION]))
				{
					$totalFilesToDrop += $row['UNNECESSARY_VERSION_COUNT'];
					$droppedFilesCount += $row['DROPPED_VERSION_COUNT'];
				}
				if (Volume\Task::isRunningMode($row[\Thurly\Disk\Volume\Task::DROP_TRASHCAN]))
				{
					$totalFilesToDrop += $row['FILE_COUNT'];
					$droppedFilesCount += $row['DROPPED_FILE_COUNT'];
				}
				if (Volume\Task::isRunningMode($row[\Thurly\Disk\Volume\Task::DROP_FOLDER]))
				{
					$totalFilesToDrop += $row['FILE_COUNT'];
					$droppedFilesCount += $row['DROPPED_FILE_COUNT'];
				}
				if (Volume\Task::isRunningMode($row[\Thurly\Disk\Volume\Task::EMPTY_FOLDER]))
				{
					$totalFilesToDrop += $row['FILE_COUNT'];
					$droppedFilesCount += $row['DROPPED_FILE_COUNT'];
				}
			}
			self::setProgressInfo($ownerId, $totalFilesToDrop, $droppedFilesCount, $failCount);
		}
		else
		{
			self::clearProgressInfo($ownerId);
		}

		return $workerCount;
	}


	/**
	 * Check if workers exists. Sets up/removes missing task. Remove stepper info.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return int
	 */
	public static function checkRestoreWorkers($ownerId)
	{
		$workerResult = \Thurly\Disk\Internals\VolumeTable::getList(array(
			'select' => array(
				'ID',
				'STORAGE_ID',
				\Thurly\Disk\Volume\Task::DROP_UNNECESSARY_VERSION,
				\Thurly\Disk\Volume\Task::DROP_TRASHCAN,
				\Thurly\Disk\Volume\Task::EMPTY_FOLDER,
				\Thurly\Disk\Volume\Task::DROP_FOLDER,
			),
			'filter' => array(
				'=OWNER_ID' => $ownerId,
				'=AGENT_LOCK' => array(Volume\Task::TASK_STATUS_WAIT, Volume\Task::TASK_STATUS_RUNNING),
			)
		));
		$workerCount = 0;
		if ($workerResult->getSelectedRowsCount() > 0)
		{
			$agents = \CAgent::GetList(
				array('ID' => 'DESC'),
				array('NAME' => self::agentName('%'))
			);
			$agentList = array();
			while ($agent = $agents->Fetch())
			{
				$agentList[] = $agent['NAME'];
			}

			$restoredWorkerCount = 0;
			foreach ($workerResult as $row)
			{
				$workerCount ++;
				if (in_array(self::agentName($row['ID']), $agentList) === false)
				{
					$agentParams = array(
						'ownerId' => $ownerId,
						'storageId' => $row['STORAGE_ID'],
						'filterId' => $row['ID'],
					);
					if (Volume\Task::isRunningMode($row[\Thurly\Disk\Volume\Task::DROP_UNNECESSARY_VERSION]))
					{
						$agentParams[\Thurly\Disk\Volume\Task::DROP_UNNECESSARY_VERSION] = true;
					}
					if (Volume\Task::isRunningMode($row[\Thurly\Disk\Volume\Task::DROP_TRASHCAN]))
					{
						$agentParams[\Thurly\Disk\Volume\Task::DROP_TRASHCAN] = true;
					}
					if (Volume\Task::isRunningMode($row[\Thurly\Disk\Volume\Task::DROP_FOLDER]))
					{
						$agentParams[\Thurly\Disk\Volume\Task::DROP_FOLDER] = true;
					}
					if (Volume\Task::isRunningMode($row[\Thurly\Disk\Volume\Task::EMPTY_FOLDER]))
					{
						$agentParams[\Thurly\Disk\Volume\Task::EMPTY_FOLDER] = true;
					}

					if (self::addWorker($agentParams))
					{
						$restoredWorkerCount ++;
					}
				}
			}

			if ($restoredWorkerCount > 0)
			{
				self::countWorker($ownerId);
			}
		}
		else
		{
			self::clearProgressInfo($ownerId);
		}

		return $workerCount;
	}


	/**
	 * Deletes files corresponding to indicator filter.
	 * @param Volume\IVolumeIndicator $indicator Ignited indicator for file list filter.
	 * @return boolean
	 */
	public function deleteFileByFilter(Volume\IVolumeIndicator $indicator)
	{
		$subTaskDone = true;

		if ($indicator->getFilterValue('STORAGE_ID') > 0)
		{
			$storage = \Thurly\Disk\Storage::loadById($indicator->getFilterValue('STORAGE_ID'));
			if (!$this->isAllowClearStorage($storage))
			{
				$this->collectError(
					new Error('Access denied to storage #'.$storage->getId(), 'ACCESS_DENIED'),
					false,
					true
				);

				return false;
			}
		}

		$filter = array();
		if ($this->instanceTask()->getLastFileId() > 0)
		{
			$filter['>=ID'] = $this->instanceTask()->getLastFileId();
		}

		$indicator->setLimit(self::MAX_FILE_PER_INTERACTION);

		$fileList = $indicator->getCorrespondingFileList($filter);

		$this->instanceTask()->setIterationFileCount($fileList->getSelectedRowsCount());

		$countFileErasure = 0;

		foreach ($fileList as $row)
		{
			$fileId = $row['ID'];
			$file = \Thurly\Disk\File::getById($fileId);
			if ($file instanceof \Thurly\Disk\File)
			{
				$securityContext = $this->getSecurityContext($this->getOwner(), $file);
				if($file->canDelete($securityContext))
				{
					$this->deleteFile($file);
					$countFileErasure ++;
				}
				else
				{
					$this->collectError(new Error("Access denied to file #$fileId", 'ACCESS_DENIED'));
				}
			}

			$this->instanceTask()->setLastFileId($fileId);

			// fix interval task state
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->instanceTask()->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->instanceTask()->fixState();

				// count statistic for progress bar
				self::countWorker($this->instanceTask()->getOwnerId());
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		return $subTaskDone;
	}


	/**
	 * Deletes files in trashcan.
	 * @param Volume\IVolumeIndicator $indicator Ignited indicator for file list filter.
	 * @return boolean
	 */
	public function deleteTrashcanByFilter(Volume\IVolumeIndicator $indicator)
	{
		$subTaskDone = true;

		$filter = array(
			'!DELETED_TYPE' => \Thurly\Disk\Internals\ObjectTable::DELETED_TYPE_NONE
		);
		if ($this->instanceTask()->getLastFileId() > 0)
		{
			$filter['>=ID'] = $this->instanceTask()->getLastFileId();
		}

		$indicator->setLimit(self::MAX_FILE_PER_INTERACTION);

		$fileList = $indicator->getCorrespondingFileList($filter);

		$this->instanceTask()->setIterationFileCount($fileList->getSelectedRowsCount());

		$countFileErasure = 0;

		foreach ($fileList as $row)
		{
			$fileId = $row['ID'];
			$file = \Thurly\Disk\File::getById($fileId);
			if ($file instanceof \Thurly\Disk\File)
			{
				$securityContext = $this->getSecurityContext($this->getOwner(), $file);
				if($file->canDelete($securityContext))
				{
					$this->deleteFile($file);
					$countFileErasure ++;
				}
				else
				{
					$this->collectError(new Error("Access denied to file #$fileId", 'ACCESS_DENIED'));
				}
			}

			$this->instanceTask()->setLastFileId($fileId);

			// fix interval task state
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->instanceTask()->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->instanceTask()->fixState();

				// count statistic for progress bar
				self::countWorker($this->instanceTask()->getOwnerId());
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		$indicator->setLimit(self::MAX_FOLDER_PER_INTERACTION);

		$folderList = $indicator->getCorrespondingFolderList(array('!DELETED_TYPE' => \Thurly\Disk\Internals\ObjectTable::DELETED_TYPE_NONE));

		foreach ($folderList as $row)
		{
			$folder = \Thurly\Disk\Folder::getById($row['ID']);
			if ($folder instanceof \Thurly\Disk\Folder)
			{
				$this->deleteFolder($folder);
				$countFileErasure ++;
			}

			// fix interval task state
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->instanceTask()->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->instanceTask()->fixState();

				// count statistic for progress bar
				self::countWorker($this->instanceTask()->getOwnerId());
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		return $subTaskDone;
	}


	/**
	 * Deletes unused file versions.
	 * @param Volume\IVolumeIndicator $indicator Ignited indicator for file list filter.
	 * @return boolean
	 */
	public function deleteUnnecessaryVersionByFilter(Volume\IVolumeIndicator $indicator)
	{
		$subTaskDone = true;

		if ($indicator->getFilterValue('STORAGE_ID') > 0)
		{
			$storage = \Thurly\Disk\Storage::loadById($indicator->getFilterValue('STORAGE_ID'));
			if (!$this->isAllowClearStorage($storage))
			{
				$this->collectError(
					new Error('Access denied to storage #'.$storage->getId(), 'ACCESS_DENIED'),
					false,
					true
				);

				return false;
			}
		}

		$filter = array();
		if ($this->instanceTask()->getLastFileId() > 0)
		{
			$filter['>=FILE_ID'] = $this->instanceTask()->getLastFileId();
		}

		$indicator->setLimit(self::MAX_FILE_PER_INTERACTION);

		$versionList = $indicator->getCorrespondingUnnecessaryVersionList($filter);

		$this->instanceTask()->setIterationFileCount($versionList->getSelectedRowsCount());

		$versionsPerFile = array();
		foreach ($versionList as $row)
		{
			$fileId = $row['FILE_ID'];
			$versionId = $row['VERSION_ID'];
			if (!isset($versionsPerFile[$fileId]))
			{
				$versionsPerFile[$fileId] = array();
			}
			$versionsPerFile[$fileId][] = $versionId;
		}
		unset($row, $fileId, $versionId, $versionList);


		$countFileErasure = 0;

		foreach ($versionsPerFile as $fileId => $versionIds)
		{
			$file = \Thurly\Disk\File::getById($fileId);

			if ($file instanceof \Thurly\Disk\File)
			{
				$securityContext = $this->getSecurityContext($this->getOwner(), $file);
				if($file->canDelete($securityContext))
				{
					$this->deleteFileUnnecessaryVersion($file, array('=ID' => $versionIds));
					$countFileErasure++;
				}
				else
				{
					$this->collectError(new Error("Access denied to file #$fileId", 'ACCESS_DENIED'));
				}
			}

			$this->instanceTask()->setLastFileId($fileId);

			// fix interval task state
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->instanceTask()->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->instanceTask()->fixState();

				// count statistic for progress bar
				self::countWorker($this->instanceTask()->getOwnerId());
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		return $subTaskDone;
	}


	/**
	 * Returns disk security context.
	 * @param \Thurly\Disk\User $user Task owner.
	 * @param \Thurly\Disk\BaseObject $object File or folder.
	 * @return \Thurly\Disk\Security\SecurityContext
	 */
	private function getSecurityContext($user, $object)
	{
		static $securityContextCache = array();

		$userId = $user->getId();
		$storageId = $object->getStorageId();

		if (!($securityContextCache[$userId][$storageId] instanceof \Thurly\Disk\Security\SecurityContext))
		{
			if (!isset($securityContextCache[$userId]))
			{
				$securityContextCache[$userId] = array();
			}

			if ($user->isAdmin())
			{
				$securityContextCache[$userId][$storageId] = new \Thurly\Disk\Security\FakeSecurityContext($userId);
			}
			else
			{
				$securityContextCache[$userId][$storageId] = $object->getStorage()->getSecurityContext($userId);
			}
		}

		return $securityContextCache[$userId][$storageId];
	}


	/**
	 * Deletes file.
	 * @param \Thurly\Disk\File $file File to drop.
	 * @return boolean
	 */
	public function deleteFile(\Thurly\Disk\File $file)
	{
		$logData = $this->instanceTask()->collectLogData($file);

		if(!$file->delete($this->instanceTask()->getOwnerId()))
		{
			$this->collectError($file->getErrors());

			return false;
		}

		$this->instanceTask()->log($logData, __FUNCTION__);

		$this->instanceTask()->increaseDroppedFileCount();

		return true;
	}


	/**
	 * Deletes file unnecessary versions.
	 * @param \Thurly\Disk\File $file File to purify.
	 * @param array $additionalFilter Additional filter for vertion selection.
	 * @return boolean
	 */
	public function deleteFileUnnecessaryVersion(\Thurly\Disk\File $file, $additionalFilter = array())
	{
		$subTaskDone = true;

		$filter = array(
			'=OBJECT_ID' => $file->getId(),
		);
		if (count($additionalFilter) > 0)
		{
			$filter = array_merge($filter, $additionalFilter);
		}

		$versionList = \Thurly\Disk\Version::getList(array(
			'filter' => $filter,
			'select' => array('ID')
		));
		foreach ($versionList as $row)
		{
			$versionId = $row['ID'];

			/** @var \Thurly\Disk\Version $version */
			$version = \Thurly\Disk\Version::getById($versionId);
			if(!$version instanceof \Thurly\Disk\Version)
			{
				continue;
			}

			// is a head
			//if ($version->isHead())
			if ($version->getFileId() == $file->getFileId())
			{
				continue;
			}

			// attached_object
			$attachedList = \Thurly\Disk\AttachedObject::getList(array(
				'filter' => array(
					'=OBJECT_ID' => $file->getId(),
					'=VERSION_ID' => $version->getId(),
				),
				'select' => array('ID'),
				'limit' => 1,
			));
			if($attachedList->getSelectedRowsCount() > 0)
			{
				continue;
			}

			// external_link
			$externalLinkList = \Thurly\Disk\ExternalLink::getList(array(
				'filter' => array(
					'=OBJECT_ID' => $file->getId(),
					'=VERSION_ID' => $version->getId(),
					'!TYPE' => \Thurly\Disk\ExternalLink::TYPE_AUTO,
				),
				'select' => array('ID'),
				'limit' => 1,
			));
			if($externalLinkList->getSelectedRowsCount() > 0)
			{
				continue;
			}

			$logData = $this->instanceTask()->collectLogData($version);

			// drop
			if(!$version->delete($this->instanceTask()->getOwnerId()))
			{
				$this->collectError($version->getErrors());
			}
			else
			{
				$this->instanceTask()->log($logData, __FUNCTION__);
				$this->instanceTask()->increaseDroppedVersionCount();
			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}
		}

		return $subTaskDone;
	}


	/**
	 * Deletes folder.
	 * @param \Thurly\Disk\Folder $folder Folder to drop.
	 * @param boolean $emptyOnly Just delete folder's content.
	 * @return boolean
	 */
	public function deleteFolder(\Thurly\Disk\Folder $folder, $emptyOnly = false)
	{
		$subTaskDone = true;

		if (!$this->isAllowClearStorage($folder->getStorage()))
		{
			$this->collectError(
				new Error('Access denied to storage #'. $folder->getStorageId(), 'ACCESS_DENIED'),
				false,
				true
			);

			return false;
		}

		if (!$emptyOnly && !$this->isAllowDeleteFolder($folder))
		{
			$this->collectError(
				new Error('Access denied to folder #'. $folder->getId(), 'ACCESS_DENIED'),
				false,
				true
			);

			return false;
		}

		$countFileErasure = 0;

		$objectList = \Thurly\Disk\Internals\ObjectTable::getList(array(
			'filter' => array(
				'=PATH_CHILD.PARENT_ID' => $folder->getId(),
			),
			'order' => array(
				'PATH_CHILD.DEPTH_LEVEL' => 'DESC',
				'ID' => 'ASC'
			),
			'limit' => self::MAX_FOLDER_PER_INTERACTION,
		));

		$this->instanceTask()->setIterationFileCount($objectList->getSelectedRowsCount());

		foreach ($objectList as $row)
		{
			if ($row['ID'] == $folder->getId())
			{
				continue;
			}

			$object = \Thurly\Disk\BaseObject::buildFromArray($row);

			/** @var Folder|File $object */
			if($object instanceof \Thurly\Disk\Folder)
			{
				/** @var \Thurly\Disk\File $object */
				$securityContext = $this->getSecurityContext($this->getOwner(), $object);
				if($object->canDelete($securityContext))
				{
					if ($this->isAllowDeleteFolder($object))
					{
						$logData = $this->instanceTask()->collectLogData($object);

						/** @var \Thurly\Disk\Folder $object */
						if (!$object->deleteTree($this->instanceTask()->getOwnerId()))
						{
							$this->collectError($object->getErrors(), false);

							$subTaskDone = false;
						}
						else
						{
							$this->instanceTask()->log($logData, __FUNCTION__);
							$this->instanceTask()->increaseDroppedFolderCount();
						}
					}
				}
				else
				{
					$this->collectError(new Error('Access denied to folder #'. $object->getId(), 'ACCESS_DENIED'), false);
				}
			}
			elseif($object instanceof \Thurly\Disk\File)
			{
				/** @var \Thurly\Disk\File $object */
				$securityContext = $this->getSecurityContext($this->getOwner(), $object);
				if($object->canDelete($securityContext))
				{
					$subTaskDone = $this->deleteFile($object);
				}
				else
				{
					$this->collectError(new Error('Access denied to file #'. $object->getId(), 'ACCESS_DENIED'));
				}
			}

			// fix interval task state
			$countFileErasure ++;
			if ($countFileErasure >= self::STATUS_FIX_INTERVAL)
			{
				$countFileErasure = 0;

				if ($this->instanceTask()->hasUserCanceled())
				{
					$subTaskDone = false;
					break;
				}

				$this->instanceTask()->fixState();

				// count statistic for progress bar
				self::countWorker($this->instanceTask()->getOwnerId());

			}

			if (!$this->checkTimeEnd())
			{
				$subTaskDone = false;
				break;
			}

		}

		if ($subTaskDone)
		{
			if ($emptyOnly === false)
			{
				$logData = $this->instanceTask()->collectLogData($folder);

				if (!$folder->deleteTree($this->instanceTask()->getOwnerId()))
				{
					$this->collectError($folder->getErrors());

					return false;
				}

				$this->instanceTask()->log($logData, __FUNCTION__);
				$this->instanceTask()->increaseDroppedFolderCount();
			}
		}

		return $subTaskDone;
	}


	/**
	 * Check ability to drop folder.
	 * @param \Thurly\Disk\Folder $folder Folder to drop.
	 * @return boolean
	 */
	public function isAllowDeleteFolder(\Thurly\Disk\Folder $folder)
	{
		$allowDrop = true;

		/** @var \Thurly\Disk\Volume\IDeleteConstraint[] $deleteConstraintList */
		static $deleteConstraintList;
		if (empty($deleteConstraintList))
		{
			$deleteConstraintList = array();

			// full list available indicators
			$constraintIdList = \Thurly\Disk\Volume\Base::listDeleteConstraint();
			foreach ($constraintIdList as $indicatorId => $indicatorIdClass)
			{
				$deleteConstraintList[$indicatorId] = new $indicatorIdClass();
			}
		}

		/** @var \Thurly\Disk\Volume\IDeleteConstraint $indicator */
		foreach ($deleteConstraintList as $indicatorId => $indicator)
		{
			if (!$indicator->isAllowDeleteFolder($folder))
			{
				$allowDrop = false;
			}
		}

		return $allowDrop;
	}

	/**
	 * Check ability to clear storage.
	 * @param \Thurly\Disk\Storage $storage Storage to clear.
	 * @return boolean
	 */
	public function isAllowClearStorage(\Thurly\Disk\Storage $storage)
	{
		$allowClear = true;

		/** @var \Thurly\Disk\Volume\IClearConstraint[] $clearConstraintList */
		static $clearConstraintList;
		if (empty($clearConstraintList))
		{
			$clearConstraintList = array();

			// full list available indicators
			$constraintIdList = \Thurly\Disk\Volume\Base::listClearConstraint();
			foreach ($constraintIdList as $indicatorId => $indicatorIdClass)
			{
				$clearConstraintList[$indicatorId] = new $indicatorIdClass();
			}
		}

		if ($storage instanceof \Thurly\Disk\Storage)
		{
			/** @var \Thurly\Disk\Volume\IClearConstraint $indicator */
			foreach ($clearConstraintList as $indicatorId => $indicator)
			{
				if (!$indicator->isAllowClearStorage($storage))
				{
					$allowClear = false;
				}
			}
		}

		return $allowClear;
	}

	/**
	 * Repeats measurement for indicator.
	 * @param Volume\IVolumeIndicator $indicator Ignited indicator for measure.
	 * @return boolean
	 */
	public static function repeatMeasure(Volume\IVolumeIndicator $indicator)
	{
		$indicator->resetMeasurementResult();
		$indicator->measure();

		if ($indicator->getFilterValue('STORAGE_ID') > 0)
		{
			if ($indicator::className() != Volume\Storage\Storage::className())
			{
				/** @var \Thurly\Disk\Volume\IVolumeIndicator $storageIndicator */
				$storageIndicator = new Volume\Storage\Storage();
				$storageIndicator->setOwner($indicator->getOwner());

				$storageIndicator->addFilter('STORAGE_ID', $indicator->getFilterValue('STORAGE_ID'));
				$result = $storageIndicator->getMeasurementResult();
				if ($row = $result->fetch())
				{
					$storageIndicator->setFilterId($row['ID']);
				}
				$storageIndicator->measure();
			}

			if ($indicator::className() != Volume\Storage\TrashCan::className())
			{
				/** @var \Thurly\Disk\Volume\IVolumeIndicator $trashCanIndicator */
				$trashCanIndicator = new Volume\Storage\TrashCan();
				$trashCanIndicator->setOwner($indicator->getOwner());

				$trashCanIndicator->addFilter('STORAGE_ID', $indicator->getFilterValue('STORAGE_ID'));
				$result = $trashCanIndicator->getMeasurementResult();
				if ($row = $result->fetch())
				{
					$trashCanIndicator->setFilterId($row['ID']);
				}
				$trashCanIndicator->measure();
			}
		}

		return true;
	}



	/**
	 * Gets timer.
	 * @return Volume\Timer
	 */
	public function instanceTimer()
	{
		if (!($this->timer instanceof Volume\Timer))
		{
			$this->timer = new Volume\Timer();

			if (self::isCronRun() === true)
			{
				// increase time limit for cron running task up to 10 minutes
				$this->timer->setTimeLimit(\Thurly\Disk\Volume\Timer::MAX_EXECUTION_TIME * 20);
			}
		}

		return $this->timer;
	}

	/**
	 * Sets start up time.
	 * @return void
	 */
	public function startTimer()
	{
		// running on hint
		if (self::isCronRun() === false && defined('START_EXEC_TIME') && START_EXEC_TIME > 0)
		{
			$this->instanceTimer()->startTimer(START_EXEC_TIME * 1000);
		}
		else
		{
			$this->instanceTimer()->startTimer();
		}
	}

	/**
	 * Checks timer for time limitation.
	 * @return bool
	 */
	public function checkTimeEnd()
	{
		return $this->instanceTimer()->checkTimeEnd();
	}

	/**
	 * Tells true if time limit reached.
	 * @return boolean
	 */
	public function hasTimeLimitReached()
	{
		return $this->instanceTimer()->hasTimeLimitReached();
	}

	/**
	 * Sets limitation time in seconds.
	 * @param int $timeLimit Timeout in seconds.
	 * @return void
	 */
	public function setTimeLimit($timeLimit)
	{
		$this->instanceTimer()->setTimeLimit($timeLimit);
	}

	/**
	 * Gets limitation time in seconds.
	 * @return int
	 */
	public function getTimeLimit()
	{
		return $this->instanceTimer()->getTimeLimit();
	}

	/**
	 * Gets step identification.
	 * @return string|null
	 */
	public function getStepId()
	{
		return $this->instanceTimer()->getStepId();
	}

	/**
	 * Sets step identification.
	 * @param string $stepId Step id.
	 * @return void
	 */
	public function setStepId($stepId)
	{
		$this->instanceTimer()->setStepId($stepId);
	}

	/**
	 * Gets dropped file count.
	 * @return int
	 */
	public function getDroppedFileCount()
	{
		return $this->instanceTask()->getDroppedFileCount();
	}

	/**
	 * Gets dropped version count.
	 * @return int
	 */
	public function getDroppedVersionCount()
	{
		return $this->instanceTask()->getDroppedVersionCount();
	}

	/**
	 * Gets dropped folder count.
	 * @return int
	 */
	public function getDroppedFolderCount()
	{
		return $this->instanceTask()->getDroppedFolderCount();
	}

	/**
	 * Gets task owner id.
	 * @return int
	 */
	public function getOwnerId()
	{
		return $this->ownerId;
	}

	/**
	 * Gets task owner.
	 * @return \Thurly\Disk\User
	 */
	public function getOwner()
	{
		if (!($this->owner instanceof \Thurly\Disk\User))
		{
			$this->owner = \Thurly\Disk\User::loadById($this->getOwnerId());
		}

		return $this->owner;
	}


	/**
	 * Adds an array of errors to the collection.
	 * @param \Thurly\Main\Error[] | \Thurly\Main\Error $errors Raised error.
	 * @param boolean $increaseTaskFail Increase error count in task.
	 * @param boolean $raiseTaskFatalError Raise task fatal error.
	 * @return void
	 */
	public function collectError($errors, $increaseTaskFail = true, $raiseTaskFatalError = false)
	{
		if (!($this->errorCollection instanceof ErrorCollection))
		{
			$this->errorCollection = new ErrorCollection();
		}

		if (is_array($errors))
		{
			$this->errorCollection->add($errors);
			$lastError = array_pop($errors);
		}
		else
		{
			$this->errorCollection->add(array($errors));
			$lastError = $errors;
		}

		if (($this->task instanceof Volume\Task) && ($lastError instanceof Error))
		{
			if ($increaseTaskFail)
			{
				$this->instanceTask()->increaseFailCount();
			}
			if ($raiseTaskFatalError)
			{
				$this->instanceTask()->raiseFatalError();
			}
			$this->instanceTask()->setLastError($lastError->getMessage());
		}
	}

	/**
	 * @return Error[]
	 */
	public function getErrors()
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return $this->errorCollection->toArray();
		}

		return array();
	}

	/**
	 * @return boolean
	 */
	public function hasErrors()
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return $this->errorCollection->hasErrors();
		}

		return false;
	}

	/**
	 * Returns array of errors with the necessary code.
	 * @param string $code Code of error.
	 * @return Error[]
	 */
	public function getErrorsByCode($code)
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return $this->errorCollection->getErrorsByCode($code);
		}

		return array();
	}

	/**
	 * Returns an error with the necessary code.
	 * @param string|int $code The code of the error.
	 * @return \Thurly\Main\Error|null
	 */
	public function getErrorByCode($code)
	{
		if ($this->errorCollection instanceof ErrorCollection)
		{
			return $this->errorCollection->getErrorByCode($code);
		}

		return null;
	}


	/**
	 * Set up information showing at stepper progress bar.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return array|null
	 */
	public static function getProgressInfo($ownerId)
	{
		$optionSerialized = \Thurly\Main\Config\Option::get(
			'main.stepper.disk',
			Volume\Cleaner::className(). $ownerId,
			''
		);
		if (!empty($optionSerialized))
		{
			return unserialize($optionSerialized);
		}

		return null;
	}

	/**
	 * Set up information showing at stepper progress bar.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @param int $totalFilesToDrop  Total files to drop.
	 * @param int $droppedFilesCount Dropped files count.
	 * @param int $failCount Failed deletion count.
	 * @return void
	 */
	public static function setProgressInfo($ownerId, $totalFilesToDrop, $droppedFilesCount = 0, $failCount = 0)
	{
		if ($totalFilesToDrop  > 0)
		{
			$option = Volume\Cleaner::getProgressInfo($ownerId);
			if (!empty($option) && $option['count'] > 0)
			{
				$prevTotalFilesToDrop = $option['count'];
				//$prevDroppedFilesCount = $option['steps'];

				// If total count decreases mean some agents finished its work.
				if ($prevTotalFilesToDrop > $totalFilesToDrop)
				{
					$droppedFilesCount = ($prevTotalFilesToDrop - $totalFilesToDrop) + $droppedFilesCount;
					$totalFilesToDrop = $prevTotalFilesToDrop;
				}
			}

			\Thurly\Main\Config\Option::set(
				'main.stepper.disk',
				self::className().$ownerId,
				serialize(array('steps' => ($droppedFilesCount + $failCount), 'count' => $totalFilesToDrop))
			);
		}
		else
		{
			self::clearProgressInfo($ownerId);
		}
	}

	/**
	 * Remove stepper progress bar.
	 * @param int $ownerId Whom will mark as deleted by.
	 * @return void
	 */
	public static function clearProgressInfo($ownerId)
	{
		\Thurly\Main\Config\Option::delete(
			'main.stepper.disk',
			array('name' => self::className(). $ownerId)
		);
	}
}

