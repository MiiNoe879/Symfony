<?php
namespace Thurly\Crm\Automation\Target;

use Thurly\Crm\Category\DealCategory;

class DealTarget extends BaseTarget
{
	protected $entityStages;

	public function getEntityTypeId()
	{
		return \CCrmOwnerType::Deal;
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
			$entity = \CCrmDeal::GetByID($id, false);
			if ($entity)
				$this->setEntity($entity);
		}
	}

	public function getEntityStatus()
	{
		$entity = $this->getEntity();
		return isset($entity['STAGE_ID']) ? $entity['STAGE_ID'] : '';
	}

	public function setEntityStatus($statusId)
	{
		$id = $this->getEntityId();

		$fields = array('STAGE_ID' => $statusId);
		$CCrmDeal = new \CCrmDeal(false);
		$CCrmDeal->Update($id, $fields, true, true, array(
			'DISABLE_USER_FIELD_CHECK' => true,
			'REGISTER_SONET_EVENT' => true
		));

		$this->setEntityField('STAGE_ID', $statusId);
	}

	public function getEntityStatuses()
	{
		if ($this->entityStages === null)
		{
			$entity = $this->getEntity();
			$categoryId = isset($entity['CATEGORY_ID']) ? (int)$entity['CATEGORY_ID'] : 0;
			$this->entityStages = array_keys(DealCategory::getStageList($categoryId));
		}

		return $this->entityStages;
	}
}