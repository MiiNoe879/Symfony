<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

class CBPCrmSendEmailActivity extends CBPActivity
{
	const TEXT_TYPE_BBCODE = 'bbcode';
	const TEXT_TYPE_HTML = 'html';
	const ATTACHMENT_TYPE_FILE = 'file';
	const ATTACHMENT_TYPE_DISK = 'disk';

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Subject" => "",
			"From" => null,
			"MessageText" => '',
			"MessageTextType" => '',
			"MessageTextEncoded" => 0,
			'AttachmentType' => static::ATTACHMENT_TYPE_FILE,
			'Attachment' => array()
		);
	}

	public function Execute()
	{
		if (!$this->MessageText || !CModule::IncludeModule("crm") || !CModule::IncludeModule('subscribe'))
			return CBPActivityExecutionStatus::Closed;

		$documentId = $this->GetDocumentId();
		list($typeName, $ownerID) = explode('_', $documentId[2]);

		$ownerTypeID = \CCrmOwnerType::ResolveID($typeName);

		$userID = CCrmOwnerType::GetResponsibleID($ownerTypeID, $ownerID, false);
		if($userID <= 0)
		{
			$this->WriteToTrackingService(GetMessage('CRM_SEMA_NO_RESPONSIBLE'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$fromInfo = $this->getFromEmail($userID, $this->From);

		if (!$fromInfo)
		{
			$this->WriteToTrackingService(GetMessage('CRM_SEMA_NO_FROM'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$from = $fromInfo['from'];
		$userImap = $fromInfo['userImap'];
		$crmImap = $fromInfo['crmImap'];
		$injectUrn = $fromInfo['injectUrn'];
		$reply = $fromInfo['reply'];
		$fromEmail = $fromInfo['fromEmail'];
		$fromEncoded = $fromInfo['fromEncoded'];

		$to = $this->getToEmail($ownerTypeID, $ownerID);

		if (empty($to))
		{
			$this->WriteToTrackingService(GetMessage('CRM_SEMA_NO_ADDRESSER'), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$errors = array();

		// Bindings & Communications -->
		$arBindings = array(
			array(
				'OWNER_TYPE_ID' => $ownerTypeID,
				'OWNER_ID' => $ownerID
			)
		);
		$arComms = array(array(
			'TYPE' => 'EMAIL',
			'VALUE' => $to,
			'ENTITY_ID' => $ownerID,
			'ENTITY_TYPE_ID' => $ownerTypeID
		));
		// <-- Bindings & Communications

		$subject = (string)$this->Subject;
		$message = (string)$this->MessageText;
		$messageType = $this->MessageTextType;

		if ($this->MessageTextEncoded)
		{
			$message = htmlspecialcharsback($message);
		}

		if($message !== '')
		{
			CCrmActivity::AddEmailSignature($message,
				$messageType === self::TEXT_TYPE_HTML ? CCrmContentType::Html : CCrmContentType::BBCode
			);
		}

		if($message === '')
		{
			$messageHtml = '';
		}
		else
		{
			if ($messageType !== self::TEXT_TYPE_HTML)
			{
				//Convert BBCODE to HTML
				$parser = new CTextParser();
				$parser->allow['SMILES'] = 'N';
				$messageHtml = $parser->convertText($message);
			}
			else
			{
				$messageHtml = $message;
			}

			if (strpos($messageHtml, '</html>') === false)
			{
				$messageHtml = '<html><body>'.$messageHtml.'</body></html>';
			}
		}

		$now = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL');
		if($subject === '')
		{
			$subject = GetMessage(
				'CRM_SEMA_DEFAULT_SUBJECT',
				array('#DATE#'=> $now)
			);
		}

		$description = $message;

		if ($messageType === self::TEXT_TYPE_HTML)
		{
			//$description = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $description);
			$description = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $description);
			$description = preg_replace('/<title[^>]*>.*?<\/title>/is', '', $description);

			$sanitizer = new CBXSanitizer();
			$sanitizer->setLevel(CBXSanitizer::SECURE_LEVEL_LOW);
			$sanitizer->applyHtmlSpecChars(false);
			$sanitizer->addTags(array('style' => array()));
			$description = $sanitizer->SanitizeHtml($description);
		}

		$activityFields = array(
			'AUTHOR_ID' => $userID,
			'OWNER_ID' => $ownerID,
			'OWNER_TYPE_ID' => $ownerTypeID,
			'TYPE_ID' =>  CCrmActivityType::Email,
			'SUBJECT' => $subject,
			'START_TIME' => $now,
			'END_TIME' => $now,
			'COMPLETED' => 'Y',
			'RESPONSIBLE_ID' => $userID,
			'PRIORITY' => CCrmActivityPriority::Medium,
			'DESCRIPTION' => $description,
			'DESCRIPTION_TYPE' => $messageType === self::TEXT_TYPE_HTML ? CCrmContentType::Html : CCrmContentType::BBCode,
			'DIRECTION' => CCrmActivityDirection::Outgoing,
			'BINDINGS' => array_values($arBindings),
			'COMMUNICATIONS' => $arComms,
		);

		if ($this->AttachmentType === static::ATTACHMENT_TYPE_DISK)
		{
			$attachmentStorageType = Thurly\Crm\Integration\StorageType::Disk;
			$attachment = (array)$this->Attachment;
		}
		else
		{
			$attachmentStorageType = Thurly\Crm\Integration\StorageType::File;
			$attachment = array();
			$attachmentFiles = (array)$this->ParseValue($this->getRawProperty('Attachment'), 'file');
			$attachmentFiles = CBPHelper::MakeArrayFlat($attachmentFiles);
			$attachmentFiles = array_filter($attachmentFiles);

			if($attachmentFiles)
			{
				foreach ($attachmentFiles as $fileID)
				{
					$arRawFile = CFile::MakeFileArray($fileID);
					if (is_array($arRawFile))
					{
						$fileID = intval(CFile::SaveFile($arRawFile, 'crm'));
						if ($fileID > 0)
						{
							$attachment[] = $fileID;
						}
					}
				}
			}
		}

		if ($attachment)
		{
			$activityFields['STORAGE_TYPE_ID'] = $attachmentStorageType;
			$activityFields['STORAGE_ELEMENT_IDS'] = $attachment;
		}

		if(!($ID = CCrmActivity::Add($activityFields, false, false, array('REGISTER_SONET_EVENT' => true))))
		{
			$this->WriteToTrackingService(CCrmActivity::GetLastErrorMessage(), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$arRawFiles = isset($activityFields['STORAGE_ELEMENT_IDS']) && !empty($activityFields['STORAGE_ELEMENT_IDS'])
			? \Thurly\Crm\Integration\StorageManager::makeFileArray(
				$activityFields['STORAGE_ELEMENT_IDS'], $activityFields['STORAGE_TYPE_ID']
			)
			: array();

		$urn = CCrmActivity::PrepareUrn($activityFields);
		$messageId = sprintf(
			'<crm.activity.%s@%s>', $urn,
			defined('BX24_HOST_NAME') ? BX24_HOST_NAME : (
			defined('SITE_SERVER_NAME') && SITE_SERVER_NAME
				? SITE_SERVER_NAME : \COption::getOptionString('main', 'server_name', '')
			)
		);

		\CCrmActivity::update($ID, array(
			//'DESCRIPTION' => $arFields['DESCRIPTION'],
			'URN'         => $urn,
			'SETTINGS'    => array(
				'IS_BATCH_EMAIL'  => true,
				'MESSAGE_HEADERS' => array(
					'Message-Id' => $messageId,
					'Reply-To'   => $reply ?: $from,
				),
				'EMAIL_META' => array(
					'__email' => $fromEmail,
					'from'    => $from,
					'replyTo' => $reply,
					'to'      => $to,
				),
			),
		), false, false, array('REGISTER_SONET_EVENT' => true));

		// sending email
		$rcpt = array(
			\Thurly\Main\Mail\Mail::encodeHeaderFrom($to, SITE_CHARSET)
		);

		$outgoingSubject = $subject;
		$outgoingBody = $messageHtml ?: getMessage('CRM_SEMA_DEFAULT_BODY');

		if (!empty($injectUrn))
		{
			switch (\CCrmEMailCodeAllocation::getCurrent())
			{
				case \CCrmEMailCodeAllocation::Subject:
					$outgoingSubject = \CCrmActivity::injectUrnInSubject($urn, $outgoingSubject);
					break;
				case \CCrmEMailCodeAllocation::Body:
					$outgoingBody = \CCrmActivity::injectUrnInBody($urn, $outgoingBody, 'html');
					break;
			}
		}

		$attachments = array();
		foreach ($arRawFiles as $key => $item)
		{
			$attachments[] = array(
				'ID'           => $item['external_id'],
				'NAME'         => $item['ORIGINAL_NAME'] ?: $item['name'],
				'PATH'         => $item['tmp_name'],
				'CONTENT_TYPE' => $item['type'],
			);
		}

		$outgoingParams = array(
			'CHARSET'      => SITE_CHARSET,
			'CONTENT_TYPE' => 'html',
			'ATTACHMENT'   => $attachments,
			'TO'           => join(', ', $rcpt),
			'SUBJECT'      => $outgoingSubject,
			'BODY'         => $outgoingBody,
			'HEADER'       => array(
				'From'       => $fromEncoded ?: $fromEmail,
				'Reply-To'   => $reply ?: $fromEmail,
				'Message-Id' => $messageId,
			),
		);

		$trackReadLink = \Thurly\Main\Mail\Tracking::getLinkRead('crm', ['urn' => $urn]);
		if (substr($trackReadLink, 0, 4) !== 'http')
		{
			$trackReadLink = "http://" . Thurly\Main\Config\Option::get("main", "server_name", "") . $trackReadLink;
		}
		$trackReadHtml = '<img src="' . $trackReadLink . '" border="0" height="1" width="1" alt="" />';

		$postingData = array(
			'STATUS' => 'D',
			'FROM_FIELD'    => $fromEncoded ?: $fromEmail,
			'TO_FIELD' => $to,
			'SUBJECT' => $outgoingSubject,
			'BODY_TYPE' => 'html',
			'BODY' => $outgoingBody . $trackReadHtml,
			'DIRECT_SEND' => 'Y',
			'SUBSCR_FORMAT' => 'html',
			'CHARSET' => $this->getPostingCharset()
		);

		$posting = new CPosting();
		$postingID = $posting->Add($postingData);
		if($postingID === false)
		{
			$errors[] = $posting->LAST_ERROR;
		}
		else
		{
			foreach($arRawFiles as $arRawFile)
			{
				if(isset($arRawFile['ORIGINAL_NAME']))
				{
					$arRawFile['name'] = $arRawFile['ORIGINAL_NAME'];
				}
				if(!$posting->SaveFile($postingID, $arRawFile))
				{
					$arErrors[] = $posting->LAST_ERROR;
					break;
				}
			}

			if(empty($errors))
			{
				\CCrmActivity::update($ID, array(
					'ASSOCIATED_ENTITY_ID' => $postingID,
				), false, false);
			}
		}

		if(!empty($errors))
		{
			CCrmActivity::Delete($ID);
			$this->WriteToTrackingService($errors[0], 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		if($posting->ChangeStatus($postingID, 'P'))
		{
			$rsAgents = CAgent::GetList(
				array('ID'=>'DESC'),
				array(
					'MODULE_ID' => 'subscribe',
					'NAME' => 'CPosting::AutoSend('.$postingID.',%',
				)
			);

			if(!$rsAgents->Fetch())
			{
				CAgent::AddAgent('CPosting::AutoSend('.$postingID.',true);', 'subscribe', 'N', 0);
			}
		}

		if (!empty($userImap['need_sync']) || !empty($crmImap['need_sync']))
		{
			class_exists('Thurly\Mail\Helper');

			$outgoingHeader = array_merge(
				$outgoingParams['HEADER'],
				array(
					'To'      => $outgoingParams['TO'],
					'Subject' => $outgoingParams['SUBJECT'],
				)
			);

			$outgoing = new \Thurly\Mail\DummyMail(array_merge(
				$outgoingParams,
				array(
					'HEADER' => $outgoingHeader,
				)
			));

			if (!empty($userImap['need_sync']))
				\Thurly\Mail\Helper::addImapMessage($userImap, (string) $outgoing, $err);
			if (!empty($crmImap['need_sync']))
				\Thurly\Mail\Helper::addImapMessage($crmImap, (string) $outgoing, $err);
		}

		// Try add event to entity
		$CCrmEvent = new CCrmEvent();

		$eventText  = '';
		$eventText .= GetMessage('CRM_SEMA_EMAIL_SUBJECT').': '.$subject."\n\r";
		$eventText .= GetMessage('CRM_SEMA_EMAIL_FROM').': '.$from."\n\r";
		$eventText .= GetMessage('CRM_SEMA_EMAIL_TO').': '.$to."\n\r\n\r";
		$eventText .= $messageHtml;
		// Register event only for owner
		$CCrmEvent->Add(
			array(
				'ENTITY' => array(
					$ownerID => array(
						'ENTITY_TYPE' => \CCrmOwnerType::ResolveName($ownerTypeID),
						'ENTITY_ID' => $ownerID
					)
				),
				'EVENT_ID' => 'MESSAGE',
				'EVENT_TEXT_1' => $eventText,
				'FILES' => $arRawFiles
			)
		);
		// <-- Sending Email

		return CBPActivityExecutionStatus::Closed;
	}

	private function getFromEmail($userId, $from = '')
	{
		$userImap = $crmImap = $defaultFrom = null;
		$injectUrn = false;
		$reply = '';
		$from = trim((string)$from);
		$fromEmail = '';
		$fromEncoded = '';
		$crmEmail = \CCrmMailHelper::extractEmail(\COption::getOptionString('crm', 'mail', ''));

		if (CModule::includeModule('mail'))
		{
			$res = \Thurly\Mail\MailboxTable::getList(array(
				'select' => array('*', 'LANG_CHARSET' => 'SITE.CULTURE.CHARSET'),
				'filter' => array(
					'=LID'    => SITE_ID,
					'=ACTIVE' => 'Y',
					array(
						'LOGIC'    => 'OR',
						'=USER_ID' => $userId,
						array(
							'USER_ID'      => 0,
							'=SERVER_TYPE' => 'imap',
						),
					),
				),
				'order'  => array('ID' => 'DESC'),
			));

			while ($mailbox = $res->fetch())
			{
				if (!empty($mailbox['OPTIONS']['flags']) && in_array('crm_connect', (array)$mailbox['OPTIONS']['flags']))
				{
					$mailbox['EMAIL_FROM'] = null;
					if (check_email($mailbox['NAME'], true))
						$mailbox['EMAIL_FROM'] = strtolower($mailbox['NAME']);
					elseif (check_email($mailbox['LOGIN'], true))
						$mailbox['EMAIL_FROM'] = strtolower($mailbox['LOGIN']);

					if ($mailbox['USER_ID'] > 0)
						$userImap = $mailbox;
					else
						$crmImap = $mailbox;
				}
			}

			$defaultFrom = \Thurly\Mail\User::getDefaultEmailFrom();
		}

		if ($from === '')
		{
			if (!empty($userImap))
			{
				$from = $userImap['EMAIL_FROM'] ?: $defaultFrom;
				$userImap['need_sync'] = true;
			}
			elseif (!empty($crmImap))
			{
				$from = $crmImap['EMAIL_FROM'] ?: $defaultFrom;
				$crmImap['need_sync'] = true;
			}
			else
			{
				$from = $crmEmail;
			}

			if ($from === '')
				$from = CUserOptions::GetOption('crm', 'activity_email_addresser', '', $userId);

			if ($from === '')
				$from = $defaultFrom;

			$fromData = $this->parseFromString($from);
			$fromEmail = $fromData['email'];
		}
		else
		{
			$fromData = $this->parseFromString($from);
			$fromEmail = $fromData['email'];
			$fromEncoded  = $fromData['nameEncoded'];

			if (!check_email($fromEmail, true))
			{
				$from = '';
			}
			else
			{
				if (!empty($userImap['EMAIL_FROM']) && $userImap['EMAIL_FROM'] === $fromEmail)
					$userImap['need_sync'] = true;
				if (!empty($crmImap['EMAIL_FROM']) && $crmImap['EMAIL_FROM'] === $fromEmail)
					$crmImap['need_sync'] = true;

				if (empty($userImap['need_sync']) && empty($crmImap['need_sync']))
				{
					if ($crmEmail == '' || $crmEmail != $fromEmail)
					{
						if (!empty($userImap['EMAIL_FROM']))
							$reply = $fromEmail . ', ' . $userImap['EMAIL_FROM'];
						else if (!empty($crmImap['EMAIL_FROM']))
							$reply = $fromEmail . ', ' . $crmImap['EMAIL_FROM'];
						else if ($crmEmail != '')
							$reply = $fromEmail . ', ' . $crmEmail;
					}

					$injectUrn = true;
				}
			}

		}

		if (empty($from))
		{
			return false;
		}

		return array(
			'from' => $from,
			'fromEmail' => $fromEmail,
			'userImap' => $userImap,
			'crmImap' => $crmImap,
			'reply' => $reply,
			'injectUrn' => $injectUrn,
			'fromEncoded' => $fromEncoded
		);
	}

	private function getToEmail($entityTypeId, $entityId)
	{
		$to = '';

		if ($entityTypeId == \CCrmOwnerType::Deal)
		{
			$entity = \CCrmDeal::GetByID($entityId, false);
			$entityContactID = isset($entity['CONTACT_ID']) ? intval($entity['CONTACT_ID']) : 0;
			$entityCompanyID = isset($entity['COMPANY_ID']) ? intval($entity['COMPANY_ID']) : 0;

			if($entityContactID > 0)
			{
				$to = $this->getEntityEmail(\CCrmOwnerType::Contact, $entityContactID);
			}
			if (empty($to) && $entityCompanyID > 0)
			{
				$to = $this->getEntityEmail(\CCrmOwnerType::Company, $entityCompanyID);
			}
		}
		else
		{
			$to = $this->getEntityEmail($entityTypeId, $entityId);
		}

		return $to;
	}

	private function getEntityEmail($entityTypeId, $entityId)
	{
		$result = '';
		$dbResFields = CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array(
				'ENTITY_ID' => \CCrmOwnerType::ResolveName($entityTypeId),
				'ELEMENT_ID' => $entityId,
				'TYPE_ID' => \CCrmFieldMulti::EMAIL
			)
		);

		while($arField = $dbResFields->Fetch())
		{
			if(empty($arField['VALUE']))
			{
				continue;
			}

			$result = $arField['VALUE'];
			break;
		}

		return $result;
	}

	private function getPostingCharset()
	{
		$postingCharset = '';
		$siteCharset = defined('LANG_CHARSET') ? LANG_CHARSET : (defined('SITE_CHARSET') ? SITE_CHARSET : 'windows-1251');
		$arSupportedCharset = explode(',', COption::GetOptionString('subscribe', 'posting_charset'));
		if (count($arSupportedCharset) === 0)
		{
			$postingCharset = $siteCharset;
		}
		else
		{
			foreach ($arSupportedCharset as $curCharset)
			{
				if (strcasecmp($curCharset, $siteCharset) === 0)
				{
					$postingCharset = $curCharset;
					break;
				}
			}

			if ($postingCharset === '')
			{
				$postingCharset = $arSupportedCharset[0];
			}
		}

		return $postingCharset;
	}

	private function parseFromString($from)
	{
		$fromName = $fromEncoded = '';
		$fromEmail = $from;

		if (preg_match('/(.*)<(.+?)>\s*$/is', $from, $matches))
		{
			$fromName  = trim($matches[1], "\"\x20\t\n\r\0\x0b");
			$fromEmail = strtolower(trim($matches[2]));

			if ($fromName != '')
			{
				$fromNameEscaped = str_replace(array('\\', '"', '<', '>'), array('/', '\'', '(', ')'), $fromName);
				$fromEncoded = sprintf(
					'%s <%s>',
					sprintf('=?%s?B?%s?=', SITE_CHARSET, base64_encode($fromNameEscaped)),
					$fromEmail
				);
			}
		}

		return array('email' => $fromEmail, 'name' => $fromName, 'nameEncoded' => $fromEncoded);
	}

	private static function getMailboxes()
	{
		$mailboxes = array();

		//crutch for Anton B. "amazing" API
		ob_start(); //prevent error showing when component is not found
		CThurlyComponent::includeComponentClass('thurly:main.mail.confirm');
		ob_end_clean();

		if (
			class_exists('MainMailConfirmComponent')
			&& method_exists('MainMailConfirmComponent', 'prepareMailboxes')
		)
		{
			$mailboxes = (array)MainMailConfirmComponent::prepareMailboxes();
		}

		return $mailboxes;
	}

	private static function makeMailboxesSelectOptions(array $mailboxes)
	{
		$options = array();
		foreach ($mailboxes as $mailbox)
		{
			$options[] = sprintf(
				$mailbox['name'] ? '%s <%s>' : '%s%s',
				$mailbox['name'], $mailbox['email']
			);
		}
		return $options;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (empty($arTestProperties["MessageText"]))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageText", "message" => GetMessage("CRM_SEMA_EMPTY_PROP"));
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

		$map = array(
			'Subject' => array(
				'Name' => GetMessage('CRM_SEMA_EMAIL_SUBJECT'),
				'FieldName' => 'subject',
				'Type' => 'string',
				'Required' => true
			),
			'MessageText' => array(
				'Name' => GetMessage('CRM_SEMA_MESSAGE_TEXT'),
				'FieldName' => 'message_text',
				'Type' => 'text',
				'Required' => true
			),
			'MessageTextType' => array(
				'Name' => GetMessage('CRM_SEMA_MESSAGE_TEXT_TYPE'),
				'FieldName' => 'message_text_type',
				'Type' => 'select',
				'Options' => array(
					self::TEXT_TYPE_BBCODE => 'BBCODE',
					self::TEXT_TYPE_HTML => 'HTML'
				),
				'Default' => self::TEXT_TYPE_BBCODE
			),
			'MessageTextEncoded' => array(
				'Name' => 'MessageTextEncoded',
				'FieldName' => 'message_text_encoded',
				'Type' => 'int',
				'Default' => 0
			),
			'AttachmentType' => array(
				'Name' => GetMessage('CRM_SEMA_ATTACHMENT_TYPE'),
				'FieldName' => 'attachment_type',
				'Type' => 'select',
				'Options' => array(
					static::ATTACHMENT_TYPE_FILE => GetMessage('CRM_SEMA_ATTACHMENT_FILE'),
					static::ATTACHMENT_TYPE_DISK => GetMessage('CRM_SEMA_ATTACHMENT_DISK')
				)
			),
			'Attachment' => array(
				'Name' => GetMessage('CRM_SEMA_ATTACHMENT'),
				'FieldName' => 'attachment',
				'Type' => 'file',
				'Multiple' => true
			)
		);

		$mailboxes = static::getMailboxes();

		if (!empty($mailboxes))
		{
			$map['From'] = array(
				'Name' => GetMessage('CRM_SEMA_EMAIL_FROM'),
				'FieldName' => 'from',
				'Type' => 'select',
				'Options' => static::makeMailboxesSelectOptions($mailboxes)
			);

			$dialog->setRuntimeData(array(
				'mailboxes' => $mailboxes
			));
		}
		$dialog->setMap($map);

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = array();

		$properties = array(
			'Subject' => (string)$arCurrentValues["subject"],
			'MessageText' => (string)$arCurrentValues["message_text"],
			'MessageTextType' => (string)$arCurrentValues["message_text_type"],
			'AttachmentType' => (string)$arCurrentValues["attachment_type"],
			'From' => (string)$arCurrentValues["from"],

			'MessageTextEncoded' => 0,
			'Attachment' => array()
		);

		if ($properties['AttachmentType'] === static::ATTACHMENT_TYPE_DISK)
		{
			foreach ((array)$arCurrentValues["attachment"] as $attachmentId)
			{
				$attachmentId = (int)$attachmentId;
				if ($attachmentId > 0)
				{
					$properties['Attachment'][] = $attachmentId;
				}
			}
		}
		else
		{
			$properties['Attachment'] = isset($arCurrentValues["attachment"])
				? $arCurrentValues["attachment"] : $arCurrentValues["attachment_text"];
		}

		if (
			$properties['MessageTextType'] !== self::TEXT_TYPE_BBCODE
			&& $properties['MessageTextType'] !== self::TEXT_TYPE_HTML
		)
		{
			$properties['MessageTextType'] = self::TEXT_TYPE_BBCODE;
		}

		if ($properties['MessageTextType'] === self::TEXT_TYPE_HTML)
		{
			$request = \Thurly\Main\Application::getInstance()->getContext()->getRequest();
			$rawData = $request->getPostList()->getRaw('message_text');
			if ($rawData === null)
			{
				$rawData = (array)$request->getPostList()->getRaw('form_data');
				$rawData = $rawData['message_text'];
			}

			if ($request->isAjaxRequest())
			{
				\CUtil::decodeURIComponent($rawData);
			}
			$properties['MessageText'] = htmlspecialcharsbx($rawData);
			$properties['MessageTextEncoded'] = 1;
		}

		if (count($errors) > 0)
			return false;

		$errors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($errors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $properties;

		return true;
	}
}