<?php
namespace Thurly\Crm\Automation\Target;

class LeadTarget extends BaseTarget
{
	protected $entityStatuses;

	public function getEntityTypeId()
	{
		return \CCrmOwnerType::Lead;
	}

	public function getEntityId()
	{
		$entity = $this->getEntity();
		return isset($entity['ID']) ? (int)$entity['ID'] : 0;
	}

	public function getResponsibleId()
	{
		$entity = $this->getEntity();
		return isset($entity['ASSIGNED_BY_ID']) ? (int)$entity['ASSIGNED_BY_ID'] : 0;
	}

	public function setEntityById($id)
	{
		$id = (int)$id;
		if ($id > 0)
		{
			$entity = \CCrmLead::GetByID($id, false);
			if ($entity)
				$this->setEntity($entity);
		}
	}

	public function getEntityStatus()
	{
		$entity = $this->getEntity();
		return isset($entity['STATUS_ID']) ? $entity['STATUS_ID'] : '';
	}

	public function setEntityStatus($statusId)
	{
		$id = $this->getEntityId();

		$fields = array('STATUS_ID' => $statusId);
		$CCrmLead = new \CCrmLead(false);
		$CCrmLead->Update($id, $fields, true, true, array(
			'DISABLE_USER_FIELD_CHECK' => true,
			'REGISTER_SONET_EVENT' => true
		));

		$this->setEntityField('STATUS_ID', $statusId);
	}

	public function getEntityStatuses()
	{
		if ($this->entityStatuses === null)
		{
			$this->entityStatuses = array_keys(\CCrmStatus::GetStatusList('STATUS'));
		}

		return $this->entityStatuses;
	}
}