<?php
namespace Thurly\Crm;

use Thurly\Main\ArgumentException;
use Thurly\Crm\Activity\BindingSelector;
use Thurly\Crm\Integrity\ActualEntitySelector;
use Thurly\Crm\Merger;
use Thurly\Crm\Automation;


/**
 * Class EntityManageFacility
 * @package Thurly\Crm
 */
class EntityManageFacility
{
	const TYPE_DEF = 'def';
	const TYPE_MAIL = 'mail';
	const TYPE_CALL = 'call';
	const TYPE_TRACKER = 'tracker';

	const UPDATE_MODE_NONE = 0;
	const UPDATE_MODE_MERGE = 1; // merge all: contact & company
	const UPDATE_MODE_REPLACE = 2; // replace all: contact & company

	/** @var ActualEntitySelector|null  */
	protected $selector;

	protected $errors = array();

	protected $bindings = null;

	protected $registeredId = null;
	protected $registeredTypeId = null;

	protected $isRCLeadAdded = false;

	protected $updateClientMode = self::UPDATE_MODE_MERGE;

	/**
	 * Create by fields and type.
	 *
	 * @param string $type Type
	 * @param array $fields Fields
	 *  <li>'NAME' => 'Mike',
	 *  <li>'SECOND_NAME' => 'Julio',
	 *  <li>'LAST_NAME' => 'Johnson',
	 *  <li>'COMPANY_TITLE' => 'Example company name',
	 *  <li>'FM' => array(
	 *  <li>   'EMAIL' => array(array('VALUE' => 'name@example.com')),
	 *  <li>   'PHONE' => array(array('VALUE' => '+98765432100')),
	 *  <li>)
	 * @return static
	 * @throws ArgumentException
	 */
	public static function create($type, array $fields)
	{
		switch ($type)
		{
			case self::TYPE_DEF:
				$searchParameters = array(
					ActualEntitySelector::SEARCH_PARAM_PHONE,
					ActualEntitySelector::SEARCH_PARAM_EMAIL,
					ActualEntitySelector::SEARCH_PARAM_PERSON,
					ActualEntitySelector::SEARCH_PARAM_ORGANIZATION
				);
				break;
			case self::TYPE_TRACKER:
				$searchParameters = array(
					ActualEntitySelector::SEARCH_PARAM_PHONE,
					ActualEntitySelector::SEARCH_PARAM_EMAIL,
					ActualEntitySelector::SEARCH_PARAM_PERSON
				);
				break;
			case self::TYPE_CALL:
				$searchParameters = array(
					ActualEntitySelector::SEARCH_PARAM_PHONE
				);
				break;
			case self::TYPE_MAIL:
				$searchParameters = array(
					ActualEntitySelector::SEARCH_PARAM_EMAIL
				);
				break;
			default:
				throw new ArgumentException("Wrong type {$type}");
		}

		$selector = ActualEntitySelector::create($fields, $searchParameters);
		return new static($selector);
	}

	/**
	 * Constructor.
	 *
	 * EntityManageFacility constructor.
	 * @param ActualEntitySelector|null $selector
	 */
	public function __construct(ActualEntitySelector $selector = null)
	{
		if (!$selector)
		{
			$selector = new ActualEntitySelector();
		}

		$this->selector = $selector;

		$this->setUpdateClientMode(self::UPDATE_MODE_MERGE);
	}

	/**
	 * Get entity selector.
	 *
	 * @return ActualEntitySelector
	 */
	public function getSelector()
	{
		return $this->selector;
	}

	/**
	 * Get bindings.
	 *
	 * @return array
	 */
	public function getActivityBindings()
	{
		if (!is_array($this->bindings))
		{
			$this->bindings = BindingSelector::findBindings($this->selector);
		}

		$bindings = $this->bindings;
		if ($this->registeredId)
		{
			$bindings[] = array(
				'OWNER_TYPE_ID' => $this->registeredTypeId,
				'OWNER_ID' => $this->registeredId
			);

			$bindings = BindingSelector::sortBindings($bindings);
		}

		return $bindings;
	}

	/**
	 * Add entity if it need. Update client fields if it need.
	 *
	 * @param int $entityTypeId Entity Type Id
	 * @param array $fields Fields
	 * @param bool $updateSearch is update search needed
	 * @param array $options Options
	 * @return int|null
	 * @throws ArgumentException When try to use unsupported entity type id
	 */
	public function registerTouch($entityTypeId, array &$fields, $updateSearch = true, $options = array())
	{
		switch ($entityTypeId)
		{
			case \CCrmOwnerType::Lead:
				$this->registerLead($fields, $updateSearch, $options);
				break;
			case \CCrmOwnerType::Contact:
				$this->registerContact($fields, $updateSearch, $options);
				break;
			case \CCrmOwnerType::Company:
				$this->registerCompany($fields, $updateSearch, $options);
				break;
			default:
				throw new ArgumentException("Unsupported Entity Type Id: {$entityTypeId}");
		}

		return $this->registeredId;
	}

	/**
	 * Add lead if it need. Update client fields if it need.
	 *
	 * @param array $fields Fields
	 * @param bool $updateSearch is update search needed
	 * @param array $options Options
	 * @return int|null
	 */
	public function registerLead(array &$fields, $updateSearch = true, $options = array())
	{
		$this->registeredId = null;
		$this->registeredTypeId = \CCrmOwnerType::Lead;

		if ($this->canAddLead())
		{
			$this->registeredId = $this->addLead($fields, $updateSearch, $options);
		}
		else
		{
			$this->updateClientByLeadFields($fields);
		}

		$this->runAutomation();
		return $this->registeredId;
	}

	/**
	 * Add company if it need. Update client fields if it need.
	 *
	 * @param array $fields Fields
	 * @param bool $updateSearch is update search needed
	 * @param array $options Options
	 * @return int|null
	 */
	public function registerCompany(array &$fields, $updateSearch = true, $options = array())
	{
		$this->registeredId = null;
		$this->registeredTypeId = \CCrmOwnerType::Company;

		if ($this->canAddCompany())
		{
			if (!isset($fields['TITLE']) || !$fields['TITLE'])
			{
				$fields['TITLE'] = (isset($fields['COMPANY_TITLE']) && $fields['COMPANY_TITLE']) ? $fields['COMPANY_TITLE'] : '';
			}

			$company = new \CCrmCompany(false);
			$this->registeredId = $company->add($fields, $updateSearch, $options);
			if (!$this->registeredId)
			{
				$this->errors[] = $company->LAST_ERROR;
			}
		}

		$this->updateClientByLeadFields($fields);
		$this->runAutomation();

		return $this->registeredId;
	}

	/**
	 * Add contact if it need. Update client fields if it need.
	 *
	 * @param array $fields Fields
	 * @param bool $updateSearch is update search needed
	 * @param array $options Options
	 * @return int|null
	 */
	public function registerContact(array &$fields, $updateSearch = true, $options = array())
	{
		$this->registeredId = null;
		$this->registeredTypeId = \CCrmOwnerType::Contact;

		if ($this->canAddContact())
		{
			$contact = new \CCrmContact(false);
			$this->registeredId = $contact->add($fields, $updateSearch, $options);
			if (!$this->registeredId)
			{
				$this->errors[] = $contact->LAST_ERROR;
			}
		}

		$this->updateClientByLeadFields($fields);
		$this->runAutomation();

		return $this->registeredId;
	}

	/**
	 * Return true if can add lead.
	 *
	 * @return bool
	 */
	public function canAddLead()
	{
		return $this->selector->canCreateLead() || $this->selector->canCreateReturnCustomerLead();
	}

	/**
	 * Return true if can add company.
	 *
	 * @return bool
	 */
	public function canAddCompany()
	{
		return !$this->selector->hasEntities();
	}

	/**
	 * Return true if can add contact.
	 *
	 * @return bool
	 */
	public function canAddContact()
	{
		return !$this->selector->hasEntities();
	}

	/**
	 * Add lead. It can create regular lead or return customer lead.
	 * And if RC-lead created, update client fields.
	 *
	 * @param array $fields Fields
	 * @param bool $updateSearch is update search needed
	 * @param array $options Options
	 * @return int|null
	 */
	public function addLead(array &$fields, $updateSearch = true, $options = array())
	{
		$this->clearErrors();

		if (!$this->canAddLead())
		{
			return null;
		}

		$isRCLeadAdded = false;
		if ($this->selector->canCreateReturnCustomerLead())
		{
			if ($this->selector->getCompanyId())
			{
				$fields['COMPANY_ID'] = $this->selector->getCompanyId();
			}
			if ($this->selector->getContactId())
			{
				$fields['CONTACT_ID'] = $this->selector->getContactId();
			}

			$fields['IS_RETURN_CUSTOMER'] = 'Y';
			$isRCLeadAdded = true;
		}
		else
		{
			$fields['IS_RETURN_CUSTOMER'] = 'N';
		}

		$updateClientFields = $fields;


		$lead = new \CCrmLead(false);
		$leadId = $lead->add($fields, $updateSearch, $options);
		if ($leadId)
		{
			$this->isRCLeadAdded = $isRCLeadAdded;
		}
		else
		{
			$this->errors[] = $lead->LAST_ERROR;
		}

		if ($leadId && $this->isRCLeadAdded)
		{
			$this->updateClientByLeadFields($updateClientFields);
		}

		return $leadId;
	}

	protected function runAutomation()
	{
		if (!$this->registeredId || !$this->registeredTypeId)
		{
			return;
		}

		// run business process
		$bpErrors = array();
		\CCrmBizProcHelper::AutoStartWorkflows(
			$this->registeredId,
			$this->registeredTypeId,
			\CCrmBizProcEventType::Create,
			$bpErrors
		);

		// run automation
		Automation\Factory::runOnAdd(
			$this->registeredId,
			$this->registeredTypeId
		);
	}

	protected function clearErrors()
	{
		return $this->errors = array();
	}

	/**
	 * Return true if there is no error.
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return count($this->errors) > 0;
	}
	/**
	 * Get error messages.
	 *
	 * @return array
	 */
	public function getErrorMessages()
	{
		return $this->errors;
	}

	/**
	 * Get update client fields mode.
	 *
	 * @return int
	 */
	public function getUpdateClientMode()
	{
		return $this->updateClientMode;
	}

	/**
	 * Set update client fields mode.
	 *
	 * @param $mode
	 * @throws \Thurly\Main\ArgumentException
	 */
	public function setUpdateClientMode($mode)
	{
		if (!in_array($mode, array(self::UPDATE_MODE_NONE, self::UPDATE_MODE_MERGE)))
		{
			throw new ArgumentException("Mode {$mode} not implemented.");
		}

		$this->updateClientMode = $mode;
	}

	/**
	 * Update contact and company by lead fields.
	 *
	 * @param array $fields Lead fields
	 */
	protected function updateClientByLeadFields(array $fields)
	{
		$mergeItems = array();
		if ($this->selector->getCompanyId())
		{
			$mergeItemFields = array();
			if (isset($fields['COMPANY_TITLE']) && $fields['COMPANY_TITLE'])
			{
				$mergeItemFields['TITLE'] = $fields['COMPANY_TITLE'];
			}

			if (!$this->selector->getContactId())
			{
				if (isset($fields['FM']))
				{
					$mergeItemFields['FM'] = $fields['FM'];
				}
			}

			if (!empty($mergeItemFields))
			{
				$mergeItems[] = array(
					'typeId' => \CCrmOwnerType::Company,
					'id' => $this->selector->getCompanyId(),
					'fields' => $mergeItemFields
				);
			}
		}

		if ($this->selector->getContactId())
		{
			$mergeItemFields = $fields;
			$customerFields = \CCrmLead::getCustomerFields();
			foreach ($mergeItemFields as $fieldName => $fieldValue)
			{
				if (in_array($fieldName, $customerFields))
				{
					continue;
				}

				unset($mergeItemFields[$fieldName]);
			}
			unset($mergeItemFields['COMPANY_TITLE']);

			if (!empty($mergeItemFields))
			{
				$mergeItems[] = array(
					'typeId' => \CCrmOwnerType::Contact,
					'id' => $this->selector->getContactId(),
					'fields' => $mergeItemFields
				);
			}
		}

		switch ($this->updateClientMode)
		{
			case self::UPDATE_MODE_MERGE:

				foreach ($mergeItems as $mergeItem)
				{
					if ($mergeItem['typeId'] == \CCrmOwnerType::Company)
					{
						$entityObject = new \CCrmCompany(false);
						$merger = new Merger\CompanyMerger(0, false);
					}
					elseif ($mergeItem['typeId'] == \CCrmOwnerType::Contact)
					{
						$entityObject = new \CCrmContact(false);
						$merger = new Merger\ContactMerger(0, false);
					}
					else
					{
						continue;
					}

					$entityTypeId = $mergeItem['typeId'];
					$entityId = $mergeItem['id'];
					$mergeFields = $mergeItem['fields'];

					$entityMultiFields = array();
					$multiFields = \CCrmFieldMulti::getEntityFields(
						\CCrmOwnerType::resolveName($entityTypeId),
						$entityId,
						null
					);
					foreach($multiFields as $multiField)
					{
						$entityMultiFields[$multiField['TYPE_ID']] = array(
							$multiField['ID'] => array(
								'VALUE' => $multiField['VALUE'],
								'VALUE_TYPE' => $multiField['VALUE_TYPE'],
							)
						);
					}

					$entityFieldsDb = $entityObject->getListEx(
						array(),
						array(
							'=ID' => $entityId,
							'CHECK_PERMISSIONS' => 'N'
						),
						false,
						false,
						array('*', 'UF_*')
					);
					$entityFields = $entityFieldsDb->fetch();
					if ($entityFields)
					{
						$entityFields['FM'] = $entityMultiFields;
						$merger->mergeFields($mergeFields, $entityFields, false, array('ENABLE_UPLOAD' => true));

						$entityObject->update($entityId, $entityFields);
					}
				}

				break;
		}
	}
}