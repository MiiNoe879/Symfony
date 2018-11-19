<?php

namespace Thurly\Disk;


use Thurly\Main\Entity\Event;
use Thurly\Main\EventManager;

final class CrumbStorage
{
	/** @var  Driver */
	private static $instance;
	/** @var array  */
	private $crumbsByObjectId = array();

	protected function __construct()
	{
		$this->setEvents();
	}

	protected function setEvents()
	{
		$eventManager = EventManager::getInstance();

		$eventManager->addEventHandler(Driver::INTERNAL_MODULE_ID, 'FileOnAfterMove', array($this, 'onObjectOnAfterMove'));
		$eventManager->addEventHandler(Driver::INTERNAL_MODULE_ID, 'FolderOnAfterMove', array($this, 'onObjectOnAfterMove'));
		$eventManager->addEventHandler(Driver::INTERNAL_MODULE_ID, 'ObjectOnAfterMove', array($this, 'onObjectOnAfterMove'));
	}

	private function __clone()
	{}

	/**
	 * Returns Singleton of CrumbStorage.
	 * @return CrumbStorage
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new CrumbStorage;
		}

		return self::$instance;
	}

	/**
	 * Event listener, which cleans crumbs after move.
	 * @param Event $event Event.
	 * @return void
	 */
	public function onObjectOnAfterMove(Event $event)
	{
		$primaryData = $event->getParameter('id');
		if($primaryData)
		{
			$this->cleanByObjectId($primaryData['ID']);
		}
	}

	/**
	 * Cleans calculated crumbs by object id.
	 * @param int $objectId
	 * @return void
	 */
	public function cleanByObjectId($objectId)
	{
		unset($this->crumbsByObjectId[$objectId]);
	}

	/**
	 * Get list of crumbs by object.
	 * @param BaseObject $object BaseObject.
	 * @param bool   $includeSelf Append name of object.
	 * @return array
	 */
	public function getByObject(BaseObject $object, $includeSelf = false)
	{
		if(!isset($this->crumbsByObjectId[$object->getId()]))
		{
			$this->calculateCrumb($object);
		}
		if($includeSelf)
		{
			return $this->crumbsByObjectId[$object->getId()];
		}

		return array_slice($this->crumbsByObjectId[$object->getId()], 0, -1, true);
	}

	protected function calculateCrumb(BaseObject $object)
	{
		$parentId = $object->getParentId();
		if(!$parentId)
		{
			$this->crumbsByObjectId[$object->getId()] = array($object->getName());
			return $this->crumbsByObjectId[$object->getId()];
		}

		if(isset($this->crumbsByObjectId[$parentId]))
		{
			$this->crumbsByObjectId[$object->getId()] = $this->crumbsByObjectId[$parentId];
			$this->crumbsByObjectId[$object->getId()][$object->getId()] = $object->getName();

			return $this->crumbsByObjectId[$object->getId()];
		}

		$storage = $object->getStorage();
		$fake = Driver::getInstance()->getFakeSecurityContext();

		$this->crumbsByObjectId[$object->getId()] = array();
		foreach($object->getParents($fake, array('select' => array('ID', 'NAME', 'TYPE')), SORT_DESC) as $parent)
		{
			if($parent->getId() == $storage->getRootObjectId())
			{
				continue;
			}
			$this->crumbsByObjectId[$object->getId()][$parent->getId()] = $parent->getName();
		}
		unset($parent);

		$this->crumbsByObjectId[$parentId] = $this->crumbsByObjectId[$object->getId()];
		$this->crumbsByObjectId[$object->getId()][$object->getId()] = $object->getName();

		return $this->crumbsByObjectId[$object->getId()];
	}
}