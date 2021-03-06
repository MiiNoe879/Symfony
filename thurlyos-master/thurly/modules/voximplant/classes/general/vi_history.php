<?
IncludeModuleLangFile(__FILE__);

use Thurly\Voximplant as VI;
use Thurly\Main\Application;
use Thurly\Main\IO;
use Thurly\Main\Event;
use Thurly\Main\EventManager;

class CVoxImplantHistory
{
	const DURATION_FORMAT_FULL = 'full';
	const DURATION_FORMAT_BRIEF = 'brief';
	const CALL_LOCK_PREFIX = 'vi_call';

	public static function Add($params)
	{
		$callId = (string)$params["CALL_ID"];
		if($callId == '')
		{
			CHTTP::SetStatus('400 Bad Request');
			return false;
		}

		$lockAcquired = static::getLock($callId);
		if(!$lockAcquired)
		{
			CHTTP::SetStatus('409 Conflict');
			return false;
		}

		$call = VI\CallTable::getByCallId($params['CALL_ID']);
		if ($call)
		{
			VI\CallTable::delete($call['ID']);
		}

		$statisticRecord = VI\StatisticTable::getByCallId($params['CALL_ID']);
		if($statisticRecord)
		{
			self::WriteToLog('Duplicating statistic record, skipping');
			return false;
		}

		$config = false;
		if(is_array($call) && $call['CONFIG_ID'] > 0)
		{
			$config = CVoxImplantConfig::GetConfig($call['CONFIG_ID']);
		}
		else if(isset($params['ACCOUNT_SEARCH_ID']))
		{
			$config = CVoxImplantConfig::GetConfigBySearchId($params['ACCOUNT_SEARCH_ID']);
		}
		$isPortalCall = ($call && $call['PORTAL_USER_ID'] > 0);
		$arFields = array(
			"ACCOUNT_ID" =>			$params["ACCOUNT_ID"],
			"APPLICATION_ID" =>		$params["APPLICATION_ID"],
			"APPLICATION_NAME" =>	isset($params["APPLICATION_NAME"])?$params["APPLICATION_NAME"]: '-',
			"INCOMING" =>			$params["INCOMING"],
			"CALL_START_DATE" =>	$call? $call['DATE_CREATE']: new Thurly\Main\Type\DateTime(),
			"CALL_DURATION" =>		isset($params["CALL_DURATION"])? $params["CALL_DURATION"]: $params["DURATION"],
			"CALL_STATUS" =>		$params["CALL_STATUS"],
			"CALL_FAILED_CODE" =>	$params["CALL_FAILED_CODE"],
			"CALL_FAILED_REASON" =>	$params["CALL_FAILED_REASON"],
			"COST" =>				$params["COST_FINAL"],
			"COST_CURRENCY" =>		$params["COST_CURRENCY"],
			"CALL_VOTE" =>			intval($params["CALL_VOTE"]),
			"CALL_ID" =>			$params["CALL_ID"],
			"CALL_CATEGORY" =>		$params["CALL_CATEGORY"],
			"SESSION_ID" =>			$call ? $call["SESSION_ID"] : $params["SESSION_ID"],
			"TRANSCRIPT_PENDING" => $params['TRANSCRIPT_PENDING'] == 'Y' ? 'Y' : 'N',
		);

		if (strlen($params["PHONE_NUMBER"]) > 0)
			$arFields["PHONE_NUMBER"] = $params["PHONE_NUMBER"];

		if (strlen($params["CALL_DIRECTION"]) > 0)
			$arFields["CALL_DIRECTION"] = $params["CALL_DIRECTION"];

		if (strlen($params["PORTAL_NUMBER"]) > 0)
			$arFields["PORTAL_NUMBER"] = $params["PORTAL_NUMBER"];

		if (strlen($params["ACCOUNT_SEARCH_ID"]) > 0)
			$arFields["PORTAL_NUMBER"] = $params["ACCOUNT_SEARCH_ID"];

		if($arFields['CALL_VOTE'] < 1 || $arFields['CALL_VOTE'] > 5)
			$arFields['CALL_VOTE'] = null;

		if (strlen($params["CALL_LOG"]) > 0)
			$arFields["CALL_LOG"] = $params["CALL_LOG"];

		if($call && intval($call['USER_ID']) > 0)
		{
			$arFields["PORTAL_USER_ID"] = $call["USER_ID"];
		}
		else if (intval($params["PORTAL_USER_ID"]) > 0)
		{
			$arFields["PORTAL_USER_ID"] = intval($params["PORTAL_USER_ID"]);
		}
		else if ($arFields['INCOMING'] == CVoxImplantMain::CALL_INFO)
		{
			// infocalls have no responsible
			$arFields["PORTAL_USER_ID"] = null;
		}
		else
		{
			$arFields["PORTAL_USER_ID"] = intval(self::detectResponsible($call, $config, $params['PHONE_NUMBER']));
		}

		if($call && $call['CRM_ENTITY_TYPE'] && $call['CRM_ENTITY_ID'])
		{
			$arFields['CRM_ENTITY_TYPE'] = $call['CRM_ENTITY_TYPE'];
			$arFields['CRM_ENTITY_ID'] = $call['CRM_ENTITY_ID'];
		}
		else if($params['CRM_ENTITY_TYPE'] && $params['CRM_ENTITY_ID'])
		{
			$arFields['CRM_ENTITY_TYPE'] = $params['CRM_ENTITY_TYPE'];
			$arFields['CRM_ENTITY_ID'] = $params['CRM_ENTITY_ID'];
		}
		else if(!$isPortalCall)
		{
			$crmData = CVoxImplantCrmHelper::GetCrmEntity($params['PHONE_NUMBER'], 0, false);
			if(is_array($crmData))
			{
				$arFields['CRM_ENTITY_TYPE'] = $crmData['ENTITY_TYPE_NAME'];
				$arFields['CRM_ENTITY_ID'] = $crmData['ENTITY_ID'];
			}
		}

		if(CVoxImplantCrmHelper::shouldCreateLead($arFields, $config) && CModule::IncludeModule('crm'))
		{
			$leadId = CVoxImplantCrmHelper::AddLead(array(
				'USER_ID' => $arFields['PORTAL_USER_ID'],
				'PHONE_NUMBER' => $arFields['PHONE_NUMBER'],
				'SEARCH_ID' => $config['SEARCH_ID'],
				'CRM_SOURCE' => $config['CRM_SOURCE'],
				'INCOMING' => $arFields['INCOMING']
			));
			if($leadId > 0 )
			{
				$arFields['CRM_ENTITY_TYPE'] = CCrmOwnerType::LeadName;
				$arFields['CRM_ENTITY_ID'] = $leadId;
			}
		}

		if($call && $call['COMMENT'])
			$arFields['COMMENT'] = $call['COMMENT'];

		$insertResult = Thurly\VoxImplant\StatisticTable::add($arFields);
		if (!$insertResult)
		{
			static::releaseLock($callId);
			return false;
		}

		$arFields['ID'] = $insertResult->getId();

		if (!$isPortalCall && (($call && $call['CRM'] == 'Y') || $params['CRM'] == 'Y'))
		{
			if($call['CRM_ACTIVITY_ID'] && CVoxImplantCrmHelper::shouldAttachCallToActivity($arFields, $call['CRM_ACTIVITY_ID']))
			{
				CVoxImplantCrmHelper::attachCallToActivity($arFields, $call['CRM_ACTIVITY_ID']);
				$arFields['CRM_ACTIVITY_ID'] = $call['CRM_ACTIVITY_ID'];
			}
			else
			{
				$arFields['CRM_ACTIVITY_ID'] = CVoxImplantCrmHelper::AddCall($arFields, array(
					'WORKTIME_SKIPPED' => $call['WORKTIME_SKIPPED'],
					'CRM_BINDINGS' => $call['CRM_BINDINGS']
				));
			}

			VI\StatisticTable::update($arFields['ID'], array(
				'CRM_ACTIVITY_ID' => $arFields['CRM_ACTIVITY_ID']
			));

			if(isset($arFields['CRM_ENTITY_TYPE']) && isset($arFields['CRM_ENTITY_ID']))
			{
				$viMain = new CVoxImplantMain($arFields["PORTAL_USER_ID"]);
				$dialogData = $viMain->GetDialogInfo($arFields['PHONE_NUMBER'], '', false);
				if(!$dialogData['UNIFIED'])
				{
					CVoxImplantMain::UpdateChatInfo(
						$dialogData['DIALOG_ID'],
						array(
							'CRM' => $call['CRM'],
							'CRM_ENTITY_TYPE' => $arFields['CRM_ENTITY_TYPE'],
							'CRM_ENTITY_ID' => $arFields['CRM_ENTITY_ID'],
							'PHONE_NUMBER' => $arFields['PHONE_NUMBER']
						)
					);
				}
			}
		}

		$chatMessage = self::GetMessageForChat($arFields, $params['URL'] != '');
		if($chatMessage != '')
		{
			$attach = null;

			if(CVoxImplantConfig::GetChatAction() == CVoxImplantConfig::INTERFACE_CHAT_APPEND)
			{
				$attach = static::GetAttachForChat($arFields, $params['URL'] != '');
			}

			if($attach)
				self::SendMessageToChat($arFields["PORTAL_USER_ID"], $arFields["PHONE_NUMBER"], $arFields["INCOMING"], null, $attach);
			else
				self::SendMessageToChat($arFields["PORTAL_USER_ID"], $arFields["PHONE_NUMBER"], $arFields["INCOMING"], $chatMessage);
		}

		if (strlen($params['URL']) > 0)
		{
			$attachToCrm = $call['CRM'] == 'Y';

			$startDownloadAgent = false;

			$recordLimit = COption::GetOptionInt("voximplant", "record_limit");
			if ($recordLimit > 0 && !CVoxImplantAccount::IsPro())
			{
				$sipConnectorActive = CVoxImplantConfig::GetModeStatus(CVoxImplantConfig::MODE_SIP);
				if ($params['PORTAL_TYPE'] == CVoxImplantConfig::MODE_SIP && $sipConnectorActive)
				{
					$startDownloadAgent = true;
				}
				else
				{
					$recordMonth = COption::GetOptionInt("voximplant", "record_month");
					if (!$recordMonth)
					{
						$recordMonth = date('Ym');
						COption::SetOptionInt("voximplant", "record_month", $recordMonth);
					}
					$recordCount = CGlobalCounter::GetValue('vi_records', CGlobalCounter::ALL_SITES);
					if ($recordCount < $recordLimit)
					{
						CGlobalCounter::Increment('vi_records', CGlobalCounter::ALL_SITES, false);
						$startDownloadAgent = true;
					}
					else
					{
						if ($recordMonth < date('Ym'))
						{
							COption::SetOptionInt("voximplant", "record_month", date('Ym'));
							CGlobalCounter::Set('vi_records', 1, CGlobalCounter::ALL_SITES, '', false);
							CGlobalCounter::Set('vi_records_skipped', 0, CGlobalCounter::ALL_SITES, '', false);
							$startDownloadAgent = true;
						}
						else
						{
							CGlobalCounter::Increment('vi_records_skipped', CGlobalCounter::ALL_SITES, false);
						}
					}
					CVoxImplantHistory::WriteToLog(Array(
						'limit' => $recordLimit,
						'saved' => CGlobalCounter::GetValue('vi_records', CGlobalCounter::ALL_SITES),
						'skipped' => CGlobalCounter::GetValue('vi_records_skipped', CGlobalCounter::ALL_SITES),
						'save to portal' => $startDownloadAgent? 'Y':'N',
					), 'STATUS OF RECORD LIMIT');
				}
			}
			else
			{
				$startDownloadAgent = true;
			}

			if ($startDownloadAgent)
			{
				self::DownloadAgent($insertResult->getId(), $params['URL'], $attachToCrm);
			}
		}

		if (strlen($params["ACCOUNT_PAYED"]) > 0 && in_array($params["ACCOUNT_PAYED"], Array('Y', 'N')))
		{
			CVoxImplantAccount::SetPayedFlag($params["ACCOUNT_PAYED"]);
		}

		if($call && $call['CRM_LEAD'] > 0 && CVoxImplantConfig::GetLeadWorkflowExecution() == CVoxImplantConfig::WORKFLOW_START_DEFERRED)
		{
			CVoxImplantCrmHelper::StartLeadWorkflow($call['CRM_LEAD']);
		}

		if($call && $call['CRM_CALL_LIST'])
		{
			CVoxImplantCrmHelper::attachCallToCallList($call['CRM_CALL_LIST'], $arFields);
		}

		/* repeat missed callback, if neeeded */
		if($call && $call['INCOMING'] == CVoxImplantMain::CALL_CALLBACK && $params["CALL_FAILED_CODE"] == '304')
		{
			if(self::shouldRepeatCallback($call, $config))
			{
				self::repeatCallback($call, $config);
			}
		}

		static::sendCallEndEvent($arFields);
		if($arFields['INCOMING'] == CVoxImplantMain::CALL_INFO)
		{
			$callEvent = new Event(
				'voximplant',
				'OnInfoCallResult',
				array(
					$arFields['CALL_ID'],
					array(
						'RESULT' => ($arFields['CALL_FAILED_CODE'] == '200'),
						'CODE' => $arFields['CALL_FAILED_CODE'],
						'REASON' => $arFields['CALL_FAILED_REASON']
					)
				)
			);
			EventManager::getInstance()->send($callEvent);
		}
		static::releaseLock($callId);
		return true;
	}

	public static function DownloadAgent($historyID, $recordUrl, $attachToCrm = true, $retryOnFailure = true)
	{
		self::WriteToLog('Downloading record ' . $recordUrl);
		$historyID = intval($historyID);
		$attachToCrm = ($attachToCrm == true);
		if (strlen($recordUrl) <= 0 || $historyID <= 0)
		{
			return false;
		}

		$http = new \Thurly\Main\Web\HttpClient(array(
			"disableSslVerification" => true
		));
		$http->query('GET', $recordUrl);
		if ($http->getStatus() != 200)
		{
			if($retryOnFailure)
			{
				CAgent::AddAgent(
					"CVoxImplantHistory::DownloadAgent('{$historyID}','".EscapePHPString($recordUrl, "'")."','{$attachToCrm}', false);",
					'voximplant', 'N', 60, '', 'Y', ConvertTimeStamp(time() + CTimeZone::GetOffset() + 60, 'FULL')
				);
			}

			return false;
		}

		$history = VI\StatisticTable::getById($historyID);
		$arHistory = $history->fetch();

		try
		{
			$fileName = $http->getHeaders()->getFilename();
			$urlComponents = parse_url($recordUrl);
			if($fileName != '')
			{
				$tempPath = \CFile::GetTempName('', bx_basename($fileName));
			}
			else if ($urlComponents && strlen($urlComponents["path"]) > 0)
			{
				$tempPath = \CFile::GetTempName('', bx_basename($urlComponents["path"]));
			}
			else
			{
				$tempPath = \CFile::GetTempName('', bx_basename($recordUrl));
			}

			IO\Directory::createDirectory(IO\Path::getDirectory($tempPath));
			if(IO\Directory::isDirectoryExists(IO\Path::getDirectory($tempPath)) === false)
			{
				self::WriteToLog('Error creating temporary directory ' . $tempPath);
				return false;
			}

			self::WriteToLog('Downloading to temporary file ' . $tempPath);
			$file = new IO\File($tempPath);
			$handler = $file->open("w+");
			if($handler === false)
			{
				self::WriteToLog('Error opening temporary file ' . $tempPath);
				return false;
			}

			$http->setOutputStream($handler);
			$http->getResult();
			$file->close();

			$recordFile = CFile::MakeFileArray($tempPath);
			if (is_array($recordFile) && $recordFile['size'] && $recordFile['size'] > 0)
			{
				if(strpos($recordFile['name'], '.') === false)
					$recordFile['name'] = $recordFile['name'] . '.mp3';

				$recordFile['MODULE_ID'] = 'voximplant';
				$fileID = CFile::SaveFile($recordFile, 'voximplant', true);
				if(is_int($fileID) && $fileID > 0)
				{
					$elementID = CVoxImplantDiskHelper::SaveFile(
						$arHistory,
						CFile::GetFileArray($fileID),
						CSite::GetDefSite()
					);
					$elementID = intval($elementID);
					if($attachToCrm && $elementID> 0)
					{
						CVoxImplantCrmHelper::AttachRecordToCall(Array(
							'CALL_ID' => $arHistory['CALL_ID'],
							'CALL_RECORD_ID' => $fileID,
							'CALL_WEBDAV_ID' => $elementID,
						));
					}
					VI\StatisticTable::update($historyID, Array('CALL_RECORD_ID' => $fileID, 'CALL_WEBDAV_ID' => $elementID));
				}
			}
		}
		catch (Exception $ex)
		{
			self::WriteToLog('Error caught during downloading record: ' . PHP_EOL . print_r($ex, true));
		}

		return false;
	}

	public static function GetForPopup($id)
	{
		$id = intval($id);
		if ($id <= 0)
			return false;

		$history = VI\StatisticTable::getById($id);
		$params = $history->fetch();
		if (!$params)
			return false;

		$params = self::PrepereData($params);

		$arResult = Array(
			'PORTAL_USER_ID' => $params['PORTAL_USER_ID'],
			'PHONE_NUMBER' => $params['PHONE_NUMBER'],
			'PHONE_NUMBER_FORMATTED' => \Thurly\Main\PhoneNumber\Parser::getInstance()->parse($params['PHONE_NUMBER'])->format(),
			'INCOMING_TEXT' => $params['INCOMING_TEXT'],
			'CALL_ICON' => $params['CALL_ICON'],
			'CALL_FAILED_CODE' => $params['CALL_FAILED_CODE'],
			'CALL_FAILED_REASON' => $params['CALL_FAILED_REASON'],
			'CALL_DURATION_TEXT' => $params['CALL_DURATION_TEXT'],
			'COST_TEXT' => $params['COST_TEXT'],
			'CALL_RECORD_HREF' => $params['CALL_RECORD_HREF'],
		);

		return $arResult;
	}

	public static function PrepereData($params)
	{
		if ($params["INCOMING"] == "N")
		{
			$params["INCOMING"] = CVoxImplantMain::CALL_OUTGOING;
		}
		else if ($params["INCOMING"] == "N")
		{
			$params["INCOMING"] = CVoxImplantMain::CALL_INCOMING;
		}
		if ($params["PHONE_NUMBER"] == "hidden")
		{
			$params["PHONE_NUMBER"] = GetMessage("IM_PHONE_NUMBER_HIDDEN");
		}

		$params["CALL_FAILED_REASON"] = static::getStatusText($params["CALL_FAILED_CODE"]);
		$params["INCOMING_TEXT"] = static::getDirectionText($params["INCOMING"]);

		if ($params["INCOMING"] == CVoxImplantMain::CALL_OUTGOING)
		{
			if ($params["CALL_FAILED_CODE"] == 200)
				$params["CALL_ICON"] = 'outgoing';
		}
		else if ($params["INCOMING"] == CVoxImplantMain::CALL_INCOMING)
		{
			if ($params["CALL_FAILED_CODE"] == 200)
				$params["CALL_ICON"] = 'incoming';
		}
		else if ($params["INCOMING"] == CVoxImplantMain::CALL_INCOMING_REDIRECT)
		{
			if ($params["CALL_FAILED_CODE"] == 200)
				$params["CALL_ICON"] = 'incoming-redirect';
		}
		else if($params["INCOMING"] == CVoxImplantMain::CALL_CALLBACK)
		{
			if ($params["CALL_FAILED_CODE"] == 200)
				$params["CALL_ICON"] = 'incoming'; //todo: icon?
		}
		else if($params["INCOMING"] == CVoxImplantMain::CALL_INFO)
		{
			if ($params["CALL_FAILED_CODE"] == 200)
				$params["CALL_ICON"] = 'outgoing';
		}

		if ($params["CALL_FAILED_CODE"] == 304)
		{
			$params["CALL_ICON"] = 'skipped';
		}
		else if ($params["CALL_FAILED_CODE"] != 200)
		{
			$params["CALL_ICON"] = 'decline';
		}

		$params["CALL_DURATION_TEXT"] = static::convertDurationToText($params['CALL_DURATION']);

		if (CModule::IncludeModule("catalog"))
		{
			$params["COST_TEXT"] = FormatCurrency($params["COST"], ($params["COST_CURRENCY"] == "RUR" ? "RUB" : $params["COST_CURRENCY"]));
			if(isset($params['TRANSCRIPT_COST']) && $params['TRANSCRIPT_COST'] > 0)
			{
				$params["TRANSCRIPT_COST_TEXT"] =  FormatCurrency($params["TRANSCRIPT_COST"], ($params["COST_CURRENCY"] == "RUR" ? "RUB" : $params["COST_CURRENCY"]));
			}
		}
		else
		{
			$params["COST_TEXT"] = $params["COST"]." ".GetMessage("VI_CURRENCY_".$params["COST_CURRENCY"]);
			if(isset($params['TRANSCRIPT_COST']) && $params['TRANSCRIPT_COST'] > 0)
			{
				$params["TRANSCRIPT_COST_TEXT"] =  $params["TRANSCRIPT_COST"]." ".GetMessage("VI_CURRENCY_".$params["COST_CURRENCY"]);
			}
		}

		if (!$params["COST_TEXT"])
		{
			$params["COST_TEXT"] = '-';
		}

		if (intval($params["CALL_RECORD_ID"]) > 0)
		{
			$recordFile = CFile::GetFileArray($params["CALL_RECORD_ID"]);
			if ($recordFile !== false)
			{
				$params["CALL_RECORD_HREF"] = $recordFile['SRC'];
			}
		}

		$params["CALL_WEBDAV_ID"] = (int)$params["CALL_WEBDAV_ID"];
		if($params["CALL_WEBDAV_ID"] > 0 && \Thurly\Main\Loader::includeModule('disk'))
		{
			$fileId = $params["CALL_WEBDAV_ID"];
			$file = \Thurly\Disk\File::loadById($fileId);
			if(!is_null($file))
				$params['CALL_RECORD_DOWNLOAD_URL'] = \Thurly\Disk\Driver::getInstance()->getUrlManager()->getUrlForDownloadFile($file, true);
		}

		return $params;
	}

	public static function TransferMessage($userId, $transferUserId, $phoneNumber, $transferPhone = '')
	{
		$userName = '';
		$arSelect = Array("ID", "LAST_NAME", "NAME", "LOGIN", "SECOND_NAME", "PERSONAL_GENDER");
		$dbUsers = CUser::GetList(($sort_by = false), ($dummy=''), array('ID' => $transferUserId), array('FIELDS' => $arSelect));
		if ($arUser = $dbUsers->Fetch())
			$userName = CUser::FormatName(CSite::GetNameFormat(false), $arUser, true, false);

		self::SendMessageToChat(
			$userId,
			$phoneNumber,
			CVoxImplantMain::CALL_INCOMING_REDIRECT,
			GetMessage('VI_CALL_TRANSFER', Array('#USER#' => $userName)).($transferPhone != '' ? ' ('.$transferPhone.')' : '')
		);

		return true;
	}

	public static function SendMessageToChat($userId, $phoneNumber, $incomingType, $message, $attach = null)
	{
		$ViMain = new CVoxImplantMain($userId);
		$dialogInfo = $ViMain->GetDialogInfo($phoneNumber, "", false);
		$ViMain->SendChatMessage($dialogInfo['DIALOG_ID'], $incomingType, $message, $attach);

		return true;
	}

	/**
	 * Creates message for the chat associated with phone number.
	 * @param array $callFields
	 * @param bool $hasRecord
	 * @return string
	 */
	public static function GetMessageForChat($callFields, $hasRecord = false, $prependPlus = true)
	{
		$result = '';
		if (strlen($callFields["PHONE_NUMBER"]) > 0 && $callFields["PORTAL_USER_ID"] > 0 && $callFields["CALL_FAILED_CODE"] != 423)
		{
			$formattedNumber = \Thurly\Main\PhoneNumber\Parser::getInstance()->parse($callFields["PHONE_NUMBER"])->format();
			if ($callFields["INCOMING"] == CVoxImplantMain::CALL_OUTGOING)
			{
				if ($callFields['CALL_FAILED_CODE'] == '603-S')
				{
					$result = GetMessage('VI_OUT_CALL_DECLINE_SELF', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 603)
				{
					$result = GetMessage('VI_OUT_CALL_DECLINE', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 486)
				{
					$result = GetMessage('VI_OUT_CALL_BUSY', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 480)
				{
					$result = GetMessage('VI_OUT_CALL_UNAVAILABLE', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 404 || $callFields['CALL_FAILED_CODE'] == 484)
				{
					$result = GetMessage('VI_OUT_CALL_ERROR_NUMBER', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 402)
				{
					$result = GetMessage('VI_OUT_CALL_NO_MONEY', Array('#NUMBER#' => $formattedNumber));
				}
				else
				{
					$result = GetMessage('VI_OUT_CALL_END', Array(
						'#NUMBER#' => $formattedNumber,
						'#INFO#' => '[PCH='.$callFields['ID'].']'.GetMessage('VI_CALL_INFO').'[/PCH]',
					));
				}
			}
			else if ($callFields['INCOMING'] == CVoxImplantMain::CALL_CALLBACK)
			{
				if ($callFields['CALL_FAILED_CODE'] == '603-S')
				{
					$result = GetMessage('VI_CALLBACK_DECLINE_SELF', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 603)
				{
					$result = GetMessage('VI_CALLBACK_DECLINE', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 486)
				{
					$result = GetMessage('VI_CALLBACK_BUSY', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 480)
				{
					$result = GetMessage('VI_CALLBACK_UNAVAILABLE', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 404 || $callFields['CALL_FAILED_CODE'] == 484)
				{
					$result = GetMessage('VVI_CALLBACK_ERROR_NUMBER', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 402)
				{
					$result = GetMessage('VI_CALLBACK_NO_MONEY', Array('#NUMBER#' => $formattedNumber));
				}
				else if ($callFields['CALL_FAILED_CODE'] == 304)
				{
					$subMessage = '[PCH='.$callFields['ID'].']'.GetMessage('VI_CALL_INFO').'[/PCH]';
					$result = GetMessage('VI_CALLBACK_SKIP', Array('#NUMBER#' => $formattedNumber, '#INFO#' => $subMessage));
				}
				else
				{
					$result = GetMessage('VI_CALLBACK_END', Array(
						'#NUMBER#' => $formattedNumber,
						'#INFO#' => '[PCH='.$callFields['ID'].']'.GetMessage('VI_CALL_INFO').'[/PCH]',
					));
				}
			}
			else if($callFields['INCOMING'] == CVoxImplantMain::CALL_INCOMING || $callFields['INCOMING'] == CVoxImplantMain::CALL_INCOMING_REDIRECT)
			{
				if ($callFields['CALL_FAILED_CODE'] == 304)
				{
					if ($hasRecord)
						$subMessage = GetMessage('VI_CALL_VOICEMAIL', Array('#LINK_START#' => '[PCH='.$callFields['ID'].']', '#LINK_END#' => '[/PCH]',));
					else
						$subMessage = '[PCH='.$callFields['ID'].']'.GetMessage('VI_CALL_INFO').'[/PCH]';

					$result = GetMessage('VI_IN_CALL_SKIP', Array(
						'#NUMBER#' => $formattedNumber,
						'#INFO#' => $subMessage,
					));
				}
				else
				{
					$result = GetMessage('VI_IN_CALL_END', Array(
						'#NUMBER#' => $formattedNumber,
						'#INFO#' => '[PCH='.$callFields['ID'].']'.GetMessage('VI_CALL_INFO').'[/PCH]',
					));
				}
			}
		}
		return $result;
	}

	public static function GetAttachForChat($callFields, $hasRecord = false, $prependPlus = true)
	{
		if(!CModule::IncludeModule('im'))
			return null;

		$entityData = \CVoxImplantCrmHelper::getEntityFields($callFields['CRM_ENTITY_TYPE'], $callFields['CRM_ENTITY_ID']);
		if(!$entityData)
			return null;

		$result = new \CIMMessageParamAttach(null, '#dfe2e5');
		$result->AddMessage(static::GetMessageForChat($callFields, $hasRecord, $prependPlus));
		$result->AddLink(array(
			"NAME" => $entityData["DESCRIPTION"].": ".$entityData["NAME"],
			"LINK" => $entityData["SHOW_URL"]
		));
		return $result;
	}

	public static function GetCallTypes()
	{
		return array(
			CVoxImplantMain::CALL_OUTGOING => GetMessage("VI_OUTGOING"),
			CVoxImplantMain::CALL_INCOMING => GetMessage("VI_INCOMING"),
			CVoxImplantMain::CALL_INCOMING_REDIRECT => GetMessage("VI_INCOMING_REDIRECT"),
			CVoxImplantMain::CALL_CALLBACK => GetMessage("VI_CALLBACK"),
			CVoxImplantMain::CALL_INFO => GetMessage("VI_INFOCALL"),
		);
	}


	/**
	 * Returns brief call details for CRM or false if call is not found.
	 * @param string $callId Id of the call.
	 * @return array(STATUS_CODE, STATUS_TEXT, SUCCESSFUL) | false
	 */
	public static function getBriefDetails($callId)
	{
		$call = VI\StatisticTable::getRow(array('filter' => array('=CALL_ID' => $callId)));
		if(!$call)
			return false;

		return array(
			'CALL_ID' => $call['CALL_ID'],
			'CALL_TYPE' => $call['INCOMING'],
			'CALL_TYPE_TEXT' => static::getDirectionText($call['INCOMING'], true),
			'STATUS_CODE '=> $call['CALL_FAILED_CODE'],
			'STATUS_TEXT' => self::getStatusText($call["CALL_FAILED_CODE"]),
			'SUCCESSFUL' => $call['CALL_FAILED_CODE'] == '200',
			'DURATION' => (int)$call['CALL_DURATION'],
			'HAS_TRANSCRIPT' => ($call['TRANSCRIPT_ID'] > 0),
			'TRANSCRIPT_PENDING' => ($call['TRANSCRIPT_PENDING'] == 'Y'),
			'DURATION_TEXT' => static::convertDurationToText($call['CALL_DURATION'], CVoxImplantHistory::DURATION_FORMAT_BRIEF),
			'COMMENT' =>  $call['COMMENT']
		);
	}

	public static function getStatusText($statusCode)
	{
		return in_array($statusCode, array("200","304","603-S","603","403","404","486","484","503","480","402","423")) ? GetMessage("VI_STATUS_".$statusCode) : GetMessage("VI_STATUS_OTHER");
	}

	/**
	 * Returns text description for a call direction.
	 * @param int $direction Code of the direction.
	 * @return mixed|string
	 */
	public static function getDirectionText($direction, $full = false)
	{
		$phrase = '';
		if ($direction == CVoxImplantMain::CALL_OUTGOING)
			$phrase = "VI_OUTGOING";
		else if ($direction == CVoxImplantMain::CALL_INCOMING)
			$phrase = "VI_INCOMING";
		else if ($direction == CVoxImplantMain::CALL_INCOMING_REDIRECT)
			$phrase = "VI_INCOMING_REDIRECT";
		else if($direction == CVoxImplantMain::CALL_CALLBACK)
			$phrase = "VI_CALLBACK";
		else if($direction == CVoxImplantMain::CALL_INFO)
			$phrase = "VI_INFOCALL";

		if($phrase != '' && $full)
			$phrase = $phrase . '_FULL';


		return ($phrase == '') ? '' : GetMessage($phrase);
	}

	public static function saveComment($callId, $comment)
	{
		$call = VI\StatisticTable::getRow(array('filter' => array('=CALL_ID' => $callId)));
		if($call)
		{
			VI\StatisticTable::update($call['ID'], array(
				'COMMENT' => $comment
			));
		}
	}

	public static function WriteToLog($data, $title = '')
	{
		if (!COption::GetOptionInt("voximplant", "debug"))
			return false;

		if (is_array($data))
		{
			unset($data['HASH']);
			unset($data['BX_HASH']);
		}
		else if (is_object($data))
		{
			if ($data->HASH)
			{
				$data->HASH = '';
			}
			if ($data->BX_HASH)
			{
				$data->BX_HASH = '';
			}
		}
		$f=fopen($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/voximplant.log", "a+t");
		$w=fwrite($f, "\n------------------------\n".date("Y.m.d G:i:s")."\n".(strlen($title)>0? $title: 'DEBUG')."\n".print_r($data, 1)."\n------------------------\n");
		fclose($f);

		return true;
	}

	/**
	 * @param int $duration Duration in seconds.
	 * @return string Text form of duration.
	 */
	public static function convertDurationToText($duration, $format = self::DURATION_FORMAT_FULL)
	{
		$duration = (int)$duration;
		$minutes = floor($duration / 60);
		$seconds = $duration % 60;

		if($format == self::DURATION_FORMAT_FULL)
			return ($minutes > 0 ? $minutes." ".GetMessage("VI_MIN") : "") . ($minutes > 0 && $seconds > 0 ? ", " : "") . ($seconds > 0 ? $seconds . " " . GetMessage("VI_SEC") : '');
		else
			return sprintf("%02d:%02d", $minutes, $seconds);
	}

	/**
	 * This function guesses responsible person to assign missed call.
	 * @param array $call Call fields, as selected from the Thurly\Voximplant\CallTable.
	 * @param array $config Line config, as selected from the Thurly\Voximplant\ConfigTable
	 * @return int|false Id of the responsible, or false if responsible is not found.
	 */
	public static function detectResponsible($call, $config, $phoneNumber)
	{
		CVoxImplantHistory::WriteToLog($call, "detectResponsible");
		if(is_array($call) && $call['QUEUE_ID'] > 0)
		{
			$queue = VI\Queue::createWithId($call['QUEUE_ID']);
			if($queue instanceof VI\Queue)
			{
				$queueUser = $queue->getFirstUserId($config['TIMEMAN'] == 'Y');
				if ($queueUser > 0)
				{
					$queue->touchUser($queueUser);
					return $queueUser;
				}
			}
		}

		if(is_array($config) && $config['CRM'] == 'Y' && $config['CRM_FORWARD'] == 'Y')
		{
			if(is_array($call) && $call['CRM_ENTITY_TYPE'] != '' && $call['CRM_ENTITY_ID'] > 0)
			{
				$responsibleId = CVoxImplantCrmHelper::getResponsible($call['CRM_ENTITY_TYPE'], $call['CRM_ENTITY_ID']);
				if($responsibleId > 0)
				{
					return $responsibleId;
				}
			}
			else
			{
				$responsibleInfo = CVoxImplantIncoming::getCrmResponsible($phoneNumber, $config['TIMEMAN'] == 'Y');
				if($responsibleInfo && $responsibleInfo['AVAILABLE'] == 'Y')
				{
					return $responsibleInfo['USER_ID'];
				}
			}
		}

		if(is_array($config) && $config['QUEUE_ID'] > 0)
		{
			$queue = VI\Queue::createWithId($config['QUEUE_ID']);
			if($queue instanceof VI\Queue)
			{
				$queueUser = $queue->getFirstUserId($config['TIMEMAN'] == 'Y');
				if ($queueUser > 0)
				{
					$queue->touchUser($queueUser);
					return $queueUser;
				}
			}
		}

		return false;
	}

	/**
	 * This function returns true if callback should be repeated, according to the line config.
	 * @param array $call Call fields, as selected from the Thurly\Voximplant\CallTable.
	 * @param array $config Line config, as selected from the Thurly\Voximplant\ConfigTable
	 * @return true.
	 */
	public static function shouldRepeatCallback($call, $config)
	{
		if(!is_array($call) || !is_array($config))
			return false;

		if($config['CALLBACK_REDIAL'] != 'Y')
			return false;

		if($config['CALLBACK_REDIAL_ATTEMPTS'] <= 0)
			return false;

		if(!isset($call['CALLBACK_PARAMETERS']['redialAttempt']))
			return false;

		$currentAttempt = $call['CALLBACK_PARAMETERS']['redialAttempt'];

		return ($currentAttempt < $config['CALLBACK_REDIAL_ATTEMPTS']);
	}

	/**
	 * Enqueues callback for repeating, according to the line config.
	 * @param array $call Call fields, as selected from the Thurly\Voximplant\CallTable.
	 * @param array $config Line config, as selected from the Thurly\Voximplant\ConfigTable
	 * @return bool|mixed|object
	 */
	public static function repeatCallback($call, $config)
	{
		$apiClient = new CVoxImplantHttp();

		if($config['CALLBACK_REDIAL_PERIOD'] <= 0)
			return false;

		$callbackParameters = $call['CALLBACK_PARAMETERS'];
		$callbackParameters['redialAttempt']++;

		return $apiClient->enqueueCallback($call['CALLBACK_PARAMETERS'], time() + $config['CALLBACK_REDIAL_PERIOD']);
	}

	/**
	 * @param array $statisticFields Call record, as selected from StatisticTable
	 * @see \Thurly\Voximplant\StatisticTable
	 */
	public static function sendCallEndEvent(array $statisticFields)
	{
		foreach(GetModuleEvents("voximplant", "onCallEnd", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, Array(Array(
				'CALL_ID' => $statisticFields['CALL_ID'],
				'CALL_TYPE' => $statisticFields['INCOMING'],
				'PHONE_NUMBER' => $statisticFields['PHONE_NUMBER'],
				'PORTAL_NUMBER' => $statisticFields['PORTAL_NUMBER'],
				'PORTAL_USER_ID' => $statisticFields['PORTAL_USER_ID'],
				'CALL_DURATION' => $statisticFields['CALL_DURATION'],
				'CALL_START_DATE' => $statisticFields['CALL_START_DATE'],
				'COST' => $statisticFields['COST'],
				'COST_CURRENCY' => $statisticFields['COST_CURRENCY'],
				'CALL_FAILED_CODE' => $statisticFields['CALL_FAILED_CODE'],
				'CALL_FAILED_REASON' => $statisticFields['CALL_FAILED_REASON'],
				'CRM_ACTIVITY_ID' => $statisticFields['CRM_ACTIVITY_ID'],
			)));
		}
	}

	/**
	 * @param string $callId
	 * @return bool
	 */
	public static function getLock($callId)
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$lockName = $sqlHelper->forSql(self::CALL_LOCK_PREFIX . "_" . $callId);
		$lockRow = Application::getConnection()->query("SELECT GET_LOCK('{$lockName}', 0) as L")->fetch();
		return $lockRow["L"] == "1";
	}

	/**
	 * @param string $callId
	 * @return bool
	 */
	public static function releaseLock($callId)
	{
		$sqlHelper = Application::getConnection()->getSqlHelper();
		$lockName = $sqlHelper->forSql(self::CALL_LOCK_PREFIX . "_" . $callId);
		Application::getConnection()->query("SELECT RELEASE_LOCK('{$lockName}')");
		return true;
	}
}