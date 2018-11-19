<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

class CBPCrmCreateMeetingActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Subject" => null,
			"StartTime" => null,
			"EndTime" => null,
			"IsImportant" => null,
			"Description" => null,
			"Location" => null,
			"NotifyType" => null,
			"NotifyValue" => null,
			"Responsible" => null,
			"AutoComplete" => null,
			//return
			"Id" => null
		);

		$this->SetPropertiesTypes(array(
			'Id' => array(
				'Type' => 'int'
			)
		));
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("crm"))
			return CBPActivityExecutionStatus::Closed;

		$start = (string)$this->StartTime;
		$end = (string)$this->EndTime;

		if ($start === '')
			$start = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL');

		if ($end === '')
			$end = $start;

		$responsibleId = $this->getResponsibleId();
		/** @var \Thurly\Crm\Activity\Provider\Base $provider */
		$provider = \Thurly\Crm\Activity\Provider\Meeting::className();

		$activityFields = array(
			'AUTHOR_ID' => $responsibleId,
			'START_TIME' => $start,
			'END_TIME' => $end,
			'TYPE_ID' =>  \CCrmActivityType::Meeting,
			'SUBJECT' => (string)$this->Subject,
			'PRIORITY' => ($this->IsImportant == 'Y') ? \CCrmActivityPriority::High : CCrmActivityPriority::Medium,
			'DESCRIPTION' => (string)$this->Description,
			'DESCRIPTION_TYPE' => CCrmContentType::PlainText,
			'LOCATION' => (string)$this->Location,
			'PROVIDER_ID' => $provider::getId(),
			'PROVIDER_TYPE_ID' => $provider::getTypeId(array()),
			'RESPONSIBLE_ID' => $responsibleId,
			'AUTOCOMPLETE_RULE' => (
				$this->AutoComplete == 'Y' ?
					\Thurly\Crm\Activity\AutocompleteRule::AUTOMATION_ON_STATUS_CHANGED
					: \Thurly\Crm\Activity\AutocompleteRule::NONE
			)
		);

		$provider::fillDefaultActivityFields($activityFields);

		$notifyType = (int)$this->NotifyType;
		$notifyValue = (int)$this->NotifyValue;

		if ($notifyType > 0 && $notifyValue > 0)
		{
			$activityFields['NOTIFY_VALUE'] = $notifyValue;
			$activityFields['NOTIFY_TYPE'] = $notifyType;
		}
		else
		{
			$defaults = \CUserOptions::GetOption('crm.activity.planner', 'defaults', array(), $responsibleId);
			if (isset($defaults['notify']) && isset($defaults['notify'][$provider::getId()]))
			{
				$activityFields['NOTIFY_VALUE'] = (int)$defaults['notify'][$provider::getId()]['value'];
				$activityFields['NOTIFY_TYPE'] = (int)$defaults['notify'][$provider::getId()]['type'];
			}
		}

		$activityFields['BINDINGS'] = $this->getBindings();
		$communications = $this->getCommunications();

		if (empty($activityFields['SUBJECT']) && !empty($communications))
		{
			$arCommInfo = array(
				'ENTITY_ID' => $communications[0]['ENTITY_ID'],
				'ENTITY_TYPE_ID' => $communications[0]['ENTITY_TYPE_ID']
			);
			CCrmActivity::PrepareCommunicationInfo($arCommInfo);

			$activityFields['SUBJECT'] = \Thurly\Crm\Activity\Provider\Meeting::generateSubject(
				$activityFields['PROVIDER_TYPE_ID'],
				CCrmActivityDirection::Undefined,
				array(
					'#DATE#'=> $activityFields['START_TIME'],
					'#TITLE#' => isset($arCommInfo['TITLE']) ? $arCommInfo['TITLE'] : '',
					'#COMMUNICATION#' => ''
				)
			);
		}

		if(!($id = CCrmActivity::Add($activityFields, false, true, array('REGISTER_SONET_EVENT' => true))))
		{
			$this->WriteToTrackingService(CCrmActivity::GetLastErrorMessage(), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		if ($id > 0)
		{
			$this->Id = $id;
			CCrmActivity::SaveCommunications($id, $communications, $activityFields, false, false);
			$this->WriteToTrackingService($id, 0, CBPTrackingType::AttachedEntity);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function getResponsibleId()
	{
		$id = $this->Responsible;
		if (!$id)
		{
			$runtime = CBPRuntime::GetRuntime();
			$runtime->StartRuntime();
			/** @var CBPDocumentService $documentService */
			$documentService = $runtime->GetService('DocumentService');
			$document = $documentService->GetDocument($this->GetDocumentId());

			$id = isset($document['ASSIGNED_BY_ID']) ? $document['ASSIGNED_BY_ID'] : null;

		}

		return CBPHelper::ExtractUsers($id, $this->GetDocumentId(), true);
	}

	private function getBindings()
	{
		$documentId = $this->GetDocumentId();
		list($typeName, $id) = explode('_', $documentId[2]);

		return array(
			array(
				'OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($typeName),
				'OWNER_ID' => $id
			)
		);
	}

	private function getCommunications()
	{
		$documentId = $this->GetDocumentId();
		list($typeName, $id) = explode('_', $documentId[2]);

		if ($typeName === CCrmOwnerType::DealName)
		{
			$communications = $this->getDealCommunications($id);
			$communications = array_slice($communications, 0, 1);
		}
		else
		{
			$communications = array(array(
				'ENTITY_ID' => $id,
				'ENTITY_TYPE_ID' => CCrmOwnerType::ResolveID($typeName),
				'ENTITY_TYPE' => $typeName,
				'TYPE' => ''
			));
		}

		return $communications;
	}

	private function getDealCommunications($id)
	{
		$communications = array();

		$entity = CCrmDeal::GetByID($id, false);
		if(!$entity)
		{
			return array();
		}

		$entityContactID = isset($entity['CONTACT_ID']) ? intval($entity['CONTACT_ID']) : 0;
		$entityCompanyID = isset($entity['COMPANY_ID']) ? intval($entity['COMPANY_ID']) : 0;

		if ($entityContactID > 0)
		{
			$communications[] = array(
				'ENTITY_ID' => $entityContactID,
				'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
				'ENTITY_TYPE' => CCrmOwnerType::ContactName,
				'TYPE' => ''
			);
		}

		if (empty($communications) && $entityCompanyID > 0)
		{
			$communications = CCrmActivity::GetCompanyCommunications($entityCompanyID, '');
		}

		if (empty($communications))
		{
			$communications = CCrmActivity::GetCommunicationsByOwner('DEAL', $id, '');
			foreach ($communications as $key => $communication)
			{
				$communications[$key]['VALUE'] = (string)$communications[$key]['VALUE'];
			}
		}

		return $communications;
	}

	public static function ValidateProperties($testProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$errors = array();
		if (CModule::IncludeModule("crm"))
		{
			$fieldsMap = static::getPropertiesDialogMap();
			foreach ($fieldsMap as $propertyKey => $fieldProperties)
			{
				if (
					CBPHelper::getBool($fieldProperties['Required'])
					&& CBPHelper::isEmptyValue($testProperties[$propertyKey])
				)
					$errors[] = array(
						"code" => "NotExist",
						"parameter" => $propertyKey,
						"message" => GetMessage("CRM_CREATE_MEETING_EMPTY_PROP", array('#PROPERTY#' => $fieldProperties['Name']))
					);
			}
		}

		return array_merge($errors, parent::ValidateProperties($testProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null, $siteId = '')
	{
		if (!CModule::IncludeModule("crm"))
			return '';

		$dialog = new \Thurly\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId
		));

		$dialog->setMap(static::getPropertiesDialogMap());

		return $dialog;
	}

	private static function getPropertiesDialogMap()
	{
		$notifyTypes = \CCrmActivityNotifyType::GetAllDescriptions();
		unset($notifyTypes[\CCrmActivityNotifyType::None]);

		return array(
			'Subject' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_SUBJECT'),
				'FieldName' => 'subject',
				'Type' => 'string'
			),
			'StartTime' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_START_TIME'),
				'FieldName' => 'start_time',
				'Type' => 'datetime'
			),
			'EndTime' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_END_TIME'),
				'FieldName' => 'end_time',
				'Type' => 'datetime'
			),
			'Description' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_DESCRIPTION'),
				'FieldName' => 'description',
				'Type' => 'text'
			),
			'Location' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_LOCATION'),
				'FieldName' => 'location',
				'Type' => 'string'
			),
			'NotifyValue' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_NOTIFY_VALUE'),
				'FieldName' => 'notify_value',
				'Type' => 'int'
			),
			'NotifyType' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_NOTIFY_TYPE'),
				'FieldName' => 'notify_type',
				'Type' => 'select',
				'Options' => $notifyTypes
			),
			'Responsible' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_RESPONSIBLE_ID'),
				'FieldName' => 'responsible',
				'Type' => 'user',
				'Default' => 'author'
			),
			'IsImportant' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_IS_IMPORTANT'),
				'FieldName' => 'is_important',
				'Type' => 'bool'
			),
			'AutoComplete' => array(
				'Name' => GetMessage('CRM_CREATE_MEETING_AUTO_COMPLETE'),
				'FieldName' => 'auto_completed',
				'Type' => 'bool',
				'Default' => 'N'
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $currentValues, &$errors)
	{
		if (!CModule::IncludeModule("crm"))
			return false;

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();

		$errors = $properties = array();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService('DocumentService');

		$fieldsMap = static::getPropertiesDialogMap();
		foreach ($fieldsMap as $propertyKey => $fieldProperties)
		{
			$field = $documentService->getFieldTypeObject($documentType, $fieldProperties);
			if (!$field)
				continue;

			$properties[$propertyKey] = $field->extractValue(
				array('Field' => $fieldProperties['FieldName']),
				$currentValues,
				$errors
			);
		}

		//convert special robot datetime interval
		$startTimeFieldsPrefix = $fieldsMap['StartTime']['FieldName'];
		if (isset($currentValues[$startTimeFieldsPrefix.'_interval_d']) && isset($currentValues[$startTimeFieldsPrefix.'_interval_t']))
		{
			$interval = array('d' => $currentValues[$startTimeFieldsPrefix.'_interval_d']);
			$time = \Thurly\Crm\Automation\Helper::parseTimeString($currentValues[$startTimeFieldsPrefix.'_interval_t']);
			$interval['h'] = $time['h'];
			$interval['i'] = $time['i'];
			$properties['StartTime'] = \Thurly\Crm\Automation\Helper::getDateTimeIntervalString($interval);
			++$interval['h'];
			$properties['EndTime'] = \Thurly\Crm\Automation\Helper::getDateTimeIntervalString($interval);
		}

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
			return false;

		$currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$currentActivity['Properties'] = $properties;

		return true;
	}
}