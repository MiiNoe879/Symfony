<?php

namespace Thurly\Disk\Volume\Module;

use Thurly\Main\Localization\Loc;
use Thurly\Disk\Volume;
use Thurly\Disk\Internals\ObjectTable;

/**
 * Disk storage volume measurement class.
 * @package Thurly\Disk\Volume
 */
class Socialnetwork extends Volume\Module\Module
{
	/** @var string */
	protected static $moduleId = 'socialnetwork';

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
				if ($attachedEntitySql != '')
				{
					$attachedEntitySql .= ', ';
				}
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
			$eventTypeXML = array();
			$eventTypeList = array('sonet', 'forum', 'photo_photo', 'news');
			foreach ($eventTypeList as $eventId)
			{
				$forumMetaData = \CSocNetLogTools::getForumCommentMetaData($eventId);
				$eventTypeXML[] = $forumMetaData[0];
			}
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
							AND files.TYPE = '".ObjectTable::TYPE_FILE."'
							AND files.ID = files.REAL_OBJECT_ID
					WHERE
						EXISTS(
							SELECT 1 
							FROM 
								b_disk_attached_object attached
								INNER JOIN b_forum_message message 
									ON message.ID = attached.ENTITY_ID
							WHERE
								attached.OBJECT_ID = files.ID
								AND (attached.VERSION_ID IS NULL OR attached.VERSION_ID = ver.ID)
								AND attached.ENTITY_TYPE = '". $connection->getSqlHelper()->forSql(\Thurly\Disk\Uf\ForumMessageConnector::className()). "'
								AND substring_index(message.XML_ID,'_', 1) IN('". implode("','", $eventTypeXML). "')
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
				'=ENTITY_ID' => array(
					\Thurly\Socialnetwork\Livefeed\Provider::DATA_ENTITY_TYPE_BLOG_POST, //'BLOG_POST',
					\Thurly\Socialnetwork\Livefeed\Provider::DATA_ENTITY_TYPE_BLOG_COMMENT, //'BLOG_COMMENT',
					\Thurly\Socialnetwork\Livefeed\LogEvent::PROVIDER_ID, //'SONET_LOG',
					\Thurly\Socialnetwork\Livefeed\LogComment::PROVIDER_ID, //'SONET_COMMENT',
					'FORUM_MESSAGE',
				),
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
					if (isset($entityList[$entityName]))
					{
						continue;
					}

					//$entity[$entityName][] = $userField;

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
			\Thurly\Disk\Uf\BlogPostConnector::className(),
			\Thurly\Disk\Uf\BlogPostCommentConnector::className(),
			\Thurly\Disk\Uf\SonetLogConnector::className(),
			\Thurly\Disk\Uf\SonetCommentConnector::className(),
			//\Thurly\Disk\Uf\ForumMessageConnector::className(),
		);

		return $attachedEntityList;
	}

	/**
	 * @param Volume\Fragment $fragment Module description structure.
	 * @return string
	 */
	public static function getTitle(Volume\Fragment $fragment)
	{
		Loc::loadMessages(__FILE__);
		return Loc::getMessage('DISK_VOLUME_MODULE_SOCIALNETWORK');
	}
}

