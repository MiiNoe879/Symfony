<?
global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen("/index.php"));

IncludeModuleLangFile($PathInstall."/install.php");

if(class_exists("voximplant")) return;

Class voximplant extends CModule
{
	var $MODULE_ID = "voximplant";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

	function voximplant()
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
			$this->MODULE_VERSION = VI_VERSION;
			$this->MODULE_VERSION_DATE = VI_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("VI_MODULE_NAME_2");
		$this->MODULE_DESCRIPTION = GetMessage("VI_MODULE_DESCRIPTION_2");
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step < 2)
		{
			$this->CheckModules();
			$APPLICATION->IncludeAdminFile(GetMessage("VI_INSTALL_TITLE_2"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/voximplant/install/step1.php");
		}
		elseif($step == 2)
		{
			if ($this->CheckModules())
			{
				$this->InstallDB(Array(
					'PUBLIC_URL' => $_REQUEST["PUBLIC_URL"]
				));
				$this->InstallFiles();

				$GLOBALS["CACHE_MANAGER"]->CleanDir("menu");
				CThurlyComponent::clearComponentCache("thurly:menu");
			}
			$APPLICATION->IncludeAdminFile(GetMessage("VI_INSTALL_TITLE_2"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/voximplant/install/step2.php");
		}
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function CheckModules()
	{
		global $APPLICATION;

		if (!CModule::IncludeModule('pull') || !CPullOptions::GetQueueServerStatus())
		{
			$this->errors[] = GetMessage('VI_CHECK_PULL');
		}

		if (!IsModuleInstalled('im'))
		{
			$this->errors[] = GetMessage('VI_CHECK_IM');
		}

		$mainVersion = \Thurly\Main\ModuleManager::getVersion('main');
		if (version_compare("14.9.2", $mainVersion) == 1)
		{
			$this->errors[] = GetMessage('VI_CHECK_MAIN');
		}

		if (IsModuleInstalled('intranet'))
		{
			$intranetVersion = \Thurly\Main\ModuleManager::getVersion('intranet');
			if (version_compare("14.5.6", $intranetVersion) == 1)
			{
				$this->errors[] = GetMessage('VI_CHECK_INTRANET');
			}
		}
		else
		{
			$this->errors[] = GetMessage('VI_CHECK_INTRANET_INSTALL');
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
		if (strlen($params['PUBLIC_URL']) > 0 && strlen($params['PUBLIC_URL']) < 12)
		{
			if (!$this->errors)
			{
				$this->errors = Array();
			}
			$this->errors[] = GetMessage('VI_CHECK_PUBLIC_PATH');
		}
		if(!$this->errors && !$DB->Query("SELECT 'x' FROM b_voximplant_phone", true))
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/thurly/modules/voximplant/install/db/".strtolower($DB->type)."/install.sql");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		COption::SetOptionString("voximplant", "portal_url", $params['PUBLIC_URL']);

		RegisterModule("voximplant");

		RegisterModuleDependences('main', 'OnBeforeUserAdd', 'voximplant', 'CVoxImplantEvent', 'OnBeforeUserAdd');
		RegisterModuleDependences('main', 'OnAfterUserAdd', 'voximplant', 'CVoxImplantEvent', 'OnBeforeUserUpdate');
		RegisterModuleDependences('main', 'OnBeforeUserUpdate', 'voximplant', 'CVoxImplantEvent', 'OnBeforeUserUpdate');
		RegisterModuleDependences('main', 'OnAfterUserUpdate', 'voximplant', 'CVoxImplantEvent', 'OnAfterUserUpdate');
		RegisterModuleDependences('main', 'OnUserDelete', 'voximplant', 'CVoxImplantEvent', 'OnUserDelete');
		RegisterModuleDependences("perfmon", "OnGetTableSchema", "voximplant", "CVoxImplantTableSchema", "OnGetTableSchema");

		RegisterModuleDependences("crm", "OnAfterExternalCrmLeadAdd", "voximplant", "CVoxImplantCrmHelper", "RegisterEntity");
		RegisterModuleDependences("crm", "OnAfterExternalCrmContactAdd", "voximplant", "CVoxImplantCrmHelper", "RegisterEntity");
		RegisterModuleDependences("crm", "OnCrmCallbackFormSubmitted", "voximplant", "CVoxImplantCrmHelper", "OnCrmCallbackFormSubmitted");

		RegisterModuleDependences("pull", "OnGetDependentModule", "voximplant", "CVoxImplantEvent", "PullOnGetDependentModule");
		RegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'voximplant', 'CVoxImplantRestService', 'OnRestServiceBuildDescription');
		RegisterModuleDependences('rest', 'OnRestAppInstall', 'voximplant', '\Thurly\Voximplant\Rest\Helper', 'onRestAppInstall');
		RegisterModuleDependences('rest', 'OnRestAppDelete', 'voximplant', '\Thurly\Voximplant\Rest\Helper', 'onRestAppDelete');
		RegisterModuleDependences("im", "OnGetNotifySchema", "voximplant", "CVoxImplantEvent", "onGetNotifySchema");

		if (!IsModuleInstalled('thurlyos'))
		{
			CAgent::AddAgent("CVoxImplantPhone::SynchronizeUserPhones();", "voximplant", "N", 300);
		}

		$this->InstallDefaultData();
		$this->InstallUserFields();

		CModule::IncludeModule("voximplant");

		if(CVoxImplantMain::isDbMySql() && $DB->Query("CREATE fulltext index IXF_VI_TRANSCRIPT_LINE_1 on b_voximplant_transcript_line (MESSAGE)", true))
		{
			\Thurly\Voximplant\Model\TranscriptLineTable::getEntity()->enableFullTextIndex("MESSAGE");
		}

		if(CVoxImplantMain::isDbMySql() && $DB->Query("CREATE fulltext index IXF_VI_STATS_1 on b_voximplant_statistic_index (CONTENT)", true))
		{
			\Thurly\Voximplant\Model\StatisticIndexTable::getEntity()->enableFullTextIndex("CONTENT");
		}

		if(CVoxImplantMain::isDbMySql() && $DB->Query("CREATE fulltext index IXF_VI_ST_1 on b_voximplant_statistic (COMMENT)", true))
		{
			\Thurly\Voximplant\StatisticTable::getEntity()->enableFullTextIndex("COMMENT");
		}

		return true;
	}

	function InstallFiles()
	{
		if($_ENV['COMPUTERNAME']!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/voximplant/install/js", $_SERVER["DOCUMENT_ROOT"]."/thurly/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/voximplant/install/components", $_SERVER["DOCUMENT_ROOT"]."/thurly/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/voximplant/install/activities", $_SERVER["DOCUMENT_ROOT"]."/thurly/activities", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/voximplant/install/tools", $_SERVER["DOCUMENT_ROOT"]."/thurly/tools", true, true);
		}
		return true;
	}

	function InstallDefaultData()
	{
		if(!CModule::IncludeModule('voximplant'))
			return;

		$this->CreateDefaultGroup();
		$this->CreateDefaultLineConfig();
		$this->CreateDefaultPermissions();
		$this->CreateDefaultLineAccess();
		$this->CreateDefaultIvr();
	}

	/**
	 * Creates default roles (for b24 roles will be created with wizard)
	 */
	function CreateDefaultPermissions()
	{
		$checkCursor = \Thurly\Voximplant\Model\RoleTable::getList(array('limit' => 1));
		if(!$checkCursor->fetch() && !\Thurly\Main\ModuleManager::isModuleInstalled('thurlyos'))
		{
			$defaultRoles = array(
				'admin' => array(
					'NAME' => GetMessage('VOXIMPLANT_ROLE_ADMIN'),
					'PERMISSIONS' => array(
						'CALL_DETAIL' => array(
							'VIEW' => 'X',
						),
						'CALL' => array(
							'PERFORM' => 'X'
						),
						'CALL_RECORD' => array(
							'LISTEN' => 'X'
						),
						'USER' => array(
							'MODIFY' => 'X'
						),
						'SETTINGS' => array(
							'MODIFY' => 'X'
						),
						'LINE' => array(
							'MODIFY' => 'X'
						)
					)
				),
				'chief' => array(
					'NAME' => GetMessage('VOXIMPLANT_ROLE_CHIEF'),
					'PERMISSIONS' => array(
						'CALL_DETAIL' => array(
							'VIEW' => 'X',
						),
						'CALL' => array(
							'PERFORM' => 'X'
						),
						'CALL_RECORD' => array(
							'LISTEN' => 'X'
						),
					)
				),
				'department_head' => array(
					'NAME' => GetMessage('VOXIMPLANT_ROLE_DEPARTMENT_HEAD'),
					'PERMISSIONS' => array(
						'CALL_DETAIL' => array(
							'VIEW' => 'D',
						),
						'CALL' => array(
							'PERFORM' => 'X'
						),
						'CALL_RECORD' => array(
							'LISTEN' => 'D'
						),
					)
				),
				'manager' => array(
					'NAME' => GetMessage('VOXIMPLANT_ROLE_MANAGER'),
					'PERMISSIONS' => array(
						'CALL_DETAIL' => array(
							'VIEW' => 'A',
						),
						'CALL' => array(
							'PERFORM' => 'X'
						),
						'CALL_RECORD' => array(
							'LISTEN' => 'A'
						),
					)
				)
			);

			$roleIds = array();
			foreach ($defaultRoles as $roleCode => $role)
			{
				$addResult = \Thurly\Voximplant\Model\RoleTable::add(array(
					'NAME' => $role['NAME'],
				));

				$roleId = $addResult->getId();
				if ($roleId)
				{
					$roleIds[$roleCode] = $roleId;
					\Thurly\Voximplant\Security\RoleManager::setRolePermissions($roleId, $role['PERMISSIONS']);
				}
			}

			if (isset($roleIds['admin']))
			{
				\Thurly\Voximplant\Model\RoleAccessTable::add(array(
					'ROLE_ID' => $roleIds['admin'],
					'ACCESS_CODE' => 'G1'
				));
			}

			if (isset($roleIds['manager']) && \Thurly\Main\Loader::includeModule('intranet'))
			{
				$departmentTree = CIntranetUtils::GetDeparmentsTree();
				$rootDepartment = (int)$departmentTree[0][0];

				if ($rootDepartment > 0)
				{
					\Thurly\Voximplant\Model\RoleAccessTable::add(array(
						'ROLE_ID' => $roleIds['manager'],
						'ACCESS_CODE' => 'DR'.$rootDepartment
					));
				}
			}
		}
	}

	/**
	 * Creates config for "default" number
	 */
	function CreateDefaultLineConfig()
	{
		$checkCursor = \Thurly\Voximplant\ConfigTable::getList(array(
			'filter' => array('=PORTAL_MODE' => \CVoxImplantConfig::MODE_LINK),
			'limit' => 1
		));
		if(!$checkCursor->fetch())
		{
			$insertResult = \Thurly\Voximplant\ConfigTable::add(array(
				'PORTAL_MODE' => \CVoxImplantConfig::MODE_LINK,
				'SEARCH_ID' => \CVoxImplantConfig::LINK_BASE_NUMBER,
				'PHONE_VERIFIED' => 'N',
				'RECORDING' => 'N',
				'CRM' => 'Y',
				'QUEUE_ID' => CVoxImplantMain::getDefaultGroupId()
			));
		}
	}

	/**
	 * Creates access settings for "default" number
	 */
	function CreateDefaultLineAccess()
	{
		$checkCursor = \Thurly\Voximplant\Model\LineAccessTable::getList(array(
			'filter' => array('=LINE_SEARCH_ID' => \CVoxImplantConfig::LINK_BASE_NUMBER),
			'limit' => 1
		));

		if(!$checkCursor->fetch())
			return;

		\Thurly\Voximplant\Model\LineAccessTable::add(array(
			'LINE_SEARCH_ID' => CVoxImplantConfig::LINK_BASE_NUMBER,
			'ACCESS_CODE' => 'G2'
		));
	}

	/**
	 * Creates default group of users and populates it with members of the administrators group.
	 */
	function CreateDefaultGroup()
	{
		$checkCursor = \Thurly\Voximplant\Model\QueueTable::getList(array(
			'limit' => 1
		));
		if($checkCursor->fetch())
			return;

		$admins = array();
		$adminCursor = \Thurly\Main\Application::getConnection()->query('SELECT u.ID as ID FROM b_user u INNER JOIN b_user_group g ON u.ID = g.USER_ID WHERE g.GROUP_ID = 1');
		while ($row = $adminCursor->fetch())
		{
			$admins[] = $row['ID'];
		}

		$insertResult = \Thurly\Voximplant\Model\QueueTable::add(array(
			'NAME' => GetMessage('VOXIMPLANT_DEFAULT_GROUP'),
			'WAIT_TIME' => 5
		));
		$defaultGroupId = $insertResult->getId();
		COption::SetOptionString("voximplant", "default_group_id", $defaultGroupId);
		foreach ($admins as $adminId)
		{
			\Thurly\Voximplant\Model\QueueUserTable::add(array(
				'QUEUE_ID' => $defaultGroupId,
				'USER_ID' => $adminId
			));
		}
	}

	/**
	 * Creates default IVR menu
	 */
	function CreateDefaultIvr()
	{
		$checkCursor = \Thurly\Voximplant\Model\IvrTable::getList(array(
			'limit' => 1
		));

		if($checkCursor->fetch())
			return;

		$insertResult = \Thurly\Voximplant\Model\IvrTable::add(array(
			'NAME' => GetMessage('VOXIMPLANT_DEFAULT_IVR')
		));
		$ivrId = $insertResult->getId();

		$insertResult = \Thurly\Voximplant\Model\IvrItemTable::add(array(
			'IVR_ID' => $ivrId,
			'TYPE' => \Thurly\Voximplant\Ivr\Item::TYPE_MESSAGE,
			'MESSAGE' => GetMessage('VOXIMPLANT_DEFAULT_ITEM'),
			'TTS_VOICE' => \Thurly\Voximplant\Tts\Language::getDefaultVoice(\Thurly\Main\Application::getInstance()->getContext()->getLanguage()),
			'TIMEOUT' => 2,
			'TIMEOUT_ACTION' => \Thurly\Voximplant\Ivr\Action::ACTION_EXIT
		));
		$itemId = $insertResult->getId();

		\Thurly\Voximplant\Model\IvrTable::update($ivrId, array(
			'FIRST_ITEM_ID' => $itemId
		));

		\Thurly\Voximplant\Model\IvrActionTable::add(array(
			'ITEM_ID' => $itemId,
			'DIGIT' => '0',
			'ACTION' => \Thurly\Voximplant\Ivr\Action::ACTION_EXIT
		));
	}

	function InstallUserFields()
	{
		$arFields = array();
		$arFields['ENTITY_ID'] = 'USER';
		$arFields['FIELD_NAME'] = 'UF_VI_PASSWORD';

		$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => $arFields['ENTITY_ID'], 'FIELD_NAME' => $arFields['FIELD_NAME']));
		if (!$res->Fetch())
		{
			$rs = CUserTypeEntity::GetList(array(), array(
				"ENTITY_ID" => $arFields["ENTITY_ID"],
				"FIELD_NAME" => $arFields["FIELD_NAME"],
			));
			if(!$rs->Fetch())
			{
				$arMess['VI_UF_NAME_PASSWORD'] = 'VoxImplant: user password';

				$arFields['USER_TYPE_ID'] = 'string';
				$arFields['EDIT_IN_LIST'] = 'N';
				$arFields['SHOW_IN_LIST'] = 'N';
				$arFields['MULTIPLE'] = 'N';

				$arFields['EDIT_FORM_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				$arFields['LIST_COLUMN_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				$arFields['LIST_FILTER_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				if (LANGUAGE_ID != 'en')
				{
					$arFields['EDIT_FORM_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
					$arFields['LIST_COLUMN_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
					$arFields['LIST_FILTER_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
				}

				$CUserTypeEntity = new CUserTypeEntity();
				$CUserTypeEntity->Add($arFields);
			}
		}

		$arFields = array();
		$arFields['ENTITY_ID'] = 'USER';
		$arFields['FIELD_NAME'] = 'UF_VI_BACKPHONE';

		$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => $arFields['ENTITY_ID'], 'FIELD_NAME' => $arFields['FIELD_NAME']));
		if (!$res->Fetch())
		{
			$rs = CUserTypeEntity::GetList(array(), array(
				"ENTITY_ID" => $arFields["ENTITY_ID"],
				"FIELD_NAME" => $arFields["FIELD_NAME"],
			));
			if(!$rs->Fetch())
			{
				$arMess['VI_UF_NAME_BACKPHONE'] = 'VoxImplant: user backphone';

				$arFields['USER_TYPE_ID'] = 'string';
				$arFields['EDIT_IN_LIST'] = 'N';
				$arFields['SHOW_IN_LIST'] = 'N';
				$arFields['MULTIPLE'] = 'N';

				$arFields['EDIT_FORM_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_BACKPHONE'];
				$arFields['LIST_COLUMN_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_BACKPHONE'];
				$arFields['LIST_FILTER_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_BACKPHONE'];
				if (LANGUAGE_ID != 'en')
				{
					$arFields['EDIT_FORM_LABEL']['en'] = $arMess['VI_UF_NAME_BACKPHONE'];
					$arFields['LIST_COLUMN_LABEL']['en'] = $arMess['VI_UF_NAME_BACKPHONE'];
					$arFields['LIST_FILTER_LABEL']['en'] = $arMess['VI_UF_NAME_BACKPHONE'];
				}
				$CUserTypeEntity = new CUserTypeEntity();
				$CUserTypeEntity->Add($arFields);
			}
		}

		$arFields = array();
		$arFields['ENTITY_ID'] = 'USER';
		$arFields['FIELD_NAME'] = 'UF_VI_PHONE';

		$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => $arFields['ENTITY_ID'], 'FIELD_NAME' => $arFields['FIELD_NAME']));
		if (!$res->Fetch())
		{
			$rs = CUserTypeEntity::GetList(array(), array(
				"ENTITY_ID" => $arFields["ENTITY_ID"],
				"FIELD_NAME" => $arFields["FIELD_NAME"],
			));
			if(!$rs->Fetch())
			{
				$arMess['VI_UF_NAME_PASSWORD'] = 'VoxImplant: phone';

				$arFields['USER_TYPE_ID'] = 'string';
				$arFields['EDIT_IN_LIST'] = 'N';
				$arFields['SHOW_IN_LIST'] = 'N';
				$arFields['MULTIPLE'] = 'N';

				$arFields['EDIT_FORM_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				$arFields['LIST_COLUMN_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				$arFields['LIST_FILTER_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				if (LANGUAGE_ID != 'en')
				{
					$arFields['EDIT_FORM_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
					$arFields['LIST_COLUMN_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
					$arFields['LIST_FILTER_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
				}

				$CUserTypeEntity = new CUserTypeEntity();
				$CUserTypeEntity->Add($arFields);
			}
		}

		$arFields = array();
		$arFields['ENTITY_ID'] = 'USER';
		$arFields['FIELD_NAME'] = 'UF_VI_PHONE_PASSWORD';

		$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => $arFields['ENTITY_ID'], 'FIELD_NAME' => $arFields['FIELD_NAME']));
		if (!$res->Fetch())
		{
			$rs = CUserTypeEntity::GetList(array(), array(
				"ENTITY_ID" => $arFields["ENTITY_ID"],
				"FIELD_NAME" => $arFields["FIELD_NAME"],
			));
			if(!$rs->Fetch())
			{
				$arMess['VI_UF_NAME_PASSWORD'] = 'VoxImplant: phone password';

				$arFields['USER_TYPE_ID'] = 'string';
				$arFields['EDIT_IN_LIST'] = 'N';
				$arFields['SHOW_IN_LIST'] = 'N';
				$arFields['MULTIPLE'] = 'N';

				$arFields['EDIT_FORM_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				$arFields['LIST_COLUMN_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				$arFields['LIST_FILTER_LABEL'][LANGUAGE_ID] = $arMess['VI_UF_NAME_PASSWORD'];
				if (LANGUAGE_ID != 'en')
				{
					$arFields['EDIT_FORM_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
					$arFields['LIST_COLUMN_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
					$arFields['LIST_FILTER_LABEL']['en'] = $arMess['VI_UF_NAME_PASSWORD'];
				}

				$CUserTypeEntity = new CUserTypeEntity();
				$CUserTypeEntity->Add($arFields);
			}
		}
	}

	function UnInstallEvents()
	{
		return true;
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("VI_UNINSTALL_TITLE_2"), $DOCUMENT_ROOT."/thurly/modules/voximplant/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array("savedata" => $_REQUEST["savedata"]));
			$this->UnInstallFiles();

			$GLOBALS["CACHE_MANAGER"]->CleanDir("menu");
			CThurlyComponent::clearComponentCache("thurly:menu");

			$APPLICATION->IncludeAdminFile(GetMessage("VI_UNINSTALL_TITLE_2"), $DOCUMENT_ROOT."/thurly/modules/voximplant/install/unstep2.php");
		}
	}

	function UnInstallDB($arParams = Array())
	{
		global $APPLICATION, $DB, $errors;

		$this->errors = false;

		if (!$arParams['savedata'])
			$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/thurly/modules/voximplant/install/db/".strtolower($DB->type)."/uninstall.sql");

		if(is_array($this->errors))
			$arSQLErrors = $this->errors;

		if(!empty($arSQLErrors))
		{
			$this->errors = $arSQLErrors;
			$APPLICATION->ThrowException(implode("", $arSQLErrors));
			return false;
		}

		UnRegisterModuleDependences('main', 'OnBeforeUserAdd', 'voximplant', 'CVoxImplantEvent', 'OnBeforeUserAdd');
		UnRegisterModuleDependences('main', 'OnAfterUserAdd', 'voximplant', 'CVoxImplantEvent', 'OnBeforeUserUpdate');
		UnRegisterModuleDependences('main', 'OnBeforeUserUpdate', 'voximplant', 'CVoxImplantEvent', 'OnBeforeUserUpdate');
		UnRegisterModuleDependences('main', 'OnAfterUserUpdate', 'voximplant', 'CVoxImplantEvent', 'OnAfterUserUpdate');
		UnRegisterModuleDependences('main', 'OnUserDelete', 'voximplant', 'CVoxImplantEvent', 'OnUserDelete');

		UnRegisterModuleDependences("crm", "OnAfterExternalCrmLeadAdd", "voximplant", "CVoxImplantCrmHelper", "RegisterEntity");
		UnRegisterModuleDependences("crm", "OnAfterExternalCrmContactAdd", "voximplant", "CVoxImplantCrmHelper", "RegisterEntity");

		UnRegisterModuleDependences("pull", "OnGetDependentModule", "voximplant", "CVoxImplantEvent", "PullOnGetDependentModule");
		UnRegisterModuleDependences('rest', 'OnRestServiceBuildDescription', 'voximplant', 'CVoxImplantRestService', 'OnRestServiceBuildDescription');
		UnRegisterModuleDependences('rest', 'OnRestAppDelete', 'voximplant', '\Thurly\Voximplant\Rest\Helper', 'onRestAppDelete');
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "voximplant", "CVoxImplantEvent", "onGetNotifySchema");

		CAgent::RemoveAgent("CVoxImplantPhone::SynchronizeUserPhones();", "voximplant");

		$this->UnInstallUserFields($arParams);

		UnRegisterModule("voximplant");

		return true;
	}

	function UnInstallFiles($arParams = array())
	{
		return true;
	}

	function UnInstallUserFields($arParams = Array())
	{
		if (!$arParams['savedata'])
		{
			$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_VI_BACKPHONE'));
			$arFieldData = $res->Fetch();
			if (isset($arFieldData['ID']))
			{
				$CUserTypeEntity = new CUserTypeEntity();
				$CUserTypeEntity->Delete($arFieldData['ID']);
			}

			$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_VI_PASSWORD'));
			$arFieldData = $res->Fetch();
			if (isset($arFieldData['ID']))
			{
				$CUserTypeEntity = new CUserTypeEntity();
				$CUserTypeEntity->Delete($arFieldData['ID']);
			}

			$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_VI_PHONE'));
			$arFieldData = $res->Fetch();
			if (isset($arFieldData['ID']))
			{
				$CUserTypeEntity = new CUserTypeEntity();
				$CUserTypeEntity->Delete($arFieldData['ID']);
			}

			$res = CUserTypeEntity::GetList(Array(), Array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_VI_PHONE_PASSWORD'));
			$arFieldData = $res->Fetch();
			if (isset($arFieldData['ID']))
			{
				$CUserTypeEntity = new CUserTypeEntity();
				$CUserTypeEntity->Delete($arFieldData['ID']);
			}
		}

		return true;
	}
}
?>