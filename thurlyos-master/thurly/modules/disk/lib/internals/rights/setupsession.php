<?php


namespace Thurly\Disk\Internals\Rights;

use Thurly\Disk\BaseObject;
use Thurly\Disk\Driver;
use Thurly\Disk\Folder;
use Thurly\Disk\Internals;
use Thurly\Main\Event;
use Thurly\Main\EventManager;
use Thurly\Main\Type\DateTime;

/**
 * Class SetupSession
 * @method BaseObject getObject()
 * @method SetupSession getParent()
 * @package Thurly\Disk\Internals\Rights
 */
final class SetupSession extends Internals\Entity\Model
{
	const REF_OBJECT = 'object';
	const REF_PARENT = 'parent';

	const STATUS_STARTED  = Table\RightSetupSessionTable::STATUS_STARTED;
	const STATUS_FINISHED = Table\RightSetupSessionTable::STATUS_FINISHED;
	const STATUS_FORKED   = Table\RightSetupSessionTable::STATUS_FORKED;
	const STATUS_BAD      = Table\RightSetupSessionTable::STATUS_BAD;

	/** @var int */
	protected $objectId;
	/** @var int */
	protected $parentId;
	/** @var int */
	protected $status;
	/** @var  DateTime */
	protected $createTime;
	/** @var int */
	protected $createdBy;

	/**
	 * Gets the fully qualified name of table class which belongs to current model.
	 * @throws \Thurly\Main\NotImplementedException
	 * @return string
	 */
	public static function getTableClassName()
	{
		return Table\RightSetupSessionTable::className();
	}

	/**
	 * Registers session to recalculate simple rights in b_disk_tmp_simple_right.
	 *
	 * @param int $objectId Object id.
	 * @param int|null $createdBy User id.
	 *
	 * @return SetupSession
	 */
	public static function register($objectId, $createdBy = null)
	{
		$model = self::add(
			array(
				'OBJECT_ID' => $objectId,
				'CREATED_BY' => $createdBy,
			),
			new Internals\Error\ErrorCollection()
		);

		if ($model)
		{
			//this is internal event. It's possible, that we will delete it. Don't use it.
			$event = new Event(
				Driver::INTERNAL_MODULE_ID,
				"onRightsSetupSessionRegister",
				array(
					'sessionModel' => $model,
				)
			);
			$event->send();
		}

		return $model;
	}

	/**
	 * Restarts process of setting simple rights on the object.
	 *
	 * @return void
	 */
	public function forkAndRestart()
	{
		if ($this->isFinished() || $this->isForked())
		{
			return;
		}

		Table\TmpSimpleRightTable::deleteBySessionId($this->id);

		if ($this->getObject())
		{
			$self = $this;

			$eventManager = EventManager::getInstance();
			$eventManager->addEventHandler(
				Driver::INTERNAL_MODULE_ID,
				'onRightsSetupSessionRegister',
				function (Event $event) use ($self){
					/** @var SetupSession $childModel */
					$childModel = $event->getParameter('sessionModel');
					$childModel->bindParent($self);
				}
			);

			Driver::getInstance()->getRightsManager()->resetSimpleRights($this->getObject());
		}

		$this->update(array(
			'STATUS' => self::STATUS_FORKED,
		));
	}

	/**
	 * Binds parent session to current.
	 * @param SetupSession $session Parent session.
	 *
	 * @return $this
	 */
	public function bindParent(SetupSession $session)
	{
		$this->update(array(
			'PARENT_ID' => $session->getId(),
	  	));

		$this->setReferenceValue(self::REF_PARENT, $session);

		return $this;
	}

	/**
	 * Finishes the logic of setup session. It moves simple rights to original table.
	 * @return void
	 */
	public function finish()
	{
		if($this->getObject() instanceof Folder)
		{
			$type = Internals\FolderTable::TYPE_FOLDER;
		}
		else
		{
			$type = Internals\FileTable::TYPE_FILE;
		}

		Internals\SimpleRightTable::deleteSimpleFromSelfAndChildren($this->objectId, $type);
		Table\TmpSimpleRightTable::moveToOriginalSimpleRights($this->id);
		Table\TmpSimpleRightTable::deleteBySessionId($this->id);

		$this->update(array(
			'STATUS' => self::STATUS_FINISHED,
		));
	}

	/**
	 * Deletes session.
	 * @see \Thurly\Disk\Internals\Cleaner::deleteRightSetupSession()
	 * @return bool
	 */
	public function delete()
	{
		return $this->deleteInternal();
	}

	/**
	 * @return int
	 */
	public function getObjectId()
	{
		return $this->objectId;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return int
	 */
	public function getParentId()
	{
		return $this->parentId;
	}

	/**
	 * @return bool
	 */
	public function isFinished()
	{
		return $this->status == self::STATUS_FINISHED;
	}

	/**
	 * @return bool
	 */
	public function isForked()
	{
		return $this->status == self::STATUS_FORKED;
	}

	/**
	 * @return DateTime
	 */
	public function getCreateTime()
	{
		return $this->createTime;
	}

	/**
	 * @return int
	 */
	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	/**
	 * Sets object to setup session.
	 * @param BaseObject $object Object.
	 *
	 * @return $this
	 */
	public function setObject(BaseObject $object)
	{
		$this->setReferenceValue(self::REF_OBJECT, $object);

		return $this;
	}

	/**
	 * @return array
	 */
	public static function getMapAttributes()
	{
		return array(
			'ID' => 'id',
			'OBJECT_ID' => 'objectId',
			'PARENT_ID' => 'parentId',
			'STATUS' => 'status',
			'CREATE_TIME' => 'createTime',
			'CREATED_BY' => 'createdBy',
		);
	}

	/**
	 * Returns the list attributes which is connected with another models.
	 *
	 * @return array
	 */
	public static function getMapReferenceAttributes()
	{
		return array(
			self::REF_OBJECT => array(
				'class' => BaseObject::className(),
				'load' => function(SetupSession $setupSession){
					return BaseObject::loadById($setupSession->getObjectId());
				},
			),
			self::REF_PARENT => array(
				'class' => SetupSession::className(),
				'load' => function(SetupSession $setupSession){
					return SetupSession::loadById($setupSession->getParentId());
				},
			),
		);
	}
} 