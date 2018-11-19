<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Thurly\Crm\Integration\SmsManager;

class CBPCrmSendSmsActivity extends CBPActivity
{
	const RECIPIENT_TYPE_ENTITY = 'entity';
	const RECIPIENT_TYPE_USER = 'user';

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"ProviderId" => '', // Means "Sender Id" in messageservice module.
			"MessageFrom" => '',
			"MessageText" => '',
			"RecipientType" => static::RECIPIENT_TYPE_ENTITY,
			"RecipientUser" => null
		);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("crm"))
			return CBPActivityExecutionStatus::Closed;

		$messageText = $this->MessageText;
		if (!$messageText || !is_scalar($messageText))
		{
			$this->WriteToTrackingService(GetMessage("CRM_SSMSA_EMPTY_TEXT"), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		list($phoneNumber, $recipientUserId) = $this->getPhoneNumber();
		if (!$phoneNumber)
		{
			$this->WriteToTrackingService(GetMessage("CRM_SSMSA_EMPTY_PHONE_NUMBER"), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$providerId = (string)$this->ProviderId;
		$messageFrom = (string)$this->MessageFrom;

		//compatibility for REST providers
		if (strpos($providerId, '|') !== false)
		{
			$messageFrom = $providerId;
			$providerId = 'rest';
		}

		if ($providerId === 'rest')
		{
			$sendResult = $this->sendByRest($messageFrom, $phoneNumber, $messageText);
		}
		else
		{
			$sendResult = $this->sendByProvider($providerId, $messageFrom, $phoneNumber, $messageText);
		}

		if ($sendResult)
		{
			$messageId = is_int($sendResult) ? $sendResult : null;

			$this->WriteToTrackingService(GetMessage("CRM_SSMSA_SEND_RESULT_TRUE", array(
				'#PHONE#' => $phoneNumber
			)));

			$documentId = $this->GetDocumentId();
			list($typeName, $id) = explode('_', $documentId[2]);
			$responsibleId = CCrmOwnerType::GetResponsibleID(\CCrmOwnerType::ResolveID($typeName), $id, false);
			\Thurly\Crm\Activity\Provider\Sms::addActivity(array(
				'AUTHOR_ID' => $responsibleId,
				'OWNER_TYPE_ID' => \CCrmOwnerType::ResolveID($typeName),
				'OWNER_ID' => $id,
				'DESCRIPTION' => $messageText,
				'ASSOCIATED_ENTITY_ID' => $messageId,
				'PROVIDER_PARAMS' => array(
					'sender' => 'robot',
					'recipient_user_id' => $recipientUserId
				),
				'COMMUNICATIONS' => array(array(
					'ENTITY_ID' => $id,
					'ENTITY_TYPE_ID' => \CCrmOwnerType::ResolveID($typeName),
					'ENTITY_TYPE' => $typeName,
					'TYPE' => \CCrmFieldMulti::PHONE,
					'VALUE' => $phoneNumber
				))
			), false);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (empty($arTestProperties["ProviderId"]))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "ProviderId", "message" => GetMessage("CRM_SSMSA_EMPTY_PROVIDER"));
		}

		if (empty($arTestProperties["MessageText"]))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageText", "message" => GetMessage("CRM_SSMSA_EMPTY_TEXT"));
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

		$providers = self::getProvidersList();

		$dialog->setMap(array(
			'MessageText' => array(
				'Name' => GetMessage('CRM_SSMSA_MESSAGE_TEXT'),
				'FieldName' => 'message_text',
				'Type' => 'text',
				'Required' => true
			),
			'ProviderId' => array(
				'Name' => GetMessage('CRM_SSMSA_PROVIDER'),
				'FieldName' => 'provider_id',
				'Type' => 'select',
				'Required' => true,
				'Options' => static::makeProvidersSelectOptions($providers)
			),
			'MessageFrom' => array(
				'Name' => GetMessage('CRM_SSMSA_MESSAGE_FROM'),
				'FieldName' => 'message_from',
				'Type' => 'string'
			),
			'RecipientType' => array(
				'Name' => GetMessage('CRM_SSMSA_RECIPIENT_TYPE'),
				'FieldName' => 'recipient_type',
				'Type' => 'select',
				'Required' => true,
				'Options' => array(
					static::RECIPIENT_TYPE_ENTITY => GetMessage('CRM_SSMSA_RECIPIENT_TYPE_ENTITY'),
					static::RECIPIENT_TYPE_USER => GetMessage('CRM_SSMSA_RECIPIENT_TYPE_USER')
				)
			),
			'RecipientUser' => array(
				'Name' => GetMessage('CRM_SSMSA_RECIPIENT_USER'),
				'FieldName' => 'recipient_user',
				'Type' => 'user',
				'Default' => 'author'
			)
		));

		$dialog->setRuntimeData(array('providers' => $providers));

		//fix old values
		$values = $dialog->getCurrentValues();
		if (!empty($values['provider_id']) && strpos($values['provider_id'], '|') !== false)
		{
			$values['message_from'] = $values['provider_id'];
			$values['provider_id'] = 'rest';
			$dialog->setCurrentValues($values);
		}

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = Array();

		$arProperties = array(
			'MessageText' => (string)$arCurrentValues["message_text"],
			'ProviderId' => (string)$arCurrentValues["provider_id"],
			'MessageFrom' => (string)$arCurrentValues["message_from"],
			'RecipientType' => (string)$arCurrentValues["recipient_type"],
			'RecipientUser' => CBPHelper::UsersStringToArray($arCurrentValues["recipient_user"], $documentType, $arErrors)
		);

		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	private function getPhoneNumber()
	{
		$phoneNumber = null;
		$userId = 0;
		$recipientType = $this->RecipientType == static::RECIPIENT_TYPE_USER ?
			static::RECIPIENT_TYPE_USER : static::RECIPIENT_TYPE_ENTITY;

		if ($recipientType === static::RECIPIENT_TYPE_ENTITY)
		{
			$documentId = $this->GetDocumentId();
			list($typeName, $id) = explode('_', $documentId[2]);
			$phoneNumber = $this->getEntityPhoneNumber(\CCrmOwnerType::ResolveID($typeName), $id);
		}
		else
		{
			list($phoneNumber, $userId) = $this->getUserPhoneNumber($this->RecipientUser);
		}
		return array($phoneNumber, $userId);
	}

	private function getEntityPhoneNumber($typeId, $id)
	{
		if ($typeId == \CCrmOwnerType::Deal)
		{
			$communications = $this->getDealCommunications($id);
		}
		else
		{
			$communications = $this->getCommunicationsFromFM($typeId, $id);
		}
		$communications = array_slice($communications, 0, 1);
		return $communications? $communications[0]['VALUE'] : null;
	}

	private function getUserPhoneNumber($user)
	{
		$phoneNumber = null;
		$userId = CBPHelper::ExtractUsers($user, $this->GetDocumentId(), true);
		if ($userId > 0)
		{
			$result = CUser::GetByID($userId);
			$user = $result->fetch();
			if (!empty($user['PERSONAL_MOBILE']) && \NormalizePhone($user['PERSONAL_MOBILE']) !== false)
			{
				$phoneNumber = $user['PERSONAL_MOBILE'];
			}
			elseif (!empty($user['WORK_PHONE']) && \NormalizePhone($user['WORK_PHONE']) !== false)
			{
				$phoneNumber = $user['WORK_PHONE'];
			}
		}
		return array($phoneNumber, $userId);
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
			$communications = $this->getCommunicationsFromFM(CCrmOwnerType::Contact, $entityContactID);
		}

		if (empty($communications) && $entityCompanyID > 0)
		{
			$communications = $this->getCommunicationsFromFM(CCrmOwnerType::Company, $entityCompanyID);
		}

		return $communications;
	}

	private function getCommunicationsFromFM($entityTypeId, $entityId)
	{
		$entityTypeName = CCrmOwnerType::ResolveName($entityTypeId);
		$communications = array();

		$iterator = CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array('ENTITY_ID' => $entityTypeName,
				  'ELEMENT_ID' => $entityId,
				  'TYPE_ID' => 'PHONE'
			)
		);

		while ($row = $iterator->fetch())
		{
			if (empty($row['VALUE']))
				continue;

			$communications[] = array(
				'ENTITY_ID' => $entityId,
				'ENTITY_TYPE_ID' => $entityTypeId,
				'ENTITY_TYPE' => $entityTypeName,
				'TYPE' => 'PHONE',
				'VALUE' => $row['VALUE'],
				'VALUE_TYPE' => $row['VALUE_TYPE']
			);
		}

		return $communications;
	}

	private static function getProvidersList()
	{
		if (!SmsManager::canUse())
		{
			return static::getProvidersListOld();
		}

		$result = array();

		foreach (SmsManager::getSenderInfoList(true) as $sender)
		{
			$providerData = array(
				'IS_INTERNAL' => $sender['isConfigurable'],
				'ID'          => $sender['id'],
				'NAME'        => $sender['name'],
				'CAN_USE'     => $sender['canUse'],
				'FROM_LIST'   => $sender['fromList']
			);

			if ($sender['isConfigurable'])
			{
				$providerData['IS_DEMO'] = $sender['isDemo'];
				$providerData['MANAGE_URL'] = $sender['manageUrl'];
			}
			$result[] = $providerData;
		}

		return $result;
	}

	private static function makeProvidersSelectOptions(array $providers)
	{
		$options = array();
		foreach ($providers as $provider)
		{
			$options[$provider['ID']] = $provider['NAME'];
		}
		return $options;
	}

	private static function getProvidersListOld()
	{
		$result = array();

		$ormResult = \Thurly\Bizproc\RestProviderTable::getList(array(
			'select' => array('APP_ID', 'CODE', 'NAME', 'APP_NAME'),
			'order' => array('APP_NAME' => 'ASC', 'NAME' => 'ASC')
		));

		while ($row = $ormResult->fetch())
		{
			$result[] = array(
				'id' => $row['APP_ID'].'|'.$row['CODE'],
				'name' => sprintf('[%s] %s',
					\Thurly\Bizproc\RestProviderTable::getLocalization($row['APP_NAME'], LANGUAGE_ID),
					\Thurly\Bizproc\RestProviderTable::getLocalization($row['NAME'], LANGUAGE_ID)
				)
			);
		}

		return array(
			array(
				'IS_INTERNAL' => false,
				'ID' => 'rest',
				'NAME' => 'REST',
				'FROM_LIST' => $result
			)
		);
	}

	private function sendByRest($from, $phoneNumber, $messageText)
	{
		if (!SmsManager::canUse())
		{
			return static::sendByRestOld($from, $phoneNumber, $messageText);
		}

		$documentId = $this->GetDocumentId();
		list($typeName, $id) = explode('_', $documentId[2]);
		$authorId = \CCrmOwnerType::GetResponsibleID(\CCrmOwnerType::ResolveID($typeName), $id, false);

		$result = SmsManager::sendMessage(array(
			'SENDER_ID' => 'rest',
			'AUTHOR_ID' => $authorId,
			'MESSAGE_FROM' => $from,
			'MESSAGE_TO' => $phoneNumber,
			'MESSAGE_BODY' => $messageText,
			'MESSAGE_HEADERS' => array(
				'module_id' => 'bizproc',
				'workflow_id' => $this->getWorkflowInstanceId(),
				'document_id' => $documentId,
				'document_type' => $this->GetDocumentType(),
				'properties' => array(
					'phone_number' => $phoneNumber,
					'message_text' => $messageText,
				)
			)
		));

		if (!$result->isSuccess())
		{
			$errorMessages = $result->getErrorMessages();
			foreach ($errorMessages as $message)
			{
				$this->WriteToTrackingService($message, 0, CBPTrackingType::Error);
			}
			return false;
		}

		return $result->getId();
	}

	private function sendByProvider($senderId, $messageFrom, $phoneNumber, $messageText)
	{
		if (!SmsManager::canUse())
		{
			$this->WriteToTrackingService(GetMessage('CRM_SSMSA_NO_MESSAGESERVICE'), 0, CBPTrackingType::Error);
			return false;
		}

		$documentId = $this->GetDocumentId();
		list($typeName, $id) = explode('_', $documentId[2]);
		$authorId = \CCrmOwnerType::GetResponsibleID(\CCrmOwnerType::ResolveID($typeName), $id, false);

		$result = SmsManager::sendMessage(array(
			'SENDER_ID' => $senderId,
			'AUTHOR_ID' => $authorId,
			'MESSAGE_FROM' => $messageFrom ?: null,
			'MESSAGE_TO' => $phoneNumber,
			'MESSAGE_BODY' => $messageText,
		));

		if (!$result->isSuccess())
		{
			$errorMessages = $result->getErrorMessages();
			foreach ($errorMessages as $message)
			{
				$this->WriteToTrackingService($message, 0, CBPTrackingType::Error);
			}
			return false;
		}

		return $result->getId();
	}

	private function sendByRestOld($providerId, $phoneNumber, $messageText)
	{
		if (!CModule::includeModule('rest') || !\Thurly\Rest\OAuthService::getEngine()->isRegistered())
			return false;

		list($appId, $providerCode) = explode('|', $providerId);
		$provider = null;

		if ($appId && $providerCode)
		{
			$provider = \Thurly\Bizproc\RestProviderTable::getList(
				array('filter' => array('APP_ID' => $appId, 'CODE' => $providerCode))
			)->fetch();
		}

		if (!$provider)
		{
			$this->WriteToTrackingService(GetMessage("CRM_SSMSA_NO_PROVIDER"), 0, CBPTrackingType::Error);
			return false;
		}

		$session = \Thurly\Rest\Event\Session::get();
		if(!$session)
		{
			$this->WriteToTrackingService(GetMessage("CRM_SSMSA_REST_SESSION_ERROR"), 0, CBPTrackingType::Error);
			return false;
		}

		$dbRes = \Thurly\Rest\AppTable::getList(array(
			'filter' => array(
				'=CLIENT_ID' => $provider['APP_ID'],
			)
		));
		$application = $dbRes->fetch();

		if (!$application)
		{
			$this->WriteToTrackingService(GetMessage("CRM_SSMSA_NO_PROVIDER"), 0, CBPTrackingType::Error);
			return false;
		}

		$appStatus = \Thurly\Rest\AppTable::getAppStatusInfo($application, '');
		if($appStatus['PAYMENT_ALLOW'] === 'N')
		{
			$this->WriteToTrackingService(GetMessage("CRM_SSMSA_PAYMENT_REQUIRED"), 0, CBPTrackingType::Error);
			return false;
		}

		$documentId = $this->GetDocumentId();
		list($typeName, $id) = explode('_', $documentId[2]);

		$auth = array(
			'CODE' => $provider['CODE'],
			\Thurly\Rest\Event\Session::PARAM_SESSION => $session,
			\Thurly\Rest\OAuth\Auth::PARAM_LOCAL_USER => \CCrmOwnerType::GetResponsibleID(
				\CCrmOwnerType::ResolveID($typeName), $id, false
			),
			"application_token" => \CRestUtil::getApplicationToken($application),
		);

		$queryItems = array(
			\Thurly\Rest\Sqs::queryItem(
				$provider['APP_ID'],
				$provider['HANDLER'],
				array(
					'workflow_id' => $this->getWorkflowInstanceId(),
					'type' => $provider['TYPE'],
					'code' => $provider['CODE'],
					'document_id' => $this->GetDocumentId(),
					'document_type' => $this->GetDocumentType(),
					'properties' => array(
						'phone_number' => $phoneNumber,
						'message_text' => $messageText,
					),
					'ts' => time(),
				),
				$auth,
				array(
					"sendAuth" => true,
					"sendRefreshToken" => false,
					"category" => \Thurly\Rest\Sqs::CATEGORY_BIZPROC,
				)
			),
		);

		\Thurly\Rest\OAuthService::getEngine()->getClient()->sendEvent($queryItems);
		return true;
	}
}