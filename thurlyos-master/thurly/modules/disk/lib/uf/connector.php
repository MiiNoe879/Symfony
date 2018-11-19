<?php

namespace Thurly\Disk\Uf;

use Thurly\Disk\AttachedObject;
use Thurly\Main\Application;
use Thurly\Main\Loader;
use Thurly\Main\SystemException;

abstract class Connector
{
	protected $entityId;

	protected static $pathToUser  = '/company/personal/user/#user_id#/';
	protected static $pathToGroup = '/workgroups/group/#group_id#/';

	public function __construct($entityId)
	{
		$this->entityId = $entityId;
	}

	final public static function buildFromAttachedObject(AttachedObject $attachedObject)
	{
		if(!Loader::includeModule($attachedObject->getModuleId()))
		{
			throw new SystemException("Could not include module {$attachedObject->getModuleId()}");
		}
		$className = str_replace('\\\\', '\\', $attachedObject->getEntityType());
		/** @var \Thurly\Disk\Uf\Connector $connector */
		$connector = new $className($attachedObject->getEntityId());

		if(!$connector instanceof Connector)
		{
			throw new SystemException('Invalid class for Connector. Must be instance of Connector');
		}

		if($connector instanceof IWorkWithAttachedObject)
		{
			$connector->setAttachedObject($attachedObject);
		}

		return $connector;
	}

	public static function className()
	{
		return get_called_class();
	}

	public function getDataToShow()
	{
		return array();
	}

	/**
	 * $data contains 'text', 'version'
	 * @param       $authorId
	 * @param array $data
	 */
	public function addComment($authorId, array $data)
	{
		return;
	}

	/**
	 * @param $userId
	 * @return bool
	 */
	public function canRead($userId)
	{
		return false;
	}

	/**
	 * @param $userId
	 * @return bool
	 */
	public function canUpdate($userId)
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function canConfidenceReadInOperableEntity()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function canConfidenceUpdateInOperableEntity()
	{
		return false;
	}

	public function getPathToUser()
	{
		return $this::$pathToUser;
	}

	public function getPathToGroup()
	{
		return $this::$pathToGroup;
	}

	public static function setPathToUser($path)
	{
		if(!empty($path))
		{
			static::$pathToUser = $path;
		}
	}

	public static function setPathToGroup($path)
	{
		if(!empty($path))
		{
			static::$pathToGroup = $path;
		}
	}

	/**
	 * @return Application|\Thurly\Main\HttpApplication|\CAllMain|\CMain
	 */
	protected function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	/**
	 * @return array|bool|\CAllUser|\CUser
	 */
	protected function getUser()
	{
		global $USER;
		return $USER;
	}
}