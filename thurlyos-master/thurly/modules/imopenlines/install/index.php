<?
global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen("/index.php"));

IncludeModuleLangFile($PathInstall."/install.php");

if(class_exists("imopenlines")) return;

Class imopenlines extends CModule
{
	var $MODULE_ID = "imopenlines";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	function imopenlines()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = IMOPENLINES_VERSION;
			$this->MODULE_VERSION_DATE = IMOPENLINES_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("IMOPENLINES_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("IMOPENLINES_MODULE_DESCRIPTION");
	}

	public function GetPath($notDocumentRoot=false)
	{
		if($notDocumentRoot)
			return str_replace($_SERVER["DOCUMENT_ROOT"],'',dirname(__DIR__));
		else
			return dirname(__DIR__);
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step < 2)
		{
			$this->CheckModules();
			$APPLICATION->IncludeAdminFile(GetMessage("IMOPENLINES_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/imopenlines/install/step1.php");
		}
		elseif($step == 2)
		{
			if ($this->CheckModules())
			{
				$this->InstallDB(Array(
					'PUBLIC_URL' => $_REQUEST["PUBLIC_URL"]
				));
				$this->InstallFiles();
			}
			$APPLICATION->IncludeAdminFile(GetMessage("IMOPENLINES_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/imopenlines/install/step2.php");
		}
		return true;
	}

	function InstallEvents()
	{
		$orm = \Thurly\Main\Mail\Internal\EventTypeTable::getList(array(
			'select' => array('ID'),
			'filter' => Array(
				'=EVENT_NAME' => Array('IMOL_HISTORY_LOG', 'IMOL_OPERATOR_ANSWER')
			)
		));

		if(!$orm->fetch())
		{
			include($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/imopenlines/install/events/set_events.php");
		}

		return true;
	}

	function CheckModules()
	{
		global $APPLICATION;

		if (!CModule::IncludeModule('pull') || !CPullOptions::GetQueueServerStatus())
		{
			$this->errors[] = GetMessage('IMOPENLINES_CHECK_PULL');
		}

		if (!IsModuleInstalled('imconnector'))
		{
			$this->errors[] = GetMessage('IMOPENLINES_CHECK_CONNECTOR');
		}

		if (!IsModuleInstalled('im'))
		{
			$this->errors[] = GetMessage('IMOPENLINES_CHECK_IM');
		}
		else
		{
			$imVersion = \Thurly\Main\ModuleManager::getVersion('im');
			if (version_compare("16.5.0", $imVersion) == 1)
			{
				$this->errors[] = GetMessage('IMOPENLINES_CHECK_IM_VERSION');
			}
		}

		if(is_array($this->errors) && !empty($this->errors))
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			return true;
		}
	}

	function InstallDB($params = Array())
	{
		global $DB, $APPLICATION;

		$this->errors = false;

		if(strtolower($DB->type) !== 'mysql')
		{
			$this->errors = array(
				GetMessage('IMOPENLINES_DB_NOT_SUPPORTED'),
			);
		}

		if (strlen($params['PUBLIC_URL']) > 0 && strlen($params['PUBLIC_URL']) < 12)
		{
			if (!$this->errors)
			{
				$this->errors = Array();
			}
			$this->errors[] = GetMessage('IMOPENLINES_CHECK_PUBLIC_PATH');
		}

		if(!$this->errors && !$DB->Query("SELECT 'x' FROM b_imopenlines_config", true))
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/thurly/modules/imopenlines/install/db/".strtolower($DB->type)."/install.sql");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		RegisterModule("imopenlines");

		COption::SetOptionString("imopenlines", "portal_url", $params['PUBLIC_URL']);

		RegisterModuleDependences('im', 'OnBeforeChatMessageAdd', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onBeforeMessageSend');
		RegisterModuleDependences('im', 'OnAfterMessagesAdd', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onMessageSend');
		RegisterModuleDependences('im', 'OnAfterMessagesAdd', 'imopenlines', '\Thurly\ImOpenLines\LiveChat', 'onMessageSend');
		RegisterModuleDependences('im', 'OnAfterChatRead', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onChatRead');
		RegisterModuleDependences('im', 'OnStartWriting', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onStartWriting');
		RegisterModuleDependences('im', 'OnLoadLastMessage', 'imopenlines', '\Thurly\ImOpenLines\Session', 'onSessionProlongLastMessage');
		RegisterModuleDependences('im', 'OnStartWriting', 'imopenlines', '\Thurly\ImOpenLines\Session', 'onSessionProlongWriting');
		RegisterModuleDependences('im', 'OnChatRename', 'imopenlines', '\Thurly\ImOpenLines\Session', 'onSessionProlongChatRename');
		RegisterModuleDependences('im', 'OnAfterMessagesUpdate', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onMessageUpdate');
		RegisterModuleDependences('im', 'OnAfterMessagesDelete', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onMessageDelete');
		RegisterModuleDependences('im', 'OnGetNotifySchema', 'imopenlines', '\Thurly\ImOpenLines\Chat', 'onGetNotifySchema');

		$eventManager = \Thurly\Main\EventManager::getInstance();
		$eventManager->registerEventHandler('imconnector', 'OnReceivedPost', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedPost');
		$eventManager->registerEventHandler('imconnector', 'OnReceivedMessageUpdate', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedPostUpdate');
		$eventManager->registerEventHandler('imconnector', 'OnReceivedMessage', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedMessage');
		$eventManager->registerEventHandler('imconnector', 'OnReceivedMessageUpdate', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedMessageUpdate');
		$eventManager->registerEventHandler('imconnector', 'OnReceivedMessageDel', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedMessageDelete');
		$eventManager->registerEventHandler('imconnector', 'OnReceivedStatusDelivery', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedStatusDelivery');
		$eventManager->registerEventHandler('imconnector', 'OnReceivedStatusReading', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedStatusReading');
		$eventManager->registerEventHandler('imconnector', 'OnReceivedStatusWrites', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedStatusWrites');
		$eventManager->registerEventHandler('main', 'OnAfterSetOption_~controller_group_name', 'imopenlines', '\Thurly\ImOpenLines\Limit', 'onThurlyOSLicenseChange');
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'imopenlines', '\Thurly\ImOpenLines\Rest', 'onRestServiceBuildDescription');

		CAgent::AddAgent('\Thurly\ImOpenLines\Session::transferToNextInQueueAgent(0);', "imopenlines", "N", 60);
		CAgent::AddAgent('\Thurly\ImOpenLines\Session::closeByTimeAgent(0);', "imopenlines", "N", 60);
		CAgent::AddAgent('\Thurly\ImOpenLines\Session::mailByTimeAgent(0);', "imopenlines", "N", 60);
		CAgent::AddAgent('\Thurly\ImOpenLines\Common::deleteBrokenSession();', "imopenlines", "N", 86400, "", "Y", \ConvertTimeStamp(time()+\CTimeZone::GetOffset()+86400, "FULL"));

		if (!IsModuleInstalled('thurlyos'))
		{
			CAgent::AddAgent('\Thurly\ImOpenLines\Security\Helper::installRolesAgent();', "imopenlines", "N", 60, "", "Y", \ConvertTimeStamp(time()+\CTimeZone::GetOffset()+60, "FULL"));
		}

		if(strtolower($DB->type) == 'mysql' && $DB->Query("CREATE fulltext index IXF_IMOL_S_INDEX_1 on b_imopenlines_session_index (SEARCH_CONTENT)", true))
		{
			\CModule::IncludeModule("imopenlines");
			\Thurly\Imopenlines\Model\SessionIndexTable::getEntity()->enableFullTextIndex("SEARCH_CONTENT");
		}
		
		$this->InstallChatApps();		

		$this->InstallEvents();

		return true;
	}

	function InstallFiles()
	{
		\CopyDirFiles($this->GetPath()."/install/public", $_SERVER["DOCUMENT_ROOT"]."/", true, true);
		\CopyDirFiles($this->GetPath()."/install/js", $_SERVER["DOCUMENT_ROOT"]."/thurly/js", true, true);
		\CopyDirFiles($this->GetPath()."/install/components/thurly", $_SERVER["DOCUMENT_ROOT"]."/thurly/components/thurly", true, true);
		\CopyDirFiles($this->GetPath()."/install/activities", $_SERVER["DOCUMENT_ROOT"]."/thurly/activities", true, true);
		\CopyDirFiles($this->GetPath()."/install/templates", $_SERVER["DOCUMENT_ROOT"]."/thurly/templates", true, true);

		return true;
	}
	
	function InstallChatApps()
	{
		if (!\CModule::IncludeModule("im"))
		{
			return false;
		}
		
		$result = \Thurly\Im\Model\AppTable::getList(Array(
			'filter' => Array('=MODULE_ID' => 'imopenlines', '=CODE' => 'quick')
		))->fetch();
		
		if (!$result)
		{
			\Thurly\Im\App::register(Array(
				'MODULE_ID' => 'imopenlines',
				'BOT_ID' => 0,
				'CODE' => 'quick',
				'REGISTERED' => 'Y',
				'ICON_ID' => self::uploadIcon('quick'),
				'IFRAME' => '/desktop_app/iframe/imopenlines_quick.php',
				'IFRAME_WIDTH' => '512',
				'IFRAME_HEIGHT' => '234',
				'CONTEXT' => 'lines',
				'CLASS' => '\Thurly\ImOpenLines\Chat',
				'METHOD_LANG_GET' => 'onAppLang',
			));
		}
		
		return true;
	}
	
	function UnInstallChatApps()
	{
		if (!\CModule::IncludeModule("im"))
		{
			return false;
		}
		
		$result = \Thurly\Im\Model\AppTable::getList(Array(
			'filter' => Array('=MODULE_ID' => 'imopenlines', '=CODE' => 'quick')
		))->fetch();
		
		if ($result)
		{
			\Thurly\Im\App::unRegister(Array('ID' => $result['ID'], 'FORCE' => 'Y'));
		}
		
		return true;
	}
	
	private static function uploadIcon($iconName)
	{
		if (strlen($iconName) <= 0)
			return false;
		
		$iconId = false;
		if (\Thurly\Main\IO\File::isFileExists(\Thurly\Main\Application::getDocumentRoot().'/thurly/modules/imopenlines/install/icon/icon_'.$iconName.'.png'))
		{
			$iconId = \Thurly\Main\Application::getDocumentRoot().'/thurly/modules/imopenlines/install/icon/icon_'.$iconName.'.png';
		}

		if ($iconId)
		{
			$iconId = \CFile::SaveFile(\CFile::MakeFileArray($iconId), 'imopenlines');
		}

		return $iconId;
	}

	function UnInstallEvents()
	{
		global $DB;

		include_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/imopenlines/install/events/del_events.php");

		return true;
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("IMOPENLINES_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/thurly/modules/imopenlines/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array("savedata" => $_REQUEST["savedata"]));

			if(!isset($_REQUEST["saveemails"]) || $_REQUEST["saveemails"] != "Y")
				$this->UnInstallEvents();

			$this->UnInstallFiles();

			$APPLICATION->IncludeAdminFile(GetMessage("IMOPENLINES_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/thurly/modules/imopenlines/install/unstep2.php");
		}
	}

	function UnInstallDB($arParams = Array())
	{
		global $APPLICATION, $DB, $errors;

		$this->errors = false;

		if (!$arParams['savedata'])
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/thurly/modules/imopenlines/install/db/".strtolower($DB->type)."/uninstall.sql");

		if(is_array($this->errors))
			$arSQLErrors = $this->errors;

		if(!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}

		UnRegisterModuleDependences('im', 'OnBeforeChatMessageAdd', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onBeforeMessageSend');
		UnRegisterModuleDependences('im', 'OnAfterMessagesAdd', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onMessageSend');
		UnRegisterModuleDependences('im', 'OnAfterMessagesAdd', 'imopenlines', '\Thurly\ImOpenLines\LiveChat', 'onMessageSend');
		UnRegisterModuleDependences('im', 'OnAfterChatRead', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onChatRead');
		UnRegisterModuleDependences('im', 'OnAfterMessagesUpdate', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onMessageUpdate');
		UnRegisterModuleDependences('im', 'OnAfterMessagesDelete', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onMessageDelete');
		UnRegisterModuleDependences('im', 'OnStartWriting', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onStartWriting');
		UnRegisterModuleDependences('im', 'OnLoadLastMessage', 'imopenlines', '\Thurly\ImOpenLines\Session', 'onSessionProlongLastMessage');
		UnRegisterModuleDependences('im', 'OnStartWriting', 'imopenlines', '\Thurly\ImOpenLines\Session', 'onSessionProlongWriting');
		UnRegisterModuleDependences('im', 'OnChatRename', 'imopenlines', '\Thurly\ImOpenLines\Session', 'onSessionProlongChatRename');
		UnRegisterModuleDependences('im', 'OnGetNotifySchema', 'imopenlines', '\Thurly\ImOpenLines\Chat', 'onGetNotifySchema');

		$eventManager = \Thurly\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('imconnector', 'OnReceivedPost', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedPost');
		$eventManager->unRegisterEventHandler('imconnector', 'OnReceivedPostUpdate', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'OnReceivedPostUpdate');
		$eventManager->unRegisterEventHandler('imconnector', 'OnReceivedMessage', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedMessage');
		$eventManager->unRegisterEventHandler('imconnector', 'OnReceivedMessageUpdate', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'OnReceivedMessageUpdate');
		$eventManager->unRegisterEventHandler('imconnector', 'OnReceivedMessageDel', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedMessageDelete');
		$eventManager->unRegisterEventHandler('imconnector', 'OnReceivedStatusDelivery', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedStatusDelivery');
		$eventManager->unRegisterEventHandler('imconnector', 'OnReceivedStatusReading', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedStatusReading');
		$eventManager->unRegisterEventHandler('imconnector', 'OnReceivedStatusWrites', 'imopenlines', '\Thurly\ImOpenLines\Connector', 'onReceivedStatusWrites');
		$eventManager->unRegisterEventHandler('main', 'OnAfterSetOption_~controller_group_name', 'imopenlines', '\Thurly\ImOpenLines\Limit', 'onThurlyOSLicenseChange');
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'imopenlines', '\Thurly\ImOpenLines\Rest', 'onRestServiceBuildDescription');

		$this->UnInstallChatApps();
		
		UnRegisterModule("imopenlines");

		return true;
	}

	function UnInstallFiles($arParams = array())
	{
		return true;
	}
}
?>
