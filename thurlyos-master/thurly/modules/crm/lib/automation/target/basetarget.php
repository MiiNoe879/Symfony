<?php
namespace Thurly\Crm\Automation\Target;

use Thurly\Crm\Automation\Factory;

use Thurly\Main\InvalidOperationException;

abstract class BaseTarget
{
	protected $entity;
	protected $runtime;
	protected $appliedTrigger;

	/**
	 * @param $entity
	 * @return $this
	 */
	public function setEntity($entity)
	{
		$this->entity = $entity;
		return $this;
	}

	public function setEntityField($field, $value)
	{
		if ($this->entity === null)
			throw new InvalidOperationException('Entity must be set by setEntity method.');

		$this->entity[$field] = $value;
	}

	/**
	 * @return mixed
	 */
	public function getEntity()
	{
		if ($this->entity === null)
			return array();

		return $this->entity;
	}

	/**
	 * Set applied trigger data.
	 * @param array $trigger
	 * @return $this
	 */
	public function setAppliedTrigger(array $trigger)
	{
		$this->appliedTrigger = $trigger;

		return $this;
	}

	/**
	 * Returns applied trigger data.
	 * @return array|null
	 */
	public function getAppliedTrigger()
	{
		return $this->appliedTrigger;
	}

	/**
	 * @return \Thurly\Crm\Automation\Engine\Runtime
	 */
	public function getRuntime()
	{
		if ($this->runtime === null)
		{
			$this->runtime = Factory::createRuntime();
			$this->runtime->setTarget($this);
		}

		return $this->runtime;
	}

	abstract public function getEntityTypeId();
	abstract public function getEntityId();
	abstract public function setEntityById($id);
	abstract public function getResponsibleId();
	abstract public function getEntityStatus();
	abstract public function setEntityStatus($statusId);
	abstract public function getEntityStatuses();
}