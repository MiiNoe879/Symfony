<?php

namespace Thurly\Disk;

use Thurly\Disk\Internals\RecentlyUsedTable;
use Thurly\Disk\Internals\Error\ErrorCollection;
use Thurly\Main\Type\DateTime;

/**
 * Class RecentlyUsed
 * @package Thurly\Disk
 */
final class RecentlyUsed extends Internals\Model
{
	/** @var int */
	protected $userId;
	/** @var User */
	protected $user;
	/** @var int */
	protected $objectId;
	/** @var DateTime */
	protected $createTime;

	/**
	 * Gets the fully qualified name of table class which belongs to current model.
	 * @throws \Thurly\Main\NotImplementedException
	 * @return string
	 */
	public static function getTableClassName()
	{
		return RecentlyUsedTable::className();
	}

	/**
	 * Returns create time.
	 * @return DateTime
	 */
	public function getCreateTime()
	{
		return $this->createTime;
	}

	/**
	 * Returns object id.
	 * @return int
	 */
	public function getObjectId()
	{
		return $this->objectId;
	}

	/**
	 * Returns user id.
	 * @return int
	 */
	public function getUserId()
	{
		return $this->userId;
	}

	/**
	 * Returns user model.
	 * @return User
	 */
	public function getUser()
	{
		if($this->userId === null)
		{
			return null;
		}
		if(SystemUser::isSystemUserId($this->userId))
		{
			return SystemUser::create();
		}

		if(isset($this->user) && $this->userId == $this->user->getId())
		{
			return $this->user;
		}
		$this->user = User::loadById($this->userId);

		return $this->user;
	}

	/**
	 * Pushes new data to recently used log.
	 * @param array           $data Data.
	 * @param ErrorCollection $errorCollection Error collection.
	 * @return static
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function push(array $data, ErrorCollection $errorCollection)
	{
		static::checkRequiredInputParams($data, array('USER_ID', 'OBJECT_ID'));

		return static::add(array(
			'USER_ID' => $data['USER_ID'],
			'OBJECT_ID' => $data['OBJECT_ID'],
		), $errorCollection);
	}

	/**
	 * Returns the list of pair for mapping data and object properties.
	 * Key is field in DataManager, value is object property.
	 * @return array
	 */
	public static function getMapAttributes()
	{
		return array(
			'ID' => 'id',
			'USER_ID' => 'userId',
			'OBJECT_ID' => 'objectId',
			'CREATE_TIME' => 'createTime',
		);
	}
}