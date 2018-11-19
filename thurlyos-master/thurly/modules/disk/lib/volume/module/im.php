<?php

namespace Thurly\Disk\Volume\Module;

use Thurly\Main;
use Thurly\Main\ObjectException;
use Thurly\Main\ArgumentTypeException;
use Thurly\Main\Localization\Loc;
use Thurly\Disk\Volume;
use Thurly\Disk\Internals\ObjectTable;

/**
 * Disk storage volume measurement class.
 * @package Thurly\Disk\Volume
 */
class Im extends Volume\Module\Module implements Volume\IVolumeIndicatorLink, Volume\IDeleteConstraint, Volume\IClearConstraint
{
	/** @var string */
	protected static $moduleId = 'im';

	/** @var \Thurly\Disk\Storage[] */
	private $storageList = array();

	/** @var \Thurly\Disk\Folder[] */
	private $folderList = array();

	/**
	 * Runs measure test to get volumes of selecting objects.
	 * @param array $collectData List types data to collect: ATTACHED_OBJECT, SHARING_OBJECT, EXTERNAL_LINK, UNNECESSARY_VERSION.
	 * @return $this
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public function measure($collectData = array())
	{
		if (!$this->isMeasureAvailable())
		{
			$this->addError(new \Thurly\Main\Error('', self::ERROR_MEASURE_UNAVAILABLE));
			return $this;
		}

		$connection = \Thurly\Main\Application::getConnection();
		$indicatorType = $connection->getSqlHelper()->forSql(static::className());
		$ownerId = (string)$this->getOwner();

		// collect disk statistics
		$this
			->addFilter(0, array(
				'LOGIC' => 'OR',
				'MODULE_ID' => self::getModuleId(),
				'ENTITY_TYPE' => \Thurly\Im\Disk\ProxyType\Im::className(),
			))
			->addFilter('DELETED_TYPE', ObjectTable::DELETED_TYPE_NONE);

		parent::measure();

		// collect none disk statistics
		$querySql = "
			SELECT 
				'{$indicatorType}' as INDICATOR_TYPE,
				{$ownerId} as OWNER_ID,
				". $connection->getSqlHelper()->getCurrentDateTimeFunction(). " as CREATE_TIME,
				SUM(FILE_SIZE) as FILE_SIZE,
				COUNT(*) as FILE_COUNT,
				0 as DISK_SIZE,
				0 as DISK_COUNT
			FROM
				b_file
			WHERE
				MODULE_ID IN('imopenlines', 'imconnector', 'imbot')
		";

		$columnList = Volume\QueryHelper::prepareInsert(
			array(
				'INDICATOR_TYPE',
				'OWNER_ID',
				'CREATE_TIME',
				'FILE_SIZE',
				'FILE_COUNT',
				'DISK_SIZE',
				'DISK_COUNT',
			),
			$this->getSelect()
		);

		$tableName = \Thurly\Disk\Internals\VolumeTable::getTableName();

		$connection->queryExecute("INSERT INTO {$tableName} ({$columnList}) {$querySql}");


		// collect folders statistics
		$storageListId = array();
		$folderListId = array();
		$storageList = $this->getStorageList();
		if (count($storageList) > 0)
		{
			foreach ($storageList as $storage)
			{
				$storageListId[] = $storage->getId();
				$folders = $this->getFolderList($storage);
				if (count($folders) > 0)
				{
					foreach ($folders as $folder)
					{
						$folderListId[] = $folder->getId();
					}
				}
			}
		}
		if (count($storageListId) > 0 && count($folderListId) > 0)
		{
			$agr = new \Thurly\Disk\Volume\Folder();
			$agr
				->setOwner($this->getOwner())
				->addFilter('@STORAGE_ID', $storageListId)
				->addFilter('@PARENT_ID', $folderListId)
				->purify()
				->measure(array(self::DISK_FILE));
		}

		/*
		// collect files statistics
		if (count($storageList) > 0)
		{
			foreach ($storageList as $storage)
			{
				$folders = $this->getFolderList($storage);
				if (count($folders) > 0)
				{
					foreach ($folders as $folder)
					{
						$agr = new Volume\File();
						$agr
							->setOwner($this->getOwner())
							->addFilter('STORAGE_ID', $storage->getId())
							->addFilter('PARENT_ID', $folder->getId())
							->purify()
							->measure();
					}
				}
			}
		}
		*/

		return $this;
	}

	/**
	 * Returns module storage.
	 * @return \Thurly\Disk\Storage[]|array
	 */
	public function getStorageList()
	{
		if (count($this->storageList) == 0 || !$this->storageList[0] instanceof \Thurly\Disk\Storage)
		{
			$entityTypes = self::getEntityType();
			$storage = \Thurly\Disk\Storage::load(array(
				'MODULE_ID' => self::getModuleId(),
				//'ENTITY_TYPE' => \Thurly\Im\Disk\ProxyType\Im::className()
				'ENTITY_TYPE' => $entityTypes[0]
			));

			if ($storage instanceof \Thurly\Disk\Storage)
			{
				$this->storageList[] = $storage;
			}
		}

		return $this->storageList;
	}

	/**
	 * Returns folder list corresponding to module.
	 * @param \Thurly\Disk\Storage $storage Module's storage.
	 * @return \Thurly\Disk\Folder[]|array
	 */
	public function getFolderList($storage)
	{
		if ($storage instanceof \Thurly\Disk\Storage && count($this->folderList[$storage->getId()]) == 0)
		{
			if ($this->isMeasureAvailable())
			{
				$this->folderList[$storage->getId()][] = $storage->getRootObject();
			}
		}

		return $this->folderList[$storage->getId()];
	}

	/**
	 * Returns special folder code list.
	 * @return string[]
	 */
	public static function getSpecialFolderCode()
	{
		return array('IM_SAVED');
	}

	/**
	 * Returns entity type list.
	 * @return string[]
	 */
	public static function getEntityType()
	{
		return array(
			//\Thurly\Im\Disk\ProxyType\Im::className()
			'Thurly\\Im\\Disk\\ProxyType\\Im'
		);
	}

	/**
	 * Check ability to clear storage.
	 * @param \Thurly\Disk\Storage $storage Storage to clear.
	 * @return boolean
	 */
	public function isAllowClearStorage(\Thurly\Disk\Storage $storage)
	{
		static $imStorageId;
		if (empty($imStorageId))
		{
			$storageList = $this->getStorageList();
			if ($storageList[0] instanceof \Thurly\Disk\Storage)
			{
				$imStorageId = $storageList[0]->getId();
			}
		}

		// disallow clearance if im is unavailable
		if ($storage instanceof \Thurly\Disk\Storage)
		{
			if ($imStorageId === $storage->getId())
			{
				return $this->isMeasureAvailable();// returns false to prevent fatal error
			}
		}

		return true;
	}

	/**
	 * Check ability to drop folder.
	 * @param \Thurly\Disk\Folder $folder Folder to drop.
	 * @return boolean
	 */
	public function isAllowDeleteFolder(\Thurly\Disk\Folder $folder)
	{
		/*
		if (!$this->isMeasureAvailable())
		{
			return true;
		}
		*/

		static $imStorageId;
		if (empty($imStorageId))
		{
			$storageList = $this->getStorageList();
			if ($storageList[0] instanceof \Thurly\Disk\Storage)
			{
				$imStorageId = $storageList[0]->getId();
			}
		}

		// disallow drop any folders within IM storage
		return (bool)($imStorageId != $folder->getStorageId());
	}

	/**
	 * Returns calculation result set per folder.
	 * @param array $collectedData List types of collected data to return.
	 * @return array
	 */
	public function getMeasurementFolderResult($collectedData = array())
	{
		\Thurly\Main\Loader::includeModule(self::getModuleId());

		$chatList = array();

		$totalSize = 0;
		$storageList = $this->getStorageList();
		if (count($storageList) > 0)
		{
			foreach ($storageList as $storage)
			{
				$folders = $this->getFolderList($storage);
				$folderIds = array();
				if (count($folders) > 0)
				{
					foreach ($folders as $folder)
					{
						$folderIds[] = $folder->getId();
					}
				}

				if (count($folderIds) > 0)
				{
					$folder = new Volume\Folder();
					$folder
						->setOwner($this->getOwner())
						->addFilter('=STORAGE_ID', $storage->getId())
						->addFilter('@PARENT_ID', $folderIds)
						->loadTotals();

					if ($folder->getTotalCount() > 0)
					{
						$result = $folder->getMeasurementResult();

						foreach ($result as $row)
						{
							$chatList[] = $row;
							$totalSize += $row['FILE_SIZE'];
						}
					}
				}
			}
		}
		if ($totalSize > 0)
		{
			foreach ($chatList as $id => $row)
			{
				$percent = $row['FILE_SIZE'] * 100 / $totalSize;
				$chatList[$id]['PERCENT'] = round($percent, 1);
			}
		}

		return $chatList;
	}

	/**
	 * @param string[] $filter Filter with module id.
	 * @return Volume\Fragment
	 * @throws ArgumentTypeException
	 * @throws ObjectException
	 */
	public static function getFragment(array $filter)
	{
		if($filter['INDICATOR_TYPE'] == Volume\Folder::className())
		{
			if (\Thurly\Main\Loader::includeModule(self::getModuleId()))
			{
				$chatList = \Thurly\Im\Model\ChatTable::getList(array(
					'select' => array(
						'ID' => 'ID',
						//'TITLE' => 'TITLE',
						//'AVATAR' => 'AVATAR',
						//'AUTHOR_ID' => 'AUTHOR_ID',
						//'COLOR' => 'COLOR',
						//'CHAT_TYPE' => 'TYPE',
						//'AUTHOR_NAME' => 'AUTHOR.NAME',
						//'AUTHOR_LAST_NAME' => 'AUTHOR.LAST_NAME',
					),
					'filter' => Array('=DISK_FOLDER_ID' => $filter['FOLDER_ID'])
				));
				if ($chat = $chatList->fetch())
				{
					$chatId = $chat['ID'];

					// Chat specific
					$chatData = \CIMChat::GetChatData(array('ID' => $chatId, 'PHOTO_SIZE' => 50));

					if ($chatData['chat'][$chatId]['avatar'] === '/thurly/js/im/images/blank.gif')
					{
						$chatData['chat'][$chatId]['avatar'] = '';
					}
					if ($chatData['chat'][$chatId]['owner'] > 0)
					{
						$chatOwner = \Thurly\Im\User::getInstance($chatData['chat'][$chatId]['owner']);
						if ($chatOwner instanceof \Thurly\Im\User)
						{
							$chatData['chat'][$chatId]['owner_name'] = $chatOwner->getFullName();

							if ($chatOwner->isActive() !== true)
							{
								// user fired
								Loc::loadMessages(__DIR__.'/socialnetwork.php');

								if ($chatOwner->getGender() === 'F')
								{
									$chatData['chat'][$chatId]['owner_name'] = Loc::getMessage(
										'DISK_VOLUME_MODULE_SONET_FIRED_F',
										array('#USER_NAME#' => $chatData['chat'][$chatId]['owner_name'])
									);
								}
								else
								{
									$chatData['chat'][$chatId]['owner_name'] = Loc::getMessage(
										'DISK_VOLUME_MODULE_SONET_FIRED_M',
										array('#USER_NAME#' => $chatData['chat'][$chatId]['owner_name'])
									);
								}
							}
						}
					}

					$filter['SPECIFIC'] = array(
						'chat' => $chatData['chat'][$chatId],
						'userInChat' => $chatData['userInChat'][$chatId],
						'userCount' => count($chatData['userInChat'][$chatId]),
					);
				}
			}

			return new Volume\Fragment($filter);
		}
		return parent::getFragment($filter);
	}

	/**
	 * @param Volume\Fragment $fragment Folder entity object.
	 * @return string
	 * @throws ArgumentTypeException
	 */
	public static function getTitle(Volume\Fragment $fragment)
	{
		if($fragment->getIndicatorType() == Volume\Folder::className())
		{
			$specific = $fragment->getSpecific();
			if ($specific['chat']['TITLE'] != '')
			{
				$title = $specific['chat']['TITLE'];
			}
			elseif($specific['userCount'] > 0)
			{
				$chatUserNameList = array();
				foreach ($specific['userInChat'] as $chatUserId)
				{
					$chatUserNameList[] = $userName = \Thurly\Im\User::getInstance($chatUserId)->getFullName();
					if (count($chatUserNameList) >= 3)
					{
						$chatUserNameList[] = '...';
						break;
					}
				}
				$title = implode(', ', $chatUserNameList);
			}
			else
			{
				$folder = $fragment->getFolder();
				if (!$folder instanceof \Thurly\Disk\Folder)
				{
					throw new ArgumentTypeException('Fragment must be subclass of '.\Thurly\Disk\Folder::className());
				}
				$title = $folder->getOriginalName();
			}

			return $title;
		}

		Loc::loadMessages(__FILE__);
		return Loc::getMessage('DISK_VOLUME_MODULE_IM');
	}

	/**
	 * Returns last update time of the entity object.
	 * @param Volume\Fragment $fragment Entity object.
	 * @return \Thurly\Main\Type\DateTime|null
	 * @throws ArgumentTypeException
	 */
	public static function getUpdateTime(Volume\Fragment $fragment)
	{
		$timestampUpdate = null;
		if($fragment->getIndicatorType() == Volume\Folder::className())
		{
			$specific = $fragment->getSpecific();
			if ($specific['chat']['LAST_MESSAGE_ID'] > 0)
			{
				$message = \Thurly\Im\Model\MessageTable::getById($specific['chat']['LAST_MESSAGE_ID'])->fetch();
				/** @var \Thurly\Main\Type\DateTime $timestampUpdate */
				$timestampUpdate = $message['DATE_CREATE'];
			}
			else
			{
				$folder = $fragment->getFolder();
				if (!$folder instanceof \Thurly\Disk\Folder)
				{
					throw new ArgumentTypeException('Fragment must be subclass of '.\Thurly\Disk\Folder::className());
				}
				$timestampUpdate = $folder->getUpdateTime()->toUserTime();
			}
		}

		return $timestampUpdate;
	}


	/**
	 * @param Volume\Fragment $fragment Folder entity object.
	 * @return string|null
	 * @throws ArgumentTypeException
	 */
	public static function getUrl(Volume\Fragment $fragment)
	{
		$url = null;
		/*
		if($fragment->getIndicatorType() == Volume\Folder::className())
		{
			$folder = $fragment->getFolder();
			if (!$folder instanceof \Thurly\Disk\Folder)
			{
				throw new ArgumentTypeException('Fragment must be subclass of '.\Thurly\Disk\Folder::className());
			}
			$urlManager = \Thurly\Disk\Driver::getInstance()->getUrlManager();

			$url = $urlManager->getUrlFocusController('openFolderList', array('folderId' => $folder->getId()));
		}
		*/

		return $url;
	}
}
