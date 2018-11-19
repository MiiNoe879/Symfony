<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

class CBPCrmChangeStatusActivity extends CBPActivity
{
	private static $counter = array();

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"TargetStatus" => null,
		);
	}

	public function Execute()
	{
		if ($this->TargetStatus == null || !CModule::IncludeModule("crm"))
			return CBPActivityExecutionStatus::Closed;

		$documentId = $this->GetDocumentId();
		$targetStatus = (string)$this->TargetStatus;

		//check recursion
		if (!isset(static::$counter[$documentId[2]]))
			static::$counter[$documentId[2]] = array();
		if (!isset(static::$counter[$documentId[2]][$targetStatus]))
			static::$counter[$documentId[2]][$targetStatus] = 0;

		++static::$counter[$documentId[2]][$targetStatus];

		if (static::$counter[$documentId[2]][$targetStatus] > 2)
		{
			CBPDocument::TerminateWorkflow(
				$this->GetWorkflowInstanceId(),
				$documentId,
				$arErrorsTmp,
				GetMessage('CRM_CHANGE_STATUS_RECURSION')
			);

			//Stop running queue
			throw new Exception("TerminateWorkflow");
		}
		// end check recursion

		list ($entityTypeName, $entityId) = explode('_', $documentId[2]);
		$fields = null;

		$automationTarget = null;

		switch ($entityTypeName)
		{
			case \CCrmOwnerType::DealName:
				$fields = array('STAGE_ID' => $targetStatus);
				$automationTarget = \Thurly\Crm\Automation\Factory::createTarget(\CCrmOwnerType::Deal);
				break;

			case \CCrmOwnerType::LeadName:
				$fields = array('STATUS_ID' => $targetStatus);
				$automationTarget = \Thurly\Crm\Automation\Factory::createTarget(\CCrmOwnerType::Lead);
				break;
		}

		if ($fields && $automationTarget && $entityId > 0)
		{
			$automationTarget->setEntityById($entityId);

			$runtime = CBPRuntime::GetRuntime();
			/** @var CBPDocumentService $ds */
			$ds = $runtime->GetService('DocumentService');

			$ds->UpdateDocument($documentId, $fields);
		}

		CBPDocument::TerminateWorkflow(
			$this->GetWorkflowInstanceId(),
			$documentId,
			$arErrorsTmp,
			GetMessage('CRM_CHANGE_STATUS_TERMINATED')
		);

		//Stop running queue
		throw new Exception("TerminateWorkflow");

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (empty($arTestProperties["TargetStatus"]))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "TargetStatus", "message" => GetMessage("CRM_CHANGE_STATUS_EMPTY_PROP"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
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

		$dialog->setMapCallback(array(__CLASS__, 'getPropertiesDialogMap'));

		return $dialog;
	}

	/**
	 * @param \Thurly\Bizproc\Activity\PropertiesDialog $dialog
	 * @return array Map.
	 */
	public static function getPropertiesDialogMap($dialog)
	{
		if (!CModule::IncludeModule('crm'))
			return array();

		$documentStatuses = array();
		$documentType = $dialog->getDocumentType();
		$documentType = $documentType[2];
		$context = $dialog->getContext();
		$categoryId = isset($context['ENTITY_CATEGORY_ID']) ? (int)$context['ENTITY_CATEGORY_ID'] : null;
		$fieldName = '';

		switch ($documentType)
		{
			case \CCrmOwnerType::DealName:
				if ($categoryId !== null)
					$documentStatuses = \Thurly\Crm\Category\DealCategory::getStageList($categoryId);
				else
					$documentStatuses = \Thurly\Crm\Category\DealCategory::getFullStageList();
				$fieldName = GetMessage('CRM_CHANGE_STATUS_STAGE');
				break;

			case \CCrmOwnerType::LeadName:
				$documentStatuses = CCrmStatus::GetStatusList('STATUS');
				$fieldName = GetMessage('CRM_CHANGE_STATUS_STATUS');
				break;
		}

		return array(
			'TargetStatus' => array(
				'Name' => $fieldName,
				'FieldName' => 'target_status',
				'Type' => 'select',
				'Required' => true,
				'Options' => $documentStatuses
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = Array();

		$arProperties = array(
			'TargetStatus' => $arCurrentValues['target_status']
		);

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}