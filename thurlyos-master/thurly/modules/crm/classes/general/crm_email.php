<?php

use Thurly\Crm\Integration\StorageManager;
use Thurly\Crm\Integration\Channel;
use Thurly\Crm\Settings\ActivitySettings;

if(!IsModuleInstalled('thurlyos'))
{
	IncludeModuleLangFile(__FILE__);
}
else
{
	// HACK: try to take site language instead of user language
	$dbSite = CSite::GetByID(SITE_ID);
	$arSite = $dbSite->Fetch();
	IncludeModuleLangFile(__FILE__, isset($arSite['LANGUAGE_ID']) ? $arSite['LANGUAGE_ID'] : false);
}

class CCrmEMail
{
	public static function OnGetFilterList()
	{
		return array(
			'ID'					=>	'crm',
			'NAME'					=>	GetMessage('CRM_ADD_MESSAGE'),
			'ACTION_INTERFACE'		=>	$_SERVER['DOCUMENT_ROOT'].'/thurly/modules/crm/mail/action.php',
			'PREPARE_RESULT_FUNC'	=>	Array('CCrmEMail', 'PrepareVars'),
			'CONDITION_FUNC'		=>	Array('CCrmEMail', 'EmailMessageCheck'),
			'ACTION_FUNC'			=>	Array('CCrmEMail', 'EmailMessageAdd')
		);
	}

	public static function onGetFilterListImap()
	{
		return array(
			'ID'          => 'crm_imap',
			'NAME'        => GetMessage('CRM_ADD_MESSAGE'),
			'ACTION_FUNC' => Array('CCrmEMail', 'imapEmailMessageAdd')
		);
	}

	private static function FindUserIDByEmail($email)
	{
		$email = trim(strval($email));
		if($email === '')
		{
			return 0;
		}

		$dbUsers = CUser::GetList(
			($by='ID'),
			($order='ASC'),
			array('=EMAIL' => $email),
			array(
				'FIELDS' => array('ID'),
				'NAV_PARAMS' => array('nTopCount' => 1)
			)
		);

		$arUser = $dbUsers ? $dbUsers->Fetch() : null;
		return $arUser ? intval($arUser['ID']) : 0;
	}
	private static function PrepareEntityKey($entityTypeID, $entityID)
	{
		return "{$entityTypeID}-{$entityID}";
	}
	private static function CreateBinding($entityTypeID, $entityID)
	{
		$entityTypeID = intval($entityTypeID);
		$entityID = intval($entityID);

		return array(
			'ID' => $entityID,
			'TYPE_ID' => $entityTypeID,
			'TYPE_NAME' => CCrmOwnerType::ResolveName($entityTypeID)
		);
	}
	private static function CreateComm($entityTypeID, $entityID, $value)
	{
		$entityTypeID = intval($entityTypeID);
		$entityID = intval($entityID);
		$value = strval($value);

		return array(
			'ENTITY_ID' => $entityID,
			'ENTITY_TYPE_ID' => $entityTypeID,
			'VALUE' => $value,
			'TYPE' => 'EMAIL'
		);
	}
	private static function ExtractCommsFromEmails($emails, $arIgnored = array())
	{
		if(!is_array($emails))
		{
			$emails = array($emails);
		}

		if(count($emails) === 0)
		{
			return array();
		}

		$arFilter = array();
		foreach ($emails as $email)
		{
			//Process valid emails only
			if(!($email !== '' && CCrmMailHelper::IsEmail($email)))
			{
				continue;
			}

			if(in_array($email, $arIgnored, true))
			{
				continue;
			}

			$arFilter[] = array('RAW_VALUE' => $email);
		}

		if(empty($arFilter))
		{
			return array();
		}

		$dbFieldMulti = CCrmFieldMulti::GetList(
			array(),
			array(
				'ENTITY_ID' => 'LEAD|CONTACT|COMPANY',
				'TYPE_ID' => 'EMAIL',
				'FILTER' => $arFilter
			)
		);

		$result = array();
		while($arFieldMulti = $dbFieldMulti->Fetch())
		{
			$entityTypeID = CCrmOwnerType::ResolveID($arFieldMulti['ENTITY_ID']);
			$entityID = intval($arFieldMulti['ELEMENT_ID']);
			$result[] = self::CreateComm($entityTypeID, $entityID, $arFieldMulti['VALUE']);
		}
		return $result;
	}
	private static function ConvertCommsToBindings(&$arCommData)
	{
		$result = array();
		foreach($arCommData as &$arComm)
		{
			$entityTypeID = $arComm['ENTITY_TYPE_ID'];
			$entityID = $arComm['ENTITY_ID'];
			// Key to avoid dublicated entities
			$key = self::PrepareEntityKey($entityTypeID, $entityID);
			if(isset($result[$key]))
			{
				continue;
			}
			$result[$key] = self::CreateBinding($entityTypeID, $entityID);
		}
		unset($arComm);

		return $result;
	}
	private static function ExtractEmailsFromBody($body)
	{
		$body = strval($body);

		$out = array();
		if (!preg_match_all('/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $body, $out))
		{
			return array();
		}

		$result = array();
		foreach($out[0] as $email)
		{
			$email = strtolower($email);
			if (!in_array($email, $result, true))
			{
				$result[] = $email;
			}
		}

		return $result;
	}
	private static function GetResponsibleID(&$entityFields)
	{
		$result = isset($entityFields['ASSIGNED_BY_ID']) ? intval($entityFields['ASSIGNED_BY_ID']) : 0;
		if($result <= 0)
		{
			$result = isset($entityFields['CREATED_BY_ID']) ? intval($entityFields['CREATED_BY_ID']) : 0;
		}
		return $result;
	}
	private static function GetEntity($entityTypeID, $entityID, $select = array())
	{

		$entityTypeID = intval($entityTypeID);
		$entityID = intval($entityID);

		$dbRes = null;
		if($entityTypeID === CCrmOwnerType::Company)
		{
			$dbRes = CCrmCompany::GetListEx(
				array(),
				array(
					'ID' => $entityID,
					'CHECK_PERMISSIONS' => 'N'
				),
				false,
				array('nTopCount' => 1),
				$select
			);
		}
		elseif($entityTypeID === CCrmOwnerType::Contact)
		{
			$dbRes = CCrmContact::GetListEx(
				array(),
				array(
					'ID' => $entityID,
					'CHECK_PERMISSIONS' => 'N'
				),
				false,
				array('nTopCount' => 1),
				$select
			);
		}
		elseif($entityTypeID === CCrmOwnerType::Lead)
		{
			$dbRes = CCrmLead::GetListEx(
				array(),
				array(
					'ID' => $entityID,
					'CHECK_PERMISSIONS' => 'N'
				),
				false,
				array('nTopCount' => 1),
				$select
			);
		}
		elseif($entityTypeID === CCrmOwnerType::Deal)
		{
			$dbRes = CCrmDeal::GetListEx(
				array(),
				array(
					'ID' => $entityID,
					'CHECK_PERMISSIONS' => 'N'
				),
				false,
				array('nTopCount' => 1),
				$select
			);
		}

		return $dbRes ? $dbRes->Fetch() : null;
	}
	private static function IsEntityExists($entityTypeID, $entityID)
	{
		$arFields = self::GetEntity(
			$entityTypeID,
			$entityID,
			array('ID')
		);

		return is_array($arFields);
	}
	private static function GetDefaultResponsibleID($entityTypeID)
	{
		$entityTypeID = (int)$entityTypeID;
		if($entityTypeID === CCrmOwnerType::Lead)
		{
			return (int)COption::GetOptionString('crm', 'email_lead_responsible_id', 0);
		}
		elseif($entityTypeID === CCrmOwnerType::Contact)
		{
			return (int)COption::GetOptionString('crm', 'email_contact_responsible_id', 0);
		}
		return 0;
	}
	private static function ResolveResponsibleID($entityTypeID, $entityID)
	{
		$entityTypeID = intval($entityTypeID);
		$entityID = intval($entityID);

		$arFields = self::GetEntity(
			$entityTypeID,
			$entityID,
			array('ASSIGNED_BY_ID', 'CREATED_BY_ID')
		);

		return $arFields ? self::GetResponsibleID($arFields) : 0;
	}
	private static function TryImportVCard(&$fileData, $responsible = null)
	{
		$CCrmVCard = new CCrmVCard();
		$arContact = $CCrmVCard->ReadCard(false, $fileData);

		if (empty($arContact['NAME']) && empty($arContact['LAST_NAME']))
		{
			return false;
		}

		$arFilter = array();
		if (!empty($arContact['NAME']))
		{
			$arFilter['NAME'] = $arContact['NAME'];
		}
		if (!empty($arContact['LAST_NAME']))
		{
			$arFilter['LAST_NAME'] = $arContact['LAST_NAME'];
		}
		if (!empty($arContact['SECOND_NAME']))
		{
			$arFilter['SECOND_NAME'] = $arContact['SECOND_NAME'];
		}

		$arFilter['CHECK_PERMISSIONS'] = 'N';

		$dbContact = CCrmContact::GetListEx(array(), $arFilter, false, false, array('ID'));
		if ($dbContact->Fetch())
		{
			return false;
		}

		$arContact['SOURCE_ID'] = 'EMAIL';
		if (!empty($arContact['COMPANY_TITLE']))
		{
			$dbCompany = CCrmCompany::GetListEx(
				array(),
				array(
					'TITLE' => $arContact['COMPANY_TITLE'],
					'CHECK_PERMISSIONS' => 'N'
				),
				false,
				false,
				array('ID')
			);

			if (($arCompany = $dbCompany->Fetch()) !== false)
			{
				$arContact['COMPANY_ID'] = $arCompany['ID'];
			}
			else
			{
				if(!empty($arContact['COMMENTS']))
				{
					$arContact['COMMENTS'] .= PHP_EOL;
				}
				$arContact['COMMENTS'] .=
					GetMessage('CRM_MAIL_COMPANY_NAME', array('%TITLE%' => $arContact['COMPANY_TITLE']));
			}
		}

		if ($responsible <= 0)
		{
			$responsible = self::getDefaultResponsibleId(CCrmOwnerType::Contact);
			if ($responsible <= 0)
				$responsible = self::getDefaultResponsibleId(CCrmOwnerType::Lead);
		}

		if ($responsible > 0)
			$arContact['CREATED_BY_ID'] = $arContact['MODIFY_BY_ID'] = $arContact['ASSIGNED_BY_ID'] = $responsible;

		$CCrmContact = new CCrmContact(false);
		$CCrmContact->Add(
			$arContact,
			true,
			array('DISABLE_USER_FIELD_CHECK' => true)
		);

		return true;
	}
	protected static function ExtractPostingID(&$arMessageFields)
	{
		$header = isset($arMessageFields['HEADER']) ? $arMessageFields['HEADER'] : '';
		$match = array();
		return preg_match('/^X-Thurly-Posting:\s*(?P<id>[0-9]+)\s*$/im', $header, $match) === 1
			? (isset($match['id']) ? intval($match['id']) : 0)
			: 0;
	}

	protected static function getAdminsList()
	{
		static $adminList;

		if (is_null($adminList))
		{
			$adminList = array();

			$res = \CUser::getList(
				$by, $order,
				array('GROUPS_ID' => 1),
				array('FIELDS' => array('ID', 'ACTIVE'))
			);
			while ($admin = $res->fetch())
				$adminList[] = $admin;

			usort($adminList, function($a, $b)
			{
				if ($a['ACTIVE'] == 'Y' xor $b['ACTIVE'] == 'Y')
					return $a['ACTIVE'] == 'Y' ? -1 : 1;

				return $a['ID']-$b['ID'];
			});
		}

		return $adminList;
	}

	public static function imapEmailMessageAdd($msgFields)
	{
		global $DB;

		if (!\CModule::includeModule('crm'))
			return false;

		$typeIds = array(
			\CCrmOwnerType::Contact,
			\CCrmOwnerType::Company,
			\CCrmOwnerType::Lead
		);

		$messageId = isset($msgFields['ID']) ? intval($msgFields['ID']) : 0;
		$mailboxId = isset($msgFields['MAILBOX_ID']) ? intval($msgFields['MAILBOX_ID']) : 0;

		if (empty($mailboxId))
			return false;

		$mailbox = \CMailBox::getById($mailboxId)->fetch();

		if (empty($mailbox))
			return false;

		$mailbox['__email'] = '';
		if (check_email($mailbox['NAME'], true))
			$mailbox['__email'] = $mailbox['NAME'];
		else if (check_email($mailbox['LOGIN'], true))
			$mailbox['__email'] = $mailbox['LOGIN'];

		$denyNewEntityIn  = false;
		$denyNewEntityOut = false;
		$denyNewContact   = false;

		if (!empty($mailbox['OPTIONS']['flags']) && is_array($mailbox['OPTIONS']['flags']))
		{
			$denyNewEntityIn  = in_array('crm_deny_new_lead', $mailbox['OPTIONS']['flags']);
			$denyNewEntityIn  = $denyNewEntityIn || in_array('crm_deny_entity_in', $mailbox['OPTIONS']['flags']);
			$denyNewEntityOut = in_array('crm_deny_new_lead', $mailbox['OPTIONS']['flags']);
			$denyNewEntityOut = $denyNewEntityOut || in_array('crm_deny_entity_out', $mailbox['OPTIONS']['flags']);
			$denyNewContact  = in_array('crm_deny_new_contact', $mailbox['OPTIONS']['flags']);
		}

		$isIncome = empty($msgFields['IS_OUTCOME']);
		$isUnseen = empty($msgFields['IS_SEEN']);

		$userId = 0;

		$ownerTypeId = 0;
		$ownerId     = 0;

		$parentId = 0;

		$msgId     = isset($msgFields['MSG_ID']) ? $msgFields['MSG_ID'] : '';
		$inReplyTo = isset($msgFields['IN_REPLY_TO']) ? $msgFields['IN_REPLY_TO'] : '';

		$from    = isset($msgFields['FIELD_FROM']) ? $msgFields['FIELD_FROM'] : '';
		$replyTo = isset($msgFields['FIELD_REPLY_TO']) ? $msgFields['FIELD_REPLY_TO'] : '';

		$sender = array_unique(array_filter(array_merge(
			\CMailUtil::extractAllMailAddresses($replyTo),
			\CMailUtil::extractAllMailAddresses($from)
		), 'check_email'));

		$to  = isset($msgFields['FIELD_TO']) ? $msgFields['FIELD_TO'] : '';
		$cc  = isset($msgFields['FIELD_CC']) ? $msgFields['FIELD_CC'] : '';
		$bcc = isset($msgFields['FIELD_BCC']) ? $msgFields['FIELD_BCC'] : '';

		$rcpt = array_unique(array_filter(array_merge(
			\CMailUtil::extractAllMailAddresses($to),
			\CMailUtil::extractAllMailAddresses($cc),
			\CMailUtil::extractAllMailAddresses($bcc)
		), 'check_email'));

		$subject   = trim($msgFields['SUBJECT']) ?: getMessage('CRM_EMAIL_DEFAULT_SUBJECT');
		$body      = isset($msgFields['BODY']) ? $msgFields['BODY'] : '';
		$body_bb   = isset($msgFields['BODY_BB']) ? $msgFields['BODY_BB'] : '';
		$body_html = isset($msgFields['BODY_HTML']) ? $msgFields['BODY_HTML'] : '';

		if ($isIncome && preg_match('/\nX-EVENT_NAME:/i', $msgFields['HEADER']))
		{
			$defaultEmailFrom = \Thurly\Main\Config\Option::get('main', 'email_from', 'admin@'.$GLOBALS['SERVER_NAME']);
			$defaultEmailFrom = strtolower(trim($defaultEmailFrom));

			foreach ($sender as $item)
			{
				if (strtolower(trim($item)) == $defaultEmailFrom)
					return false;
			}
		}

		if (!empty($msgId))
		{
			if (!$isIncome && preg_match('/<crm\.activity\.((\d+)-[0-9a-z]+)@[^>]+>/i', sprintf('<%s>', $msgId), $matches))
			{
				$matchActivity = \CCrmActivity::getById($matches[2], false);
				if ($matchActivity && strtolower($matchActivity['URN']) == strtolower($matches[1]))
					return false;
			}
		}

		// skip employees
		{
			$filter = array('ACTIVE' => 'Y', '=EMAIL' => $isIncome ? $sender : $rcpt);

			$externalAuthId = array();
			if (isModuleInstalled('socialservices'))
				$externalAuthId[] = 'replica';
			if (isModuleInstalled('mail'))
				$externalAuthId[] = 'email';
			if (isModuleInstalled('im'))
				$externalAuthId[] = 'bot';
			if (isModuleInstalled('imopenlines'))
				$externalAuthId[] = 'imconnector';
			if (!empty($externalAuthId))
				$filter['!=EXTERNAL_AUTH_ID'] = $externalAuthId;

			$res = \Thurly\Main\UserTable::getList(array(
				'select' => array('EMAIL', 'UF_DEPARTMENT'),
				'filter' => $filter,
			));

			if ($isIncome)
			{
				while ($employee = $res->fetch())
				{
					$departments = empty($employee['UF_DEPARTMENT']) ? array() : (array) $employee['UF_DEPARTMENT'];
					if (reset($departments) > 0)
						return false;
				}

				if (CModule::includeModule('mail'))
				{
					// @TODO: index
					$employee = \Thurly\Mail\MailboxTable::getList(array(
						'select' => array('NAME', 'LOGIN'),
						'filter' => array(
							//'!USER_ID' => null,
							'=ACTIVE'  => 'Y',
							array(
								'LOGIC' => 'OR',
								'NAME'  => $sender,
								'LOGIN' => $sender,
							),
						),
						'limit' => 1,
					))->fetch();

					if (!empty($employee))
						return false;
				}
			}
			else
			{
				$employeesEmails = array();
				while ($employee = $res->fetch())
				{
					$departments = empty($employee['UF_DEPARTMENT']) ? array() : (array) $employee['UF_DEPARTMENT'];
					if (reset($departments) > 0)
						$employeesEmails[] = $employee['EMAIL'];
				}

				$employeesEmails = array_unique(array_map('strtolower', $employeesEmails));

				if (count($employeesEmails) >= count($rcpt))
					return false;

				if (CModule::includeModule('mail'))
				{
					// @TODO: index
					$res = \Thurly\Mail\MailboxTable::getList(array(
						'select' => array('NAME', 'LOGIN'),
						'filter' => array(
							//'!USER_ID' => null,
							'=ACTIVE'  => 'Y',
							array(
								'LOGIC' => 'OR',
								'NAME'  => $rcpt,
								'LOGIN' => $rcpt,
							),
						),
					));

					while ($employee = $res->fetch())
						$employeesEmails[] = check_email($employee['NAME'], true) ? $employee['NAME'] : $employee['LOGIN'];

					$employeesEmails = array_unique(array_map('strtolower', $employeesEmails));

					if (count($employeesEmails) >= count($rcpt))
						return false;
				}
			}
		}

		// @TODO: multiple
		if (!empty($inReplyTo))
		{
			if (preg_match('/<crm\.activity\.((\d+)-[0-9a-z]+)@[^>]+>/i', sprintf('<%s>', $inReplyTo), $matches))
			{
				$matchActivity = \CCrmActivity::getById($matches[2], false);
				if ($matchActivity && strtolower($matchActivity['URN']) == strtolower($matches[1]))
					$targetActivity = $matchActivity;
			}

			if (empty($targetActivity))
			{
				$res = \Thurly\Crm\Activity\MailMetaTable::getList(array(
					'select' => array('ACTIVITY_ID'),
					'filter' => array(
						'=MSG_ID_HASH' => md5(strtolower($inReplyTo))
					),
				));

				while ($mailMeta = $res->fetch())
				{
					if ($matchActivity = \CCrmActivity::getById($mailMeta['ACTIVITY_ID'], false))
					{
						$targetActivity = $matchActivity;
						break;
					}
				}
			}
		}

		if (empty($targetActivity))
		{
			$urnInfo = \CCrmActivity::parseUrn(
				\CCrmActivity::extractUrnFromMessage(
					$msgFields, \CCrmEMailCodeAllocation::getCurrent()
				)
			);

			if ($urnInfo['ID'] > 0)
			{
				$matchActivity = \CCrmActivity::getById($urnInfo['ID'], false);
				if (!empty($matchActivity) && strtolower($matchActivity['URN']) == strtolower($urnInfo['URN']))
					$targetActivity = $matchActivity;
			}
		}

		$forceNewLead = false;
		if ($isIncome && empty($targetActivity))
		{
			if (!empty($mailbox['OPTIONS']['crm_new_lead_for']) && is_array($mailbox['OPTIONS']['crm_new_lead_for']))
			{
				$forceNewLead = (boolean) array_intersect(
					array_map('strtolower', array_map('trim', $sender)),
					array_map('strtolower', array_map('trim', $mailbox['OPTIONS']['crm_new_lead_for']))
				);
			}
		}

		$commEmails = $isIncome ? $sender : $rcpt;
		$commData   = $forceNewLead ? array() : self::extractCommsFromEmails($commEmails);

		// private mailbox
		if (!empty($mailbox['USER_ID']))
		{
			// responsible = mailbox owner
			$userId = $mailbox['USER_ID'];
			$permissions = \CCrmPerms::getUserPermissions($userId);

			// do not bind forbidden entities
			foreach ($commData as $key => $item)
			{
				$canRead = \CCrmAuthorizationHelper::checkReadPermission(
					$item['ENTITY_TYPE_ID'], $item['ENTITY_ID'], $permissions
				);

				if (!$canRead)
				{
					if (self::resolveResponsibleId($item['ENTITY_TYPE_ID'], $item['ENTITY_ID']) != $userId)
						unset($commData[$key]);
				}
			}
		}

		$commData    = array_values($commData);
		$bindingData = self::convertCommsToBindings($commData);

		$correctedBindingData = array();
		$convertedLeadData = array();
		foreach ($bindingData as $key => $item)
		{
			if ($item['TYPE_ID'] != \CCrmOwnerType::Lead)
			{
				if (self::isEntityExists($item['TYPE_ID'], $item['ID']))
					$correctedBindingData[$key] = $item;

				continue;
			}

			$fields = self::getEntity(\CCrmOwnerType::Lead, $item['ID'], array('STATUS_ID'));
			if (!is_array($fields))
				continue;

			if (isset($fields['STATUS_ID']) && $fields['STATUS_ID'] == 'CONVERTED')
				$convertedLeadData[$key] = $item;
			else
				$correctedBindingData[$key] = $item;
		}

		foreach ($convertedLeadData as $item)
		{
			$exists = false;

			$res = \CCrmCompany::getListEx(
				array(), array('LEAD_ID' => $item['ID'], 'CHECK_PERMISSIONS' => 'N'),
				false, false, array('ID')
			);
			while ($entity = $res->fetch())
			{
				if (isset($correctedBindingData[self::prepareEntityKey(\CCrmOwnerType::Company, $entity['ID'])]))
				{
					$exists = true;
					continue 2;
				}
			}

			$res = \CCrmContact::getListEx(
				array(), array('LEAD_ID' => $item['ID'], 'CHECK_PERMISSIONS' => 'N'),
				false, false, array('ID')
			);
			while ($entity = $res->fetch())
			{
				if (isset($correctedBindingData[self::prepareEntityKey(\CCrmOwnerType::Contact, $entity['ID'])]))
				{
					$exists = true;
					continue 2;
				}
			}

			$res = \CCrmDeal::getListEx(
				array(), array('LEAD_ID' => $item['ID'], 'CHECK_PERMISSIONS' => 'N'),
				false, false, array('ID')
			);
			while ($entity = $res->fetch())
			{
				if (isset($correctedBindingData[self::prepareEntityKey(\CCrmOwnerType::Deal, $entity['ID'])]))
				{
					$exists = true;
					continue 2;
				}
			}

			$correctedBindingData[self::prepareEntityKey(\CCrmOwnerType::Lead, $item['ID'])] = $item;
		}

		$bindingData = $correctedBindingData;

		if (!empty($targetActivity))
		{
			// shared mailbox
			if (empty($mailbox['USER_ID']))
				$userId = $targetActivity['RESPONSIBLE_ID'];

			if ($targetActivity['RESPONSIBLE_ID'] == $userId)
			{
				$parentId = $targetActivity['ID'];

				if ($targetActivity['OWNER_TYPE_ID'] > 0 && $targetActivity['OWNER_ID'] > 0)
				{
					$key = self::prepareEntityKey($targetActivity['OWNER_TYPE_ID'], $targetActivity['OWNER_ID']);
					if (\CCrmOwnerType::Deal == $targetActivity['OWNER_TYPE_ID'] || isset($bindingData[$key]))
					{
						$ownerTypeId = (int) $targetActivity['OWNER_TYPE_ID'];
						$ownerId     = (int) $targetActivity['OWNER_ID'];

						if (!isset($bindingData[$key]))
							$bindingData[$key] = self::createBinding($ownerTypeId, $ownerId);
					}
				}

				if ($ownerId <= 0 || $ownerTypeId <= 0)
				{
					$respList   = array();
					$respAccess = array();

					$permissions = \CCrmPerms::getUserPermissions($userId);

					foreach ($typeIds as $typeId)
					{
						foreach ($bindingData as $key => $item)
						{
							if ($item['TYPE_ID'] === $typeId)
							{
								if (self::resolveResponsibleId($item['TYPE_ID'], $item['ID']) == $userId)
									$respList[$key] = array($item['TYPE_ID'], $item['ID']);

								$canRead = \CCrmAuthorizationHelper::checkReadPermission(
									$item['TYPE_ID'], $item['ID'], $permissions
								);

								if ($canRead)
									$respAccess[$key] = array($item['TYPE_ID'], $item['ID']);
							}
						}
					}

					$candidates = array_intersect_key($respList, $respAccess) ?: $respAccess ?: $respList;

					list($ownerTypeId, $ownerId) = reset($candidates);
				}
			}
		}

		// shared mailbox
		if (empty($userId))
		{
			$respQueue  = array();
			$respList   = array();
			$respAccess = array();

			if ($forceNewLead || !$denyNewEntityIn || !$denyNewEntityOut)
			{
				$respOption = (array) $mailbox['OPTIONS']['crm_lead_resp'];
				$respOption = array_values(array_unique($respOption));

				$res = \Thurly\Main\UserTable::getList(array(
					'select' => array('ID', 'ACTIVE'),
					'filter' => array(
						'ID' => $respOption,
					),
				));

				$respQueue  = array();
				$respActive = array();

				while ($resp = $res->fetch())
				{
					$respQueue[] = $resp['ID'];

					if ($resp['ACTIVE'] == 'Y')
						$respActive[] = $resp['ID'];
				}

				/* @TODO
				$respOrder = array_flip($respOption);
				$orderCompare = function ($a, $b) use (&$respOrder)
				{
					return isset($respOrder[$a], $respOrder[$b]) ? $respOrder[$a]-$respOrder[$b] : 0;
				};
				usort($respQueue, $orderCompare);
				usort($respActive, $orderCompare);
				*/

				if (count($respOption) > 0 && count($respActive) == count($respOption))
				{
					\Thurly\Main\Config\Option::set('crm', 'email_resp_queue_ok_'.$mailboxId, 'Y');
				}
				else
				{
					$shouldNotify = \Thurly\Main\Config\Option::get('crm', 'email_resp_queue_ok_'.$mailboxId, 'Y') == 'Y';
					if ($shouldNotify && \CModule::includeModule('im'))
					{
						\Thurly\Main\Config\Option::set('crm', 'email_resp_queue_ok_'.$mailboxId, 'N');

						$configUrl = \Thurly\Main\Config\Option::get('crm', 'path_to_emailtracker');

						$internalMessage = getMessage('CRM_EMAIL_BAD_RESP_QUEUE', array(
							'#EMAIL#'      => htmlspecialcharsbx($mailbox['NAME']),
							'#CONFIG_URL#' => htmlspecialcharsbx($configUrl),
						));
						$externalMessage = getMessage('CRM_EMAIL_BAD_RESP_QUEUE', array(
							'#EMAIL#'      => htmlspecialcharsbx($mailbox['NAME']),
							'#CONFIG_URL#' => htmlspecialcharsbx(\CCrmUrlUtil::toAbsoluteUrl($configUrl)),
						));

						foreach (self::getAdminsList() as $admin)
							\CCrmNotifier::notify($admin['ID'], $internalMessage, $externalMessage, 0, 'email_resp_queue_ok_'.$mailboxId);
					}
				}

				if (count($respActive) > 0)
					$respQueue = $respActive;
			}

			foreach ($typeIds as $typeId)
			{
				foreach ($bindingData as $item)
				{
					if ($item['TYPE_ID'] === $typeId)
					{
						$respId = self::resolveResponsibleId($item['TYPE_ID'], $item['ID']);
						if ($respId > 0)
						{
							$respList[] = array($item['TYPE_ID'], $item['ID'], $respId);

							$canRead = \CCrmAuthorizationHelper::checkReadPermission(
								$item['TYPE_ID'], $item['ID'],
								\CCrmPerms::getUserPermissions($respId)
							);

							if ($canRead)
								$respAccess[] = array($item['TYPE_ID'], $item['ID'], $respId);
						}
					}
				}
			}

			if (count($respList) > 0)
			{
				if (count($respAccess) > 0)
					$respList = $respAccess;

				if (count($respQueue) > 0)
				{
					foreach ($respList as $item)
					{
						list($entityTypeId, $entityId, $respId) = $item;
						if (in_array($respId, $respQueue))
						{
							$userId = $respId;

							$ownerTypeId = (int) $entityTypeId;
							$ownerId     = (int) $entityId;

							break;
						}
					}
				}

				if (empty($userId))
					list($ownerTypeId, $ownerId, $userId) = reset($respList);
			}

			if (empty($userId))
			{
				if (count($respQueue) > 0)
				{
					$luckyOne = \Thurly\Main\Config\Option::get('crm', 'last_resp_'.$mailboxId, -1) + 1;
					if ($luckyOne > count($respQueue)-1)
						$luckyOne = 0;
					\Thurly\Main\Config\Option::set('crm', 'last_resp_'.$mailboxId, $luckyOne);

					$userId = $respQueue[$luckyOne];
				}
			}
		}

		if (empty($userId))
		{
			$admin  = reset(self::getAdminsList());
			$userId = $admin['ID'];
		}

		if ($parentId == 0 && \CCrmOwnerType::Deal != $ownerId)
		{
			$bindingIds = array(
				\CCrmOwnerType::Contact => array(),
				\CCrmOwnerType::Company => array(),
			);
			foreach ($bindingData as $item)
			{
				if (\CCrmOwnerType::Deal == ($typeId = $item['TYPE_ID']))
				{
					$bindingIds = null;
					break;
				}

				if (in_array($typeId, array_keys($bindingIds)))
					$bindingIds[$typeId][] = $item['ID'];
			}

			if (!empty($bindingIds[\CCrmOwnerType::Contact]) || !empty($bindingIds[\CCrmOwnerType::Company]))
			{
				$res = \CCrmDeal::getListEx(
					array('DATE_MODIFY' => 'DESC'),
					array(
						'__INNER_FILTER_BIND' => array(
							'LOGIC' => 'OR',
							'=CONTACT_ID' => $bindingIds[\CCrmOwnerType::Contact],
							'=COMPANY_ID' => $bindingIds[\CCrmOwnerType::Company],
						),
						'CHECK_PERMISSIONS' => 'N',
					),
					false, false,
					array('ID', 'STAGE_ID', 'CATEGORY_ID')
				);

				$permissions = \CCrmPerms::getUserPermissions($userId);

				while ($item = $res->fetch())
				{
					$isFinal = \Thurly\Crm\PhaseSemantics::isFinal(
						\CCrmDeal::getSemanticId(
							$item['STAGE_ID'], (int) $item['CATEGORY_ID']
						)
					);

					if (!$isFinal)
					{
						$canRead = \CCrmAuthorizationHelper::checkReadPermission(
							\CCrmOwnerType::DealName, $item['ID'], $permissions
						);

						if ($canRead)
						{
							$key = self::prepareEntityKey(\CCrmOwnerType::Deal, $item['ID']);
							$bindingData[$key] = self::createBinding(\CCrmOwnerType::Deal, $item['ID']);

							break;
						}
					}
				}
			}
		}

		if (empty($bindingData) && count($commEmails) > 0)
		{
			if (!$forceNewLead && ($isIncome ? $denyNewEntityIn : $denyNewEntityOut))
				return true;

			$commData = array();
			$bindingData = array();

			$pushBindings = function($typeId, $id, $emails) use (&$commData, &$bindingData)
			{
				foreach ($emails as $i => $item)
					$commData[] = \CCrmEMail::createComm($typeId, $id, $item);

				$bindingData[\CCrmEMail::prepareEntityKey($typeId, $id)] = \CCrmEMail::createBinding($typeId, $id);
			};

			$sourceList = \CCrmStatus::getStatusList('SOURCE');
			$sourceId   = $mailbox['OPTIONS']['crm_lead_source'];
			if (empty($sourceId) || !isset($sourceList[$sourceId]))
			{
				if (isset($sourceList['EMAIL']))
					$sourceId = 'EMAIL';
				elseif (isset($sourceList['OTHER']))
					$sourceId = 'OTHER';
			}

			$storedOwnerType = $mailbox['OPTIONS'][$isIncome ? 'crm_new_entity_in' : 'crm_new_entity_out'];
			if (!$forceNewLead && \CCrmOwnerType::ContactName == $storedOwnerType)
			{
				$entityFields = array(
					'RESPONSIBLE_ID' => $userId,
					'TYPE_ID'        => 'CLIENT',
					'FM'             => array('EMAIL' => array()),
				);

				if ($sourceId != '')
					$entityFields['SOURCE_ID'] = $sourceId;

				$newEntity = function($fields, $emails) use ($userId, &$pushBindings)
				{
					foreach ($emails as $i => $item)
					{
						$fields['FM']['EMAIL'][sprintf('n%u', $i+1)] = array(
							'VALUE_TYPE' => 'WORK',
							'VALUE'      => $item
						);
					}

					$contactEntity = new \CCrmContact(false);
					$contactId = $contactEntity->add(
						$fields, true,
						array(
							'DISABLE_USER_FIELD_CHECK' => true,
							'REGISTER_SONET_EVENT'     => true,
							'CURRENT_USER'             => $userId,
						)
					);

					if ($contactId > 0)
					{
						$bizprocErrors = array();
						\CCrmBizProcHelper::autostartWorkflows(
							\CCrmOwnerType::Contact, $contactId,
							\CCrmBizProcEventType::Create,
							$bizprocErrors
						);

						$pushBindings(\CCrmOwnerType::Contact, $contactId, $emails);
					}
				};
			}
			else
			{
				$entityFields = array(
					'TITLE'              => getMessage(
						$isIncome ? 'CRM_MAIL_LEAD_FROM_EMAIL_TITLE' : 'CRM_MAIL_LEAD_FROM_USER_EMAIL_TITLE',
						array('%SENDER%' => $replyTo ?: $from)
					),
					'STATUS_ID'          => 'NEW',
					'COMMENTS'           => htmlspecialcharsbx($subject),
					//'SOURCE_DESCRIPTION' => getMessage(
					//	'CRM_MAIL_LEAD_FROM_EMAIL_SOURCE',
					//	array('%SENDER%' => $replyTo ?: $from)
					//),
					'OPENED'             => 'Y',
					'ORIGINATOR_ID'      => 'email-tracker',
					'ORIGIN_ID'          => $mailboxId,
					'FM'                 => array('EMAIL' => array()),
				);

				if (trim($msgFields['SUBJECT']))
					$entityFields['TITLE'] = trim($msgFields['SUBJECT']);

				if ($sourceId != '')
					$entityFields['SOURCE_ID'] = $sourceId;

				$newEntity = function($fields, $emails) use ($userId, $mailbox, &$pushBindings)
				{
					foreach ($emails as $i => $item)
					{
						$fields['FM']['EMAIL'][sprintf('n%u', $i+1)] = array(
							'VALUE_TYPE' => 'WORK',
							'VALUE'      => $item
						);
					}

					$leadEntity = new \CCrmLead(false);
					$leadId = $leadEntity->add(
						$fields, true,
						array(
							'DISABLE_USER_FIELD_CHECK' => true,
							'REGISTER_SONET_EVENT'     => true,
							'CURRENT_USER'             => $userId
						)
					);

					if ($leadId > 0)
					{
						$bizprocErrors = array();
						\CCrmBizProcHelper::autostartWorkflows(
							\CCrmOwnerType::Lead, $leadId,
							\CCrmBizProcEventType::Create,
							$bizprocErrors
						);
						\Thurly\Crm\Automation\Factory::runOnAdd(\CCrmOwnerType::Lead, $leadId);
						Channel\EmailTracker::getInstance()->registerLead($leadId, array('ORIGIN_ID' => sprintf('%u|%u', $mailbox['USER_ID'], $mailbox['ID'])));

						$pushBindings(\CCrmOwnerType::Lead, $leadId, $emails);
					}
				};
			}

			if ($isIncome)
			{
				$entityName = reset($commEmails);

				foreach (explode(',', join(',', array($replyTo, $from))) as $item)
				{
					if (!check_email($item))
						continue;

					$senderInfo = \CCrmMailHelper::parseEmail($item);
					if (!empty($senderInfo['NAME']))
					{
						$entityName = $senderInfo['NAME'];
						break;
					}
				}

				$newEntity(array_merge($entityFields, array('NAME' => $entityName)), $commEmails);
			}
			else
			{
				foreach (explode(',', join(',', array($to, $cc, $bcc))) as $item)
				{
					if (!check_email($item))
						continue;

					$rcptInfo = \CCrmMailHelper::parseEmail($item);
					$entityName = empty($rcptInfo['NAME']) ? $rcptInfo['EMAIL'] : $rcptInfo['NAME'];

					if (!empty($employeesEmails) && in_array($rcptInfo['EMAIL'], $employeesEmails))
						continue;

					$newEntity(array_merge($entityFields, array('NAME' => $entityName)), array($rcptInfo['EMAIL']));
				}
			}
		}

		if (empty($bindingData))
		{
			if (!$isIncome && !$denyNewContact)
			{
				$res = \CMailAttachment::getList(array(), array('MESSAGE_ID' => $messageId));
				while ($attachment = $res->fetch())
				{
					if (getFileExtension(strtolower($attachment['FILE_NAME'])) == 'vcf')
					{
						if ($attachment['FILE_ID'])
							$attachment['FILE_DATA'] = \CMailAttachment::getContents($attachment);
						self::tryImportVCard($attachment['FILE_DATA'], $userId);
					}
				}
			}

			return false;
		}

		if ($ownerId <= 0 || $ownerTypeId <= 0)
		{
			foreach ($typeIds as $typeId)
			{
				foreach ($bindingData as $item)
				{
					if ($item['TYPE_ID'] === $typeId)
					{
						$ownerTypeId = $typeId;
						$ownerId     = $item['ID'];

						break 2;
					}
				}
			}

			if ($ownerId <= 0 || $ownerTypeId <= 0)
			{
				$item = reset($bindingData);

				$ownerTypeId = $item['TYPE_ID'];
				$ownerId     = $item['ID'];
			}
		}

		$attachmentMaxSizeMb = (int) \COption::getOptionString('crm', 'email_attachment_max_size', 16);
		$attachmentMaxSize = $attachmentMaxSizeMb > 0 ? $attachmentMaxSizeMb*1024*1024 : 0;

		$filesData = array();
		$bannedAttachments = array();
		$res = \CMailAttachment::getList(array(), array('MESSAGE_ID' => $messageId));
		while ($attachment = $res->fetch())
		{
			if (getFileExtension(strtolower($attachment['FILE_NAME'])) == 'vcf' && !$denyNewContact)
			{
				if ($attachment['FILE_ID'])
					$attachment['FILE_DATA'] = \CMailAttachment::getContents($attachment);
				self::tryImportVCard($attachment['FILE_DATA'], $userId);
			}

			$fileSize = isset($attachment['FILE_SIZE']) ? intval($attachment['FILE_SIZE']) : 0;
			if ($fileSize <= 0)
				continue;

			if ($attachmentMaxSize > 0 && $fileSize > $attachmentMaxSize)
			{
				$bannedAttachments[] = array(
					'name' => $attachment['FILE_NAME'],
					'size' => $fileSize
				);

				continue;
			}

			if ($attachment['FILE_ID'] && empty($attachment['FILE_DATA']))
				$attachment['FILE_DATA'] = \CMailAttachment::getContents($attachment);

			$filesData[] = array(
				'name'      => $attachment['FILE_NAME'],
				'type'      => $attachment['CONTENT_TYPE'],
				'content'   => $attachment['FILE_DATA'],
				'MODULE_ID' => 'crm',
				'attachment_id' => $attachment['ID'],
			);
		}

		$eventBindings = array();
		foreach ($bindingData as $item)
		{
			$eventBindings[] = array(
				'USER_ID'     => $userId,
				'ENTITY_TYPE' => $item['TYPE_NAME'],
				'ENTITY_ID'   => $item['ID'],
			);
		}

		$eventText  = '<b>'.getMessage('CRM_EMAIL_SUBJECT').'</b>: '.$subject.PHP_EOL;
		$eventText .= '<b>'.getMessage('CRM_EMAIL_FROM').'</b>: '.join($sender, ', ').PHP_EOL;
		$eventText .= '<b>'.getMessage('CRM_EMAIL_TO').'</b>: '.join($rcpt, ', ').PHP_EOL;

		if (!empty($bannedAttachments))
		{
			$eventText .= '<b>'.getMessage('CRM_EMAIL_BANNENED_ATTACHMENTS', array('%MAX_SIZE%' => $attachmentMaxSizeMb)).'</b>: ';
			foreach ($bannedAttachments as $attachmentInfo)
			{
				$eventText .= getMessage(
					'CRM_EMAIL_BANNENED_ATTACHMENT_INFO',
					array(
						'%NAME%' => $attachmentInfo['name'],
						'%SIZE%' => round($attachmentInfo['size']/1024/1024, 1)
					)
				);
			}

			$eventText .= PHP_EOL;
		}

		$eventText .= preg_replace('/(\r\n|\n|\r)+/', PHP_EOL, htmlspecialcharsbx($body));

		$crmEvent = new \CCrmEvent();
		$crmEvent->add(
			array(
				'USER_ID'      => $userId,
				'ENTITY'       => $eventBindings,
				'ENTITY_TYPE'  => \CCrmOwnerType::resolveName($ownerTypeId),
				'ENTITY_ID'    => $ownerId,
				'EVENT_NAME'   => getMessage('CRM_EMAIL_GET_EMAIL'),
				'EVENT_TYPE'   => 2,
				'EVENT_TEXT_1' => $eventText,
				'FILES'        => $filesData,
			),
			false
		);

		$site   = \CSite::getList($by = 'sort', $order = 'desc', array('DEFAULT' => 'Y', 'ACTIVE' => 'Y'))->fetch();
		$siteId = !empty($site['LID']) ? $site['LID'] : 's1';

		$storageTypeId = \CCrmActivity::getDefaultStorageTypeID();
		$elementIds = array();
		foreach ($filesData as $i => $fileData)
		{
			$fileId = \CFile::saveFile($fileData, 'crm', true);
			if (!($fileId > 0))
				continue;

			$fileData = \CFile::getFileArray($fileId);
			if (empty($fileData))
				continue;

			if (trim($fileData['ORIGINAL_NAME']) == '')
				$fileData['ORIGINAL_NAME'] = $fileData['FILE_NAME'];
			$elementId = StorageManager::saveEmailAttachment(
				$fileData, $storageTypeId, $siteId,
				array('USER_ID' => $userId)
			);
			if ($elementId > 0)
			{
				$elementIds[] = (int) $elementId;
				$filesData[$i]['element_id'] = (int) $elementId;
			}
		}

		if (!empty($body_html))
		{
			$checkInlineFiles = true;
			$descr = $body_html;
		}
		else if (!empty($body_bb))
		{
			$bbCodeParser = new \CTextParser();
			$descr = $bbCodeParser->convertText($body_bb);

			foreach ($filesData as $item)
			{
				$descr = preg_replace(
					sprintf('/\[ATTACHMENT=attachment_%u\]/is', $item['attachment_id']),
					sprintf('<img src="aid:%u">', $item['attachment_id']),
					$descr, -1, $count
				);

				if ($count > 0)
					$checkInlineFiles = true;
			}
		}
		else
		{
			$descr = preg_replace('/\r\n|\n|\r/', '<br>', htmlspecialcharsbx($body));
		}

		if ($isIncome)
		{
			$direction = \CCrmActivityDirection::Incoming;
			$completed = $isUnseen ? 'N' : 'Y';
		}
		else
		{
			$direction = \CCrmActivityDirection::Outgoing;
			$completed = 'Y';
		}

		$datetime = $deadline = !empty($msgFields['FIELD_DATE']) && $DB->isDate($msgFields['FIELD_DATE'], FORMAT_DATETIME)
			? $msgFields['FIELD_DATE'] : convertTimeStamp(time()+\CTimeZone::getOffset(), 'FULL', $siteId);
		//if ($isIncome)
		{
			$deadline = convertTimeStamp(strtotime('tomorrow'), 'FULL', $siteId);
			if (CModule::includeModule('calendar'))
			{
				$calendarSettings = \CCalendar::getSettings();

				$dummyDeadline = new \Thurly\Main\Type\DateTime();
				$nowTimestamp = $dummyDeadline->getTimestamp();
				$dummyDeadline->setTime($calendarSettings['work_time_end'] > 0 ? $calendarSettings['work_time_end'] : 19, 0, 0);
				if ($dummyDeadline->getTimestamp() > $nowTimestamp)
					$deadline = $dummyDeadline->format(\Thurly\Main\Type\DateTime::convertFormatToPhp(FORMAT_DATETIME));
			}
		}

		$activityFields = array(
			'OWNER_ID'             => $ownerId,
			'OWNER_TYPE_ID'        => $ownerTypeId,
			'TYPE_ID'              => \CCrmActivityType::Email,
			'ASSOCIATED_ENTITY_ID' => 0,
			'PARENT_ID'            => $parentId,
			'SUBJECT'              => $subject,
			'START_TIME'           => (string) $datetime,
			'END_TIME'             => (string) $deadline,
			'COMPLETED'            => $completed,
			'AUTHOR_ID'            => $userId,
			'RESPONSIBLE_ID'       => $userId,
			'PRIORITY'             => \CCrmActivityPriority::Medium,
			'DESCRIPTION'          => $descr,
			'DESCRIPTION_TYPE'     => \CCrmContentType::Html,
			'DIRECTION'            => $direction,
			'LOCATION'             => '',
			'NOTIFY_TYPE'          => \CCrmActivityNotifyType::None,
			'STORAGE_TYPE_ID'      => $storageTypeId,
			'STORAGE_ELEMENT_IDS'  => $elementIds,
			'BINDINGS'             => array(),
			'SETTINGS'             => array(
				'EMAIL_META' => array(
					'__email' => $mailbox['__email'],
					'from'    => $from,
					'replyTo' => $replyTo,
					'to'      => $to,
					'cc'      => $cc,
					'bcc'     => $bcc,
				),
			),
		);

		foreach ($bindingData as $item)
		{
			if ($item['TYPE_ID'] > 0 && $item['ID'] > 0)
			{
				$activityFields['BINDINGS'][] = array(
					'OWNER_TYPE_ID' => $item['TYPE_ID'],
					'OWNER_ID'      => $item['ID'],
				);
			}
		}

		if (!empty($commData))
			$activityFields['COMMUNICATIONS'] = $commData;

		$activityId = \CCrmActivity::add($activityFields, false, false, array('REGISTER_SONET_EVENT' => true));
		if ($activityId > 0)
		{
			if (!empty($checkInlineFiles))
			{
				foreach ($filesData as $item)
				{
					$info = \Thurly\Crm\Integration\DiskManager::getFileInfo(
						$item['element_id'], false,
						array('OWNER_TYPE_ID' => \CCrmOwnerType::Activity, 'OWNER_ID' => $activityId)
					);

					$descr = preg_replace(
						sprintf('/<img([^>]+)src\s*=\s*(\'|\")?\s*(aid:%u)\s*\2([^>]*)>/is', $item['attachment_id']),
						sprintf('<img\1src="%s"\4>', $info['VIEW_URL']),
						$descr, -1, $count
					);

					if ($count > 0)
						$descrUpdated = true;
				}

				if (!empty($descrUpdated))
				{
					\CCrmActivity::update($activityId, array(
						'DESCRIPTION' => $descr,
					), false, false);
				}
			}

			\Thurly\Crm\Activity\MailMetaTable::add(array(
				'ACTIVITY_ID'      => $activityId,
				'MSG_ID_HASH'      => !empty($msgId) ? md5(strtolower($msgId)) : '',
				'MSG_INREPLY_HASH' => !empty($inReplyTo) ? md5(strtolower($inReplyTo)) : '',
				'MSG_HEADER_HASH'  => $msgFields['MSG_HASH'],
			));

			$res = \Thurly\Crm\Activity\MailMetaTable::getList(array(
				'select' => array('ACTIVITY_ID'),
				'filter' => array(
					'MSG_INREPLY_HASH' => md5(strtolower($msgId)),
				),
			));
			while ($mailMeta = $res->fetch())
			{
				\CCrmActivity::update($mailMeta['ACTIVITY_ID'], array(
					'PARENT_ID' => $activityId,
				), false, false);
			}

			if ($isIncome)
			{
				\Thurly\Crm\Automation\Trigger\EmailTrigger::execute($activityFields['BINDINGS'], $activityFields);
			}
			Channel\EmailTracker::getInstance()->registerActivity($activityId, array('ORIGIN_ID' => sprintf('%u|%u', $mailbox['USER_ID'], $mailbox['ID'])));
		}

		if ($userId > 0 && $isIncome && $completed != 'Y')
		{
			\CCrmActivity::notify(
				$activityFields,
				\CCrmNotifierSchemeType::IncomingEmail,
				sprintf('crm_email_%u_%u', $activityFields['OWNER_TYPE_ID'], $activityFields['OWNER_ID'])
			);
		}

		return true;
	}

	public static function EmailMessageAdd($arMessageFields, $ACTION_VARS)
	{
		if(!CModule::IncludeModule('crm'))
		{
			return false;
		}

		$date = isset($arMessageFields['FIELD_DATE']) ? $arMessageFields['FIELD_DATE'] : '';
		$maxAgeDays = intval(COption::GetOptionString('crm', 'email_max_age', 7));
		$maxAge = $maxAgeDays > 0 ? ($maxAgeDays * 86400) : 0;
		if($maxAge > 0 && $date !== '')
		{
			$now = time() + CTimeZone::GetOffset();
			$timestamp = MakeTimeStamp($date, FORMAT_DATETIME);
			if( ($now - $timestamp) > $maxAge)
			{
				//Time threshold is exceeded
				return false;
			}
		}

		$crmEmail = strtolower(trim(COption::GetOptionString('crm', 'mail', '')));

		$msgID = isset($arMessageFields['ID']) ? intval($arMessageFields['ID']) : 0;
		$mailboxID = isset($arMessageFields['MAILBOX_ID']) ? intval($arMessageFields['MAILBOX_ID']) : 0;
		$from = isset($arMessageFields['FIELD_FROM']) ? $arMessageFields['FIELD_FROM'] : '';
		$replyTo = isset($arMessageFields['FIELD_REPLY_TO']) ? $arMessageFields['FIELD_REPLY_TO'] : '';
		if($replyTo !== '')
		{
			// Ignore FROM if REPLY_TO EXISTS
			$from = $replyTo;
		}
		$addresserInfo = CCrmMailHelper::ParseEmail($from);
		if($crmEmail !== '' && strcasecmp($addresserInfo['EMAIL'], $crmEmail) === 0)
		{
			// Ignore emails from ourselves
			return false;
		}

		$to = isset($arMessageFields['FIELD_TO']) ? $arMessageFields['FIELD_TO'] : '';
		$cc = isset($arMessageFields['FIELD_CC']) ? $arMessageFields['FIELD_CC'] : '';
		$bcc = isset($arMessageFields['FIELD_BCC']) ? $arMessageFields['FIELD_BCC'] : '';

		$addresseeEmails = array_unique(
			array_merge(
				$to !== '' ? CMailUtil::ExtractAllMailAddresses($to) : array(),
				$cc !== '' ? CMailUtil::ExtractAllMailAddresses($cc) : array(),
				$bcc !== '' ? CMailUtil::ExtractAllMailAddresses($bcc) : array()),
			SORT_STRING
		);

		if($mailboxID > 0)
		{
			$dbMailbox = CMailBox::GetById($mailboxID);
			$arMailbox = $dbMailbox->Fetch();

			// POP3 mailboxes are ignored - they bound to single email
			if ($arMailbox
				&& $arMailbox['SERVER_TYPE'] === 'smtp'
				&& (empty($crmEmail) || !in_array($crmEmail, $addresseeEmails, true)))
			{
				return false;
			}
		}

		$subject = trim($arMessageFields['SUBJECT']) ?: getMessage('CRM_EMAIL_DEFAULT_SUBJECT');
		$body = isset($arMessageFields['BODY']) ? $arMessageFields['BODY'] : '';
		$arBodyEmails = null;

		$userID = 0;
		$parentID = 0;
		$ownerTypeID = CCrmOwnerType::Undefined;
		$ownerID = 0;

		$addresserID = self::FindUserIDByEmail($addresserInfo['EMAIL']);
		if($addresserID > 0 && Thurly\Crm\Integration\IntranetManager::isExternalUser($addresserID))
		{
			//Forget about extranet user
			$addresserID = 0;
		}

		$arCommEmails = $addresserID <= 0
			? array($addresserInfo['EMAIL'])
			: ($crmEmail !== ''
				? array_diff($addresseeEmails, array($crmEmail))
				: $addresseeEmails);
		//Trying to fix strange behaviour of array_diff under OPcache (issue #60862)
		$arCommEmails = array_filter($arCommEmails);

		$targInfo = CCrmActivity::ParseUrn(
			CCrmActivity::ExtractUrnFromMessage(
				$arMessageFields,
				CCrmEMailCodeAllocation::GetCurrent()
			)
		);
		$targActivity = $targInfo['ID'] > 0 ? CCrmActivity::GetByID($targInfo['ID'], false) : null;

		// Check URN
		if ($targActivity
			&& (!isset($targActivity['URN']) || strtoupper($targActivity['URN']) !== strtoupper($targInfo['URN'])))
		{
			$targActivity = null;
		}

		if($targActivity)
		{
			$postingID = self::ExtractPostingID($arMessageFields);
			if($postingID > 0 && isset($targActivity['ASSOCIATED_ENTITY_ID']) && intval($targActivity['ASSOCIATED_ENTITY_ID']) === $postingID)
			{
				// Ignore - it is our message.
				return false;
			}

			$parentID = $targActivity['ID'];
			$subject = CCrmActivity::ClearUrn($subject);

			if($addresserID > 0)
			{
				$userID = $addresserID;
			}
			elseif(isset($targActivity['RESPONSIBLE_ID']))
			{
				$userID = $targActivity['RESPONSIBLE_ID'];
			}

			if(isset($targActivity['OWNER_TYPE_ID']))
			{
				$ownerTypeID = intval($targActivity['OWNER_TYPE_ID']);
			}

			if(isset($targActivity['OWNER_ID']))
			{
				$ownerID = intval($targActivity['OWNER_ID']);
			}

			$arCommData = self::ExtractCommsFromEmails($arCommEmails);

			if($ownerTypeID > 0 && $ownerID > 0)
			{
				if(empty($arCommData))
				{
					if($addresserID > 0)
					{
						foreach($addresseeEmails as $email)
						{
							if($email === $crmEmail)
							{
								continue;
							}

							$arCommData = array(self::CreateComm($ownerTypeID, $ownerID, $email));
						}
					}
					else
					{
						$arCommData = array(self::CreateComm($ownerTypeID, $ownerID, $addresserInfo['EMAIL']));
					}
				}
				elseif($ownerTypeID !== CCrmOwnerType::Deal)
				{
					//Check if owner in communications. Otherwise clear owner.
					//There is only one exception for DEAL - it entity has no communications
					$isOwnerInComms = false;
					foreach($arCommData as &$arCommItem)
					{
						$commEntityTypeID = isset($arCommItem['ENTITY_TYPE_ID']) ? $arCommItem['ENTITY_TYPE_ID'] : CCrmOwnerType::Undefined;
						$commEntityID = isset($arCommItem['ENTITY_ID']) ? $arCommItem['ENTITY_ID'] : 0;

						if($commEntityTypeID === $ownerTypeID && $commEntityID === $ownerID)
						{
							$isOwnerInComms = true;
							break;
						}
					}
					unset($arCommItem);

					if(!$isOwnerInComms)
					{
						$ownerTypeID = CCrmOwnerType::Undefined;
						$ownerID = 0;
					}
				}
			}
		}
		else
		{
			if($addresserID > 0)
			{
				//It is email from registred user
				$userID = $addresserID;

				if(empty($arCommEmails))
				{
					$arBodyEmails = self::ExtractEmailsFromBody($body);
					//Clear system user emails and CRM email
					if(!empty($arBodyEmails))
					{
						foreach($arBodyEmails as $email)
						{
							if(strcasecmp($email, $crmEmail) !== 0 && self::FindUserIDByEmail($email) <= 0)
							{
								$arCommEmails[] = $email;
							}
						}
					}
				}

				// Try to resolve communications
				$arCommData = self::ExtractCommsFromEmails($arCommEmails);
			}
			else
			{
				//It is email from unknown user

				//Try to resolve bindings from addresser
				$arCommData = self::ExtractCommsFromEmails($arCommEmails);
				if(!empty($arCommData))
				{
					// Try to resolve responsible user
					foreach($arCommData as &$arComm)
					{
						$userID = self::ResolveResponsibleID(
							$arComm['ENTITY_TYPE_ID'],
							$arComm['ENTITY_ID']
						);

						if($userID > 0)
						{
							break;
						}
					}
					unset($arComm);
				}
			}

			// Try to resolve owner by old-style method-->
			$arACTION_VARS = explode('&', $ACTION_VARS);
			for ($i=0, $ic=count($arACTION_VARS); $i < $ic ; $i++)
			{
				$v = $arACTION_VARS[$i];
				if($pos = strpos($v, '='))
				{
					$name = substr($v, 0, $pos);
					${$name} = urldecode(substr($v, $pos+1));
				}
			}

			$arTypeNames = CCrmOwnerType::GetNames(
				array(
					CCrmOwnerType::Lead,
					CCrmOwnerType::Deal,
					CCrmOwnerType::Contact,
					CCrmOwnerType::Company
				)
			);
			foreach ($arTypeNames as $typeName)
			{
				$regexVar = 'W_CRM_ENTITY_REGEXP_'.$typeName;

				if (empty(${$regexVar}))
				{
					continue;
				}

				$regexp = '/'.${$regexVar}.'/i'.BX_UTF_PCRE_MODIFIER;
				$match = array();
				if (preg_match($regexp, $subject, $match) === 1)
				{
					$ownerID = (int)$match[1];
					$ownerTypeID = CCrmOwnerType::ResolveID($typeName);
					$subject = preg_replace($regexp, '', $subject);
					break;
				}
			}
			// <-- Try to resolve owner by old-style method

			if($ownerID > 0 && CCrmOwnerType::IsDefined($ownerTypeID))
			{
				// Filter communications by owner
				if($ownerTypeID !== CCrmOwnerType::Deal)
				{
					if(!empty($arCommData))
					{
						foreach($arCommData as $commKey => $arComm)
						{
							if($arComm['ENTITY_TYPE_ID'] === $ownerTypeID && $arComm['ENTITY_ID'] === $ownerID)
							{
								continue;
							}

							unset($arCommData[$commKey]);
						}

						$arCommData = array_values($arCommData);
					}

					if(empty($arCommData))
					{
						if($addresserID > 0)
						{
							foreach($addresseeEmails as $email)
							{
								if($email === $crmEmail)
								{
									continue;
								}

								$arCommData = array(self::CreateComm($ownerTypeID, $ownerID, $email));
							}
						}
						else
						{
							$arCommData = array(self::CreateComm($ownerTypeID, $ownerID, $addresserInfo['EMAIL']));
						}
					}
				}
				else
				{
					// Deal does not have communications. But lead communications are strange for this context.
					// It is important for explicit binding mode (like text [DID#100] in subject). Try to get rid of lead communications.
					$arCommTypeMap = array();
					foreach($arCommData as $commKey => $arComm)
					{
						$commTypeID = $arComm['ENTITY_TYPE_ID'];
						if(!isset($arCommTypeMap[$commTypeID]))
						{
							$arCommTypeMap[$commTypeID] = array();
						}
						$arCommTypeMap[$commTypeID][] = $arComm;
					}
					if(isset($arCommTypeMap[CCrmOwnerType::Contact]) || isset($arCommTypeMap[CCrmOwnerType::Company]))
					{
						if(isset($arCommTypeMap[CCrmOwnerType::Contact]) && isset($arCommTypeMap[CCrmOwnerType::Company]))
						{
							$arCommData = array_merge($arCommTypeMap[CCrmOwnerType::Contact], $arCommTypeMap[CCrmOwnerType::Company]);
						}
						elseif(isset($arCommTypeMap[CCrmOwnerType::Contact]))
						{
							$arCommData = $arCommTypeMap[CCrmOwnerType::Contact];
						}
						else//if(isset($arCommTypeMap[CCrmOwnerType::Company]))
						{
							$arCommData = $arCommTypeMap[CCrmOwnerType::Company];
						}
					}
				}
			}
		}

		$arBindingData = self::ConvertCommsToBindings($arCommData);

		// Check bindings for converted leads -->
		// Not Existed entities are ignored. Converted leads are ignored if their associated entities (contacts, companies, deals) are contained in bindings.
		$arCorrectedBindingData = array();
		$arConvertedLeadData = array();
		foreach($arBindingData as $bindingKey => &$arBinding)
		{
			if($arBinding['TYPE_ID'] !== CCrmOwnerType::Lead)
			{
				if(self::IsEntityExists($arBinding['TYPE_ID'], $arBinding['ID']))
				{
					$arCorrectedBindingData[$bindingKey] = $arBinding;
				}
				continue;
			}

			$arFields = self::GetEntity(
				CCrmOwnerType::Lead,
				$arBinding['ID'],
				array('STATUS_ID')
			);

			if(!is_array($arFields))
			{
				continue;
			}

			if(isset($arFields['STATUS_ID']) && $arFields['STATUS_ID'] === 'CONVERTED')
			{
				$arConvertedLeadData[$bindingKey] = $arBinding;
			}
			else
			{
				$arCorrectedBindingData[$bindingKey] = $arBinding;
			}
		}
		unset($arBinding);

		foreach($arConvertedLeadData as &$arConvertedLead)
		{
			$leadID = $arConvertedLead['ID'];
			$exists = false;

			$dbRes = CCrmCompany::GetListEx(
				array(),
				array('LEAD_ID' => $leadID, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID')
			);

			if($dbRes)
			{
				while($arRes = $dbRes->Fetch())
				{
					if(isset($arCorrectedBindingData[self::PrepareEntityKey(CCrmOwnerType::Company, $arRes['ID'])]))
					{
						$exists = true;
						break;
					}
				}
			}

			if($exists)
			{
				continue;
			}

			$dbRes = CCrmContact::GetListEx(
				array(),
				array('LEAD_ID' => $leadID, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID')
			);

			if($dbRes)
			{
				while($arRes = $dbRes->Fetch())
				{
					if(isset($arCorrectedBindingData[self::PrepareEntityKey(CCrmOwnerType::Contact, $arRes['ID'])]))
					{
						$exists = true;
						break;
					}
				}
			}

			if($exists)
			{
				continue;
			}

			$dbRes = CCrmDeal::GetListEx(
				array(),
				array('LEAD_ID' => $leadID, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID')
			);

			if($dbRes)
			{
				while($arRes = $dbRes->Fetch())
				{
					if(isset($arCorrectedBindingData[self::PrepareEntityKey(CCrmOwnerType::Deal, $arRes['ID'])]))
					{
						$exists = true;
						break;
					}
				}
			}

			if($exists)
			{
				continue;
			}

			$arCorrectedBindingData[self::PrepareEntityKey(CCrmOwnerType::Lead, $leadID)] = $arConvertedLead;
		}
		unset($arConvertedLead);

		$arBindingData = $arCorrectedBindingData;
		// <-- Check bindings for converted leads

		// If no bindings are found then create new lead from this message
		// Skip lead creation if email list is empty. Otherwise we will create lead with no email-addresses. It is absolutely useless.
		$emailQty = count($arCommEmails);
		if(empty($arBindingData) && $emailQty > 0)
		{
			if(strtoupper(COption::GetOptionString('crm', 'email_create_lead_for_new_addresser', 'Y')) !== 'Y')
			{
				// Creation of new lead is not allowed
				return true;
			}

			//"Lead from forwarded email..." or "Lead from email..."
			$title = trim($arMessageFields['SUBJECT'])
				?: GetMessage(
					$addresserID > 0
						? 'CRM_MAIL_LEAD_FROM_USER_EMAIL_TITLE'
						: 'CRM_MAIL_LEAD_FROM_EMAIL_TITLE',
					array('%SENDER%' => $addresserInfo['ORIGINAL'])
				);

			$comment = '';
			if($body !== '')
			{
				// Remove extra new lines (fix for #31807)
				$comment = preg_replace("/(\r\n|\n|\r)+/", '<br/>', htmlspecialcharsbx($body));
			}
			if($comment === '')
			{
				$comment = htmlspecialcharsbx($subject);
			}

			$name = '';
			if($addresserID <= 0)
			{
				$name = $addresserInfo['NAME'];
			}
			else
			{
				//Try get name from body
				for($i = 0; $i < $emailQty; $i++)
				{
					$email = $arCommEmails[$i];
					$match = array();
					if(preg_match('/"([^"]+)"\s*<'.$email.'>/i'.BX_UTF_PCRE_MODIFIER, $body, $match) === 1 && count($match) > 1)
					{
						$name = $match[1];
						break;
					}

					if(preg_match('/"([^"]+)"\s*[\s*mailto\:\s*'.$email.']/i'.BX_UTF_PCRE_MODIFIER, $body, $match) === 1 && count($match) > 1)
					{
						$name = $match[1];
						break;
					}
				}

				if($name === '')
				{
					$name = $arCommEmails[0];
				}
			}

			$arLeadFields = array(
				'TITLE' =>  $title,
				'NAME' => $name,
				'STATUS_ID' => 'NEW',
				'COMMENTS' => $comment,
				'SOURCE_DESCRIPTION' => GetMessage('CRM_MAIL_LEAD_FROM_EMAIL_SOURCE', array('%SENDER%' => $addresserInfo['ORIGINAL'])),
				'OPENED' => 'Y',
				'FM' => array(
					'EMAIL' => array()
				)
			);

			$sourceList = CCrmStatus::GetStatusList('SOURCE');
			$sourceID = COption::GetOptionString('crm', 'email_lead_source_id', '');
			if($sourceID === '' || !isset($sourceList[$sourceID]))
			{
				if(isset($sourceList['EMAIL']))
				{
					$sourceID = 'EMAIL';
				}
				elseif(isset($sourceList['OTHER']))
				{
					$sourceID = 'OTHER';
				}
			}

			if($sourceID !== '')
			{
				$arLeadFields['SOURCE_ID'] = $sourceID;
			}

			$responsibleID = self::GetDefaultResponsibleID(CCrmOwnerType::Lead);
			if($responsibleID > 0)
			{
				$arLeadFields['CREATED_BY_ID'] = $arLeadFields['MODIFY_BY_ID'] = $arLeadFields['ASSIGNED_BY_ID'] = $responsibleID;

				if($userID === 0)
				{
					$userID = $responsibleID;
				}
			}

			for($i = 0; $i < $emailQty; $i++)
			{
				$arLeadFields['FM']['EMAIL']['n'.($i + 1)] =
				array(
					'VALUE_TYPE' => 'WORK',
					'VALUE' => $arCommEmails[$i]
				);
			}

			$leadEntity = new CCrmLead(false);
			$leadID = $leadEntity->Add(
				$arLeadFields,
				true,
				array(
					'DISABLE_USER_FIELD_CHECK' => true,
					'REGISTER_SONET_EVENT' => true,
					'CURRENT_USER' => $responsibleID
				)
			);
			// TODO: log error
			if($leadID > 0)
			{
				$arBizProcErrors = array();
				CCrmBizProcHelper::AutoStartWorkflows(
					CCrmOwnerType::Lead,
					$leadID,
					CCrmBizProcEventType::Create,
					$arBizProcErrors
				);

				//Region automation
				\Thurly\Crm\Automation\Factory::runOnAdd(\CCrmOwnerType::Lead, $leadID);
				//End region

				$arCommData = array();
				for($i = 0; $i < $emailQty; $i++)
				{
					$arCommData[] = self::CreateComm(
						CCrmOwnerType::Lead,
						$leadID,
						$arCommEmails[$i]
					);
				}

				$arBindingData = array(
					self::PrepareEntityKey(CCrmOwnerType::Lead, $leadID) =>
					self::CreateBinding(CCrmOwnerType::Lead, $leadID)
				);
			}
		}

		// Terminate processing if no bindings are found.
		if(empty($arBindingData))
		{
			// Try to export vcf-files before exit if email from registered user
			if($addresserID > 0)
			{
				$dbAttachment = CMailAttachment::GetList(array(), array('MESSAGE_ID' => $msgID));
				while ($arAttachment = $dbAttachment->Fetch())
				{
					if(GetFileExtension(strtolower($arAttachment['FILE_NAME'])) === 'vcf')
					{
						if ($arAttachment['FILE_ID'])
							$arAttachment['FILE_DATA'] = CMailAttachment::getContents($arAttachment);
						self::TryImportVCard($arAttachment['FILE_DATA']);
					}
				}
			}
			return false;
		}

		// If owner info not defined set it by default
		if($ownerID <= 0 || $ownerTypeID <= 0)
		{
			if(count($arBindingData) > 1)
			{
				// Search owner in specified order: Contact, Company, Lead.
				$arTypeIDs = array(
					CCrmOwnerType::Contact,
					CCrmOwnerType::Company,
					CCrmOwnerType::Lead
				);

				foreach($arTypeIDs as $typeID)
				{
					foreach($arBindingData as &$arBinding)
					{
						if($arBinding['TYPE_ID'] === $typeID)
						{
							$ownerTypeID = $typeID;
							$ownerID = $arBinding['ID'];
							break;
						}
					}
					unset($arBinding);

					if($ownerID > 0 && $ownerTypeID > 0)
					{
						break;
					}
				}
			}

			if($ownerID <= 0 || $ownerTypeID <= 0)
			{
				$arBinding = array_shift(array_values($arBindingData));
				$ownerTypeID = $arBinding['TYPE_ID'];
				$ownerID = $arBinding['ID'];
			}
		}

		// Precessing of attachments -->
		$attachmentMaxSizeMb = intval(COption::GetOptionString('crm', 'email_attachment_max_size', 16));
		$attachmentMaxSize = $attachmentMaxSizeMb > 0 ? ($attachmentMaxSizeMb * 1048576) : 0;

		$arFilesData = array();
		$dbAttachment = CMailAttachment::GetList(array(), array('MESSAGE_ID' => $msgID));
		$arBannedAttachments = array();
		while ($arAttachment = $dbAttachment->Fetch())
		{
			if (GetFileExtension(strtolower($arAttachment['FILE_NAME'])) === 'vcf')
			{
				if ($arAttachment['FILE_ID'])
					$arAttachment['FILE_DATA'] = CMailAttachment::getContents($arAttachment);
				self::TryImportVCard($arAttachment['FILE_DATA']);
			}

			$fileSize = isset($arAttachment['FILE_SIZE']) ? intval($arAttachment['FILE_SIZE']) : 0;
			if($fileSize <= 0)
			{
				//Skip zero lenth files
				continue;
			}

			if($attachmentMaxSize > 0 && $fileSize > $attachmentMaxSize)
			{
				//File size limit  is exceeded
				$arBannedAttachments[] = array(
					'name' => $arAttachment['FILE_NAME'],
					'size' => $fileSize
				);
				continue;
			}

			if ($arAttachment['FILE_ID'] && empty($arAttachment['FILE_DATA']))
				$arAttachment['FILE_DATA'] = CMailAttachment::getContents($arAttachment);

			$arFilesData[] = array(
				'name' => $arAttachment['FILE_NAME'],
				'type' => $arAttachment['CONTENT_TYPE'],
				'content' => $arAttachment['FILE_DATA'],
				//'size' => $arAttachment['FILE_SIZE'], // HACK: Must be commented if use CFile:SaveForDB
				'MODULE_ID' => 'crm'
			);
		}
		//<-- Precessing of attachments

		// Remove extra new lines (fix for #31807)
		$body = preg_replace("/(\r\n|\n|\r)+/", PHP_EOL, $body);
		$encodedBody = htmlspecialcharsbx($body);

		// Creating of new event -->
		$arEventBindings = array();
		foreach($arBindingData as &$arBinding)
		{
			$arEventBindings[] = array(
				'ENTITY_TYPE' => $arBinding['TYPE_NAME'],
				'ENTITY_ID' => $arBinding['ID']
			);
		}
		unset($arBinding);

		$eventText  = '';
		$eventText .= '<b>'.GetMessage('CRM_EMAIL_SUBJECT').'</b>: '.$subject.PHP_EOL;
		$eventText .= '<b>'.GetMessage('CRM_EMAIL_FROM').'</b>: '.$addresserInfo['EMAIL'].PHP_EOL;
		$eventText .= '<b>'.GetMessage('CRM_EMAIL_TO').'</b>: '.implode($addresseeEmails, '; ').PHP_EOL;
		if(!empty($arBannedAttachments))
		{
			$eventText .= '<b>'.GetMessage('CRM_EMAIL_BANNENED_ATTACHMENTS', array('%MAX_SIZE%' => $attachmentMaxSizeMb)).'</b>: ';
			foreach($arBannedAttachments as &$attachmentInfo)
			{
				$eventText .= GetMessage(
					'CRM_EMAIL_BANNENED_ATTACHMENT_INFO',
					array(
						'%NAME%' => $attachmentInfo['name'],
						'%SIZE%' => round($attachmentInfo['size'] / 1048576, 1)
					)
				);
			}
			unset($attachmentInfo);
			$eventText .= PHP_EOL;
		}
		$eventText .= $encodedBody;

		$CCrmEvent = new CCrmEvent();
		$CCrmEvent->Add(
			array(
				'USER_ID' => $userID,
				'ENTITY' => array_values($arEventBindings),
				'ENTITY_TYPE' => CCrmOwnerType::ResolveName($ownerTypeID),
				'ENTITY_ID' => $ownerID,
				'EVENT_NAME' => GetMessage('CRM_EMAIL_GET_EMAIL'),
				'EVENT_TYPE' => 2,
				'EVENT_TEXT_1' => $eventText,
				'FILES' => $arFilesData,
			),
			false
		);
		// <-- Creating of new event

		// Creating new activity -->
		$siteID = '';
		$dbSites = CSite::GetList($by = 'sort', $order = 'desc', array('DEFAULT' => 'Y', 'ACTIVE' => 'Y'));
		$defaultSite = is_object($dbSites) ? $dbSites->Fetch() : null;
		if(is_array($defaultSite))
		{
			$siteID = $defaultSite['LID'];
		}
		if($siteID === '')
		{
			$siteID = 's1';
		}

		$storageTypeID =  CCrmActivity::GetDefaultStorageTypeID();
		$arElementIDs = array();
		foreach($arFilesData as $fileData)
		{
			$fileID = CFile::SaveFile($fileData, 'crm', true);
			if (!($fileID > 0))
				continue;

			$fileData = \CFile::getFileArray($fileID);
			if (empty($fileData))
				continue;

			if (trim($fileData['ORIGINAL_NAME']) == '')
				$fileData['ORIGINAL_NAME'] = $fileData['FILE_NAME'];
			$elementID = StorageManager::saveEmailAttachment(
				$fileData, $storageTypeID, $siteID,
				array('USER_ID' => $userID)
			);
			if($elementID > 0)
			{
				$arElementIDs[] = (int)$elementID;
			}
		}

		$descr = preg_replace("/(\r\n|\n|\r)/", '<br/>', htmlspecialcharsbx($body));
		$now = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL', $siteID);

		$direction = CCrmActivityDirection::Incoming;
		$completed = 'N'; // Incomming emails must be marked as 'Not Completed'.

		if ($addresserID > 0)
		{
			if (ActivitySettings::getValue(ActivitySettings::MARK_FORWARDED_EMAIL_AS_OUTGOING))
			{
				$direction = CCrmActivityDirection::Outgoing;
				$completed = 'Y';
			}

			\Thurly\Main\Config\Option::set(
				'crm', 'email_forwarded_cnt',
				\Thurly\Main\Config\Option::get('crm', 'email_forwarded_cnt', 0) + 1
			);
		}

		$arActivityFields = array(
			'OWNER_ID' => $ownerID,
			'OWNER_TYPE_ID' => $ownerTypeID,
			'TYPE_ID' =>  CCrmActivityType::Email,
			'ASSOCIATED_ENTITY_ID' => 0,
			'PARENT_ID' => $parentID,
			'SUBJECT' => $subject,
			'START_TIME' => $now,
			'END_TIME' => $now,
			'COMPLETED' => $completed,
			'AUTHOR_ID' => $userID,
			'RESPONSIBLE_ID' => $userID,
			'PRIORITY' => CCrmActivityPriority::Medium,
			'DESCRIPTION' => $descr,
			'DESCRIPTION_TYPE' => CCrmContentType::Html,
			'DIRECTION' => $direction,
			'LOCATION' => '',
			'NOTIFY_TYPE' => CCrmActivityNotifyType::None,
			'STORAGE_TYPE_ID' => $storageTypeID,
			'STORAGE_ELEMENT_IDS' => $arElementIDs
		);

		$arActivityFields['BINDINGS'] = array();
		foreach($arBindingData as &$arBinding)
		{
			$entityTypeID = $arBinding['TYPE_ID'];
			$entityID = $arBinding['ID'];

			if($entityTypeID <= 0 || $entityID <= 0)
			{
				continue;
			}

			$arActivityFields['BINDINGS'][] =
				array(
					'OWNER_TYPE_ID' => $entityTypeID,
					'OWNER_ID' => $entityID
				);
		}
		unset($arBinding);

		if (!empty($arCommData))
			$arActivityFields['COMMUNICATIONS'] = $arCommData;

		$activityID = CCrmActivity::Add($arActivityFields, false, false, array('REGISTER_SONET_EVENT' => true));
		if ($activityID > 0)
		{
			if ($direction === CCrmActivityDirection::Incoming)
			{
				\Thurly\Crm\Automation\Trigger\EmailTrigger::execute($arActivityFields['BINDINGS'], $arActivityFields);
			}
		}

		//Notity responsible user
		if($userID > 0 && $direction === CCrmActivityDirection::Incoming)
		{
			CCrmActivity::Notify($arActivityFields, CCrmNotifierSchemeType::IncomingEmail);
		}
		// <-- Creating new activity
		return true;
	}
	public static function EmailMessageCheck($arFields, $ACTION_VARS)
	{
		$arACTION_VARS = explode('&', $ACTION_VARS);
		for ($i=0, $ic=count($arACTION_VARS); $i < $ic ; $i++)
		{
			$v = $arACTION_VARS[$i];
			if($pos = strpos($v, '='))
			{
				$name = substr($v, 0, $pos);
				${$name} = urldecode(substr($v, $pos+1));
			}
		}
		return true;
	}
	public static function PrepareVars()
	{
		$str = 'W_CRM_ENTITY_REGEXP_LEAD='.urlencode($_REQUEST['W_CRM_ENTITY_REGEXP_LEAD']).
			'&W_CRM_ENTITY_REGEXP_CONTACT='.urlencode($_REQUEST['W_CRM_ENTITY_REGEXP_CONTACT']).
			'&W_CRM_ENTITY_REGEXP_COMPANY='.urlencode($_REQUEST['W_CRM_ENTITY_REGEXP_COMPANY']).
			'&W_CRM_ENTITY_REGEXP_DEAL='.urlencode($_REQUEST['W_CRM_ENTITY_REGEXP_DEAL']);
		return $str;
	}
	public static function BeforeSendMail($arMessageFields)
	{
		// ADD ADDITIONAL HEADERS
		$postingID = self::ExtractPostingID($arMessageFields);
		if($postingID <= 0)
		{
			return $arMessageFields;
		}

		$dbActivity = CAllCrmActivity::GetList(
			array(),
			array(
				'=TYPE_ID' => CCrmActivityType::Email,
				'=ASSOCIATED_ENTITY_ID' => $postingID,
				'CHECK_PERMISSIONS'=>'N'
			),
			false,
			false,
			array('SETTINGS'),
			array()
		);

		$arActivity = $dbActivity ? $dbActivity->Fetch() : null;

		if(!$arActivity)
		{
			return $arMessageFields;
		}

		$settings = isset($arActivity['SETTINGS']) && is_array($arActivity['SETTINGS']) ? $arActivity['SETTINGS'] : array();
		$messageHeaders = isset($settings['MESSAGE_HEADERS']) ? $settings['MESSAGE_HEADERS'] : array();
		if(empty($messageHeaders))
		{
			return $arMessageFields;
		}

		$header = isset($arMessageFields['HEADER']) ? $arMessageFields['HEADER'] : '';
		$eol = CEvent::GetMailEOL();
		foreach($messageHeaders as $headerName => &$headerValue)
		{
			if(strlen($header) > 0)
			{
				$header .= $eol;
			}

			$header .= $headerName.': '.$headerValue;
		}
		unset($headerValue);
		$arMessageFields['HEADER'] = $header;

		$cidRegex = sprintf(
			'/Content-Disposition: attachment; filename="(.+?)_(bxacid.[0-9a-f]{2,8}@[0-9a-f]{2,8}.crm)"(%s)/i',
			'\x'.join('\x', str_split(bin2hex(\Thurly\Main\Mail\Mail::getMailEol()), 2))
		);
		if (preg_match_all($cidRegex, $arMessageFields['BODY'], $matches, PREG_SET_ORDER) > 0)
		{
			foreach ($matches as $set)
			{
				$arMessageFields['BODY'] = str_replace(
					$set[0],
					sprintf(
						'Content-Disposition: attachment; filename="%s"%sContent-ID: <%s>%s',
						$set[1], $set[3], $set[2], $set[3]
					),
					$arMessageFields['BODY']
				);

				$arMessageFields['BODY'] = str_replace(
					sprintf('%s_%s', $set[1], $set[2]),
					$set[1],
					$arMessageFields['BODY']
				);
			}
		}

		return $arMessageFields;
	}

	public static function OnImapEmailMessageObsolete(\Thurly\Main\Event $event)
	{
		global $DB;

		$resp = $event->getParameter('user');
		$hash = $event->getParameter('hash');

		$res = \Thurly\Crm\Activity\MailMetaTable::getList(array(
			'select' => array('ACTIVITY_ID'),
			'filter' => array('=MSG_HEADER_HASH' => $hash),
		));

		while ($mailMeta = $res->fetch())
		{
			if ($activity = \CCrmActivity::getById($mailMeta['ACTIVITY_ID'], false))
			{
				if ($activity['TYPE_ID'] != \CCrmActivityType::Email || $activity['DIRECTION'] != \CCrmActivityDirection::Incoming)
					break;

				if ($resp > 0 && $activity['RESPONSIBLE_ID'] != $resp)
					break;

				$response = $DB->query(sprintf('SELECT 1 FROM b_crm_act WHERE PARENT_ID = %u', $activity['ID']))->fetch();
				if (!$response)
				{
					$bindRes = $DB->query(sprintf(
						'SELECT OWNER_ID FROM b_crm_act_bind WHERE ACTIVITY_ID = %u AND OWNER_TYPE_ID = %u',
						$activity['ID'], \CCrmOwnerType::Lead
					));

					$leadIds = array();
					while ($bind = $bindRes->fetch())
						$leadIds[] = $bind['OWNER_ID'];

					\CCrmActivity::delete($activity['ID'], false, false);
					\Thurly\Crm\Activity\MailMetaTable::delete($activity['ID']);

					if (!empty($leadIds))
					{
						$leadRes = \CCrmLead::getListEx(
							array(),
							array(
								'ID'                => $leadIds,
								'ORIGINATOR_ID'     => 'email-tracker',
								'STATUS_ID'         => 'NEW',
								'CHECK_PERMISSIONS' => 'N'
							),
							false, false,
							array('ID', 'DATE_CREATE', 'DATE_MODIFY')
						);

						while ($lead = $leadRes->fetch())
						{
							if ($lead['DATE_CREATE'] == $lead['DATE_MODIFY'])
							{
								$response = $DB->query(sprintf(
									'SELECT 1 FROM b_crm_act_bind WHERE OWNER_ID = %u AND OWNER_TYPE_ID = %u',
									$lead['ID'], \CCrmOwnerType::Lead
								))->fetch();
								if (!$response)
								{
									$obsoleteLead = new \CCrmLead(false);
									$obsoleteLead->delete($lead['ID']);
								}
							}
						}
					}
				}

				break;
			}
		}
	}

	public static function OnImapEmailMessageModified(\Thurly\Main\Event $event)
	{
		$resp = $event->getParameter('user');
		$hash = $event->getParameter('hash');
		$seen = $event->getParameter('seen');

		$res = \Thurly\Crm\Activity\MailMetaTable::getList(array(
			'select' => array('ACTIVITY_ID'),
			'filter' => array('=MSG_HEADER_HASH' => $hash),
		));

		while ($mailMeta = $res->fetch())
		{
			if ($activity = \CCrmActivity::getById($mailMeta['ACTIVITY_ID'], false))
			{
				if ($activity['TYPE_ID'] != \CCrmActivityType::Email || $activity['DIRECTION'] != \CCrmActivityDirection::Incoming)
					break;

				if ($resp > 0 && $activity['RESPONSIBLE_ID'] != $resp)
					break;

				\CCrmActivity::update($activity['ID'], array(
					'COMPLETED' => $seen ? 'Y' : 'N',
				), false);

				break;
			}
		}
	}

	public static function OnActivityModified(\Thurly\Main\Event $event)
	{
		$before  = $event->getParameter('before');
		$current = $event->getParameter('current');

		if ($before['COMPLETED'] != $current['COMPLETED'])
		{
			if ($current['TYPE_ID'] == \CCrmActivityType::Email && $current['DIRECTION'] == \CCrmActivityDirection::Incoming)
			{
				$mailMeta = \Thurly\Crm\Activity\MailMetaTable::getList(array(
					'select' => array('HASH' => 'MSG_HEADER_HASH'),
					'filter' => array('ACTIVITY_ID' => $current['ID']),
				))->fetch();

				if ($mailMeta && \CModule::includeModule('mail'))
				{
					\Thurly\Mail\Helper::updateImapMessage($current['RESPONSIBLE_ID'], $mailMeta['HASH'], array(
						'seen' => $current['COMPLETED'] == 'Y',
					), $error);
				}
			}
		}
	}

	public static function OnActivityDelete($id)
	{
		\Thurly\Crm\Activity\MailMetaTable::delete($id);
	}

	public static function OnOutgoingMessageRead($fields)
	{
		if (preg_match('/^(\d+)-[0-9a-z]+$/i', trim($fields['urn']), $matches))
		{
			$activity = \CCrmActivity::getList(
				array(),
				array(
					'ID' => $matches[1],
					'=%URN' => $matches[0],
					'DIRECTION' => \CCrmActivityDirection::Outgoing,
					'CHECK_PERMISSIONS' => 'N',
				),
				false,
				false,
				array('ID', 'RESPONSIBLE_ID', 'SETTINGS')
			)->fetch();

			if (!empty($activity) and empty($activity['SETTINGS']['READ_CONFIRMED']) || $activity['SETTINGS']['READ_CONFIRMED'] <= 0)
			{
				$activity['SETTINGS']['READ_CONFIRMED'] = time();
				\CCrmActivity::update($activity['ID'], array('SETTINGS' => $activity['SETTINGS']), false, false);

				if (\Thurly\Main\Loader::includeModule('pull'))
				{
					//$datetimeFormat = \Thurly\Main\Loader::includeModule('intranet')
					//	? \CIntranetUtils::getCurrentDatetimeFormat() : false;
					\Thurly\Pull\Event::add($activity['RESPONSIBLE_ID'], array(
						'module_id' => 'crm',
						'command' => 'activity_email_read_confirmed',
						'params' => array(
							'ID' => $activity['ID'],
							'READ_CONFIRMED' => $activity['SETTINGS']['READ_CONFIRMED'],
							//'READ_CONFIRMED_FORMATTED' => \CComponentUtil::getDateTimeFormatted(
							//	$activity['SETTINGS']['READ_CONFIRMED']+\CTimeZone::getOffset(),
							//	$datetimeFormat,
							//	\CTimeZone::getOffset()
							//),
						),
					));
				}
			}
		}
	}

	public static function GetEOL()
	{
		return CEvent::GetMailEOL();
	}
}

class CCrmEMailCodeAllocation
{
	const None = 0;
	const Subject = 1;
	const Body = 2;
	private static $ALL_DESCRIPTIONS = null;
	public static function GetAllDescriptions()
	{
		if(!self::$ALL_DESCRIPTIONS)
		{
			self::$ALL_DESCRIPTIONS = array(
				self::Body => GetMessage('CRM_EMAIL_CODE_ALLOCATION_BODY'),
				self::Subject => GetMessage('CRM_EMAIL_CODE_ALLOCATION_SUBJECT'),
				self::None => GetMessage('CRM_EMAIL_CODE_ALLOCATION_NONE')
			);
		}

		return self::$ALL_DESCRIPTIONS;
	}
	public static function PrepareListItems()
	{
		return CCrmEnumeration::PrepareListItems(self::GetAllDescriptions());
	}
	public static function IsDefined($value)
	{
		$value = intval($value);
		return $value >= self::None && $value <= self::Body;
	}
	public static function SetCurrent($value)
	{
		if(!self::IsDefined($value))
		{
			$value = self::Body;
		}

		COption::SetOptionString('crm', 'email_service_code_allocation', $value);
	}
	public static function GetCurrent()
	{
		$value = intval(COption::GetOptionString('crm', 'email_service_code_allocation', self::Body));
		return self::IsDefined($value) ? $value : self::Body;
	}
}
?>
