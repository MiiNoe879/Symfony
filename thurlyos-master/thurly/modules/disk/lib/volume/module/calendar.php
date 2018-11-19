<?php

namespace Thurly\Disk\Volume\Module;

use Thurly\Disk\Volume;
use Thurly\Disk\Internals\ObjectTable;

/**
 * Disk storage volume measurement class.
 * @package Thurly\Disk\Volume
 */
class Calendar extends Volume\Module\Module
{
	/** @var string */
	protected static $moduleId = 'calendar';

	/**
	 * Runs measure test to get volumes of selecting objects.
	 * @param array $collectData List types data to collect: ATTACHED_OBJECT, SHARING_OBJECT, EXTERNAL_LINK, UNNECESSARY_VERSION.
	 * @return $this
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

		$attachedEntityList = $this->getAttachedEntityList();
		$attachedEntitySql = '';
		if (count($attachedEntityList) > 0)
		{
			foreach ($attachedEntityList as $attachedEntity)
			{
				if ($attachedEntitySql != '') $attachedEntitySql .= ', ';
				$attachedEntitySql .= "'".$connection->getSqlHelper()->forSql($attachedEntity)."'";
			}
		}

		// Scan User fields specific to module
		$entityUserFieldSource = $this->prepareUserFieldSourceSql(null, array(\CUserTypeFile::USER_TYPE_ID));
		if ($entityUserFieldSource != '')
		{
			$entityUserFieldSource = " UNION {$entityUserFieldSource} ";
		}

		// Forum comments attachments
		$attachedForumCommentsSql = '';
		if (\Thurly\Main\ModuleManager::isModuleInstalled('forum') && \Thurly\Main\Loader::includeModule('socialnetwork'))
		{
			$forumMetaData = \CSocNetLogTools::getForumCommentMetaData('lists_new_element');
			$eventTypeXML = $forumMetaData[0];

			$attachedForumCommentsSql = "
				UNION
				(
					SELECT
						SUM(ver.SIZE) as FILE_SIZE,
						COUNT(ver.FILE_ID) as FILE_COUNT,
						SUM(ver.SIZE) as DISK_SIZE,
						COUNT(DISTINCT files.ID) as DISK_COUNT,
						COUNT(ver.ID) as VERSION_COUNT
					FROM
						b_disk_version ver
						INNER JOIN b_disk_object files
							ON files.ID  = ver.OBJECT_ID
							AND files.TYPE = '".\Thurly\Disk\Internals\ObjectTable::TYPE_FILE."'
							AND files.ID = files.REAL_OBJECT_ID
					WHERE
						EXISTS
						(
							SELECT 1 
							FROM 
								b_disk_attached_object attached
								INNER JOIN b_forum_message message 
									ON message.ID = attached.ENTITY_ID
							WHERE
								attached.OBJECT_ID = files.ID
								AND (attached.VERSION_ID IS NULL OR attached.VERSION_ID = ver.ID)
								AND attached.ENTITY_TYPE = '". $connection->getSqlHelper()->forSql(\Thurly\Disk\Uf\ForumMessageConnector::className()). "'
								AND substring_index(message.XML_ID,'_', 1) = '{$eventTypeXML}'
						)
				)
			";
		}

		$querySql = "
			SELECT 
				'{$indicatorType}' as INDICATOR_TYPE,
				{$ownerId} as OWNER_ID,
				". $connection->getSqlHelper()->getCurrentDateTimeFunction(). " as CREATE_TIME,
				SUM(src.FILE_SIZE) as FILE_SIZE,
				SUM(src.FILE_COUNT) as FILE_COUNT,
				SUM(src.DISK_SIZE) as DISK_SIZE,
				SUM(src.DISK_COUNT) as DISK_COUNT,
				SUM(src.VERSION_COUNT) as VERSION_COUNT
			FROM 
			(
				(
					SELECT 
						SUM(ver.SIZE) as FILE_SIZE,
						COUNT(ver.FILE_ID) as FILE_COUNT,
						SUM(ver.SIZE) as DISK_SIZE,
						COUNT(DISTINCT files.ID) as DISK_COUNT,
						COUNT(ver.ID) as VERSION_COUNT
					FROM 
						b_disk_version ver 
						INNER JOIN b_disk_object files
							ON files.ID  = ver.OBJECT_ID
							AND files.TYPE = '".ObjectTable::TYPE_FILE."'
							AND files.ID = files.REAL_OBJECT_ID
					WHERE
						EXISTS( 
							SELECT 1 FROM b_disk_attached_object attached 
							WHERE
								attached.OBJECT_ID = files.ID
								AND (attached.VERSION_ID IS NULL OR attached.VERSION_ID = ver.ID)
								AND attached.ENTITY_TYPE IN($attachedEntitySql)
						)
				)
				{$attachedForumCommentsSql}
				{$entityUserFieldSource}
			) src
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
				'VERSION_COUNT',
			),
			$this->getSelect()
		);

		$tableName = \Thurly\Disk\Internals\VolumeTable::getTableName();

		$connection->queryExecute("INSERT INTO {$tableName} ({$columnList}) {$querySql}");

		return $this;
	}

	/**
	 * Returns entity list with user field corresponding to module.
	 * @return string[]
	 */
	public function getEntityList()
	{
		static $entityList;
		if (!isset($entityList))
		{
			$entityList = array();

			$filter = array(
				'=ENTITY_ID' => 'CALENDAR_EVENT',
				'=USER_TYPE_ID' => array(
					\CUserTypeFile::USER_TYPE_ID,
					\Thurly\Disk\Uf\FileUserType::USER_TYPE_ID,
					\Thurly\Disk\Uf\VersionUserType::USER_TYPE_ID,
				),
			);
			$userFieldList = \Thurly\Main\UserFieldTable::getList(array('filter' => $filter));
			if ($userFieldList->getSelectedRowsCount() > 0)
			{
				foreach ($userFieldList as $userField)
				{
					$entityName = $userField['ENTITY_ID'];
					if (isset($entityList[$entityName])) continue;

					$entity[$entityName][] = $userField;

					/** @var \Thurly\Main\Entity\Base $ent */
					$ent = \Thurly\Main\Entity\Base::compileEntity($entityName, array(), array(
						'namespace' => __NAMESPACE__,
						'uf_id'     => $entityName,
					));

					$entityList[$entityName] = $ent->getDataClass();
				}
			}
		}

		return $entityList;
	}

	/**
	 * Returns entity list attached to disk object corresponding to module.
	 * @return string[]
	 */
	public function getAttachedEntityList()
	{
		$attachedEntityList = array(
			\Thurly\Disk\Uf\CalendarEventConnector::className(),
		);

		return $attachedEntityList;
	}
}
