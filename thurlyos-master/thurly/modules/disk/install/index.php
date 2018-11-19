<?php
global $MESS;
use Thurly\Main\Config\Option;
use Thurly\Main\Localization\Loc;

$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall)-strlen("/index.php"));

\Thurly\Main\Localization\Loc::loadMessages(__FILE__);

if(class_exists("disk")) return;

Class disk extends CModule
{
	var $MODULE_ID = "disk";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function disk()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("DISK_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("DISK_INSTALL_DESCRIPTION");
	}

	function GetModuleTasks()
	{
		return array(
			'disk_access_read' => array(
				"LETTER" => "R",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'disk_read',
				),
			),
			'disk_access_add' => array(
				"LETTER" => "T",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'disk_read', 'disk_add',
				),
			),
			'disk_access_edit' => array(
				"LETTER" => "W",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'disk_read', 'disk_add', 'disk_edit', 'disk_delete', 'disk_start_bp',
				),
			),
			'disk_access_sharing' => array(
				"LETTER" => "S",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'disk_sharing',
				),
			),
			'disk_access_full' => array(
				"LETTER" => "X",
				"BINDING" => "module",
				"OPERATIONS" => array(
					'disk_read', 'disk_add', 'disk_edit', 'disk_settings', 'disk_delete', 'disk_destroy', 'disk_restore', 'disk_rights', 'disk_sharing', 'disk_start_bp', 'disk_create_wf',
				),
			),
		);
	}

	function InstallDB($install_wizard = true)
	{
		global $DB, $DBType, $APPLICATION;

		$errors = null;
		if (!$DB->Query("SELECT 'x' FROM b_disk_storage", true))
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/db/".$DBType."/install.sql");
		$this->InstallTasks();

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		$isWebdavInstalled = isModuleInstalled('webdav');
		$this->RegisterModuleDependencies(!$isWebdavInstalled);

		RegisterModule("disk");

		$this->InstallUserFields();
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		CAgent::addAgent('Thurly\\Disk\\ExternalLink::removeExpiredWithTypeAuto();', 'disk', 'N');
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		CAgent::addAgent('Thurly\\Disk\\ThurlyOSDisk\\UploadFileManager::removeIrrelevant();', 'disk', 'N');
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		CAgent::addAgent('Thurly\\Disk\\Internals\\Cleaner::deleteShowSession(3, 2);', 'disk', 'N', 3600);
		CAgent::addAgent('Thurly\\Disk\\Internals\\Cleaner::deleteRightSetupSession();', 'disk', 'N');
		CAgent::addAgent('Thurly\\Disk\\Internals\\Rights\\Healer::restartSetupSession();', 'disk', 'N', 3600);
		CAgent::addAgent('Thurly\\Disk\\Internals\\Rights\\Healer::markBadSetupSession();', 'disk', 'N');
		if(!$isWebdavInstalled)
		{
			require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/lib/configuration.php");
			\Thurly\Main\Config\Option::set(
				'disk',
				'successfully_converted',
				'Y'
			);
			\Thurly\Main\Config\Option::set(
				'disk',
				'disk_revision_api',
				\Thurly\Disk\Configuration::REVISION_API
			);
		}
		else
		{
			\CAdminNotify::add(array(
				"MESSAGE" => Loc::getMessage("DISK_NOTIFY_MIGRATE_WEBDAV", array(
					"#LINK#" => "/thurly/admin/disk_from_webdav_convertor.php?lang=".\Thurly\Main\Application::getInstance()->getContext()->getLanguage(),
				)),
				"TAG" => "disk_migrate_from_webdav",
				"MODULE_ID" => "disk",
				"ENABLE_CLOSE" => "N",
			));
		}

		if(\Thurly\Main\Loader::includeModule('disk') && \Thurly\Disk\ZipNginx\Configuration::isModInstalled())
		{
			\Thurly\Disk\ZipNginx\Configuration::enable();
		}

		return true;
	}

	public static function RegisterModuleDependencies($isAlreadyConverted = true)
	{
		if($isAlreadyConverted)
		{
			RegisterModuleDependences("main", "OnAfterUserAdd", "disk", "\\Thurly\\Disk\\SocialnetworkHandlers", "onAfterUserAdd");
			RegisterModuleDependences("main", "onUserDelete", "disk", "\\Thurly\\Disk\\SocialnetworkHandlers", "onUserDelete");
			RegisterModuleDependences("main", "OnAfterUserUpdate", "disk", "\\Thurly\\Disk\\SocialnetworkHandlers", "onAfterUserUpdate");
		}

		RegisterModuleDependences('main', 'OnUserTypeBuildList', 'disk', 'Thurly\\Disk\\Uf\\FileUserType', 'GetUserTypeDescription');
		RegisterModuleDependences('main', 'OnUserTypeBuildList', 'disk', 'Thurly\\Disk\\Uf\\VersionUserType', 'GetUserTypeDescription');

		if($isAlreadyConverted)
		{
			RegisterModuleDependences("search", "OnReindex", "disk", "\\Thurly\\Disk\\Search\\IndexManager", "onSearchReindex");
			RegisterModuleDependences("search", "OnSearchGetURL", "disk", "\\Thurly\\Disk\\Search\\IndexManager", "onSearchGetUrl");

			RegisterModuleDependences('socialnetwork', 'OnSocNetFeaturesAdd', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetFeaturesAdd');
			RegisterModuleDependences('socialnetwork', 'OnSocNetFeaturesUpdate', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetFeaturesUpdate');
			RegisterModuleDependences('socialnetwork', 'OnSocNetUserToGroupAdd', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetUserToGroupAdd');
			RegisterModuleDependences('socialnetwork', 'OnSocNetUserToGroupUpdate', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetUserToGroupUpdate');
			RegisterModuleDependences('socialnetwork', 'OnSocNetUserToGroupDelete', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetUserToGroupDelete');
			RegisterModuleDependences('socialnetwork', 'OnSocNetGroupDelete', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetGroupDelete');
			RegisterModuleDependences('socialnetwork', 'OnSocNetGroupAdd', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetGroupAdd');
			RegisterModuleDependences("socialnetwork", "OnSocNetGroupUpdate", "disk", "\\Thurly\\Disk\\SocialnetworkHandlers", "onSocNetGroupUpdate");
			RegisterModuleDependences('socialnetwork', 'OnAfterFetchDiskUfEntity', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onAfterFetchDiskUfEntity');
			RegisterModuleDependences("im", "OnBeforeConfirmNotify", "disk", "\\Thurly\\Disk\\Sharing", "OnBeforeConfirmNotify");
			RegisterModuleDependences("im", "OnGetNotifySchema", "disk", "\\Thurly\\Disk\\Integration\\NotifySchema", "onGetNotifySchema");

			RegisterModuleDependences("rest", "OnRestServiceBuildDescription", "disk", "\\Thurly\\Disk\\Rest\\RestManager", "onRestServiceBuildDescription");
			RegisterModuleDependences("rest", "onRestGetModule", "disk", "\\Thurly\\Disk\\Rest\\RestManager", "onRestGetModule");
			RegisterModuleDependences("rest", "OnRestAppDelete", "disk", "\\Thurly\\Disk\\Rest\\RestManager", "onRestAppDelete");
		}

		RegisterModuleDependences("iblock", "OnBeforeIBlockDelete", "disk", "disk", "OnBeforeIBlockDelete");
		RegisterModuleDependences("perfmon", "OnGetTableSchema", "disk", "disk", "OnGetTableSchema");
		RegisterModuleDependences("main", "OnAfterRegisterModule", "main", "disk", "installUserFields", 100, "/modules/disk/install/index.php"); // check UF

		RegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", "disk", "\\Thurly\\Disk\\Integration\\FileDiskProperty", "GetUserTypeDescription");
	}

	function UnInstallDB($arParams = Array())
	{
		global $DB, $DBType, $APPLICATION;

		if(CModule::IncludeModule("search"))
		{
			/** @noinspection PhpDynamicAsStaticMethodCallInspection */
			CSearch::deleteIndex("disk");
		}


		$errors = null;
		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			$this->UnInstallUserFields();
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/db/".$DBType."/uninstall.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}
		/** @noinspection PhpDynamicAsStaticMethodCallInspection */
		CAgent::removeModuleAgents("disk");
		COption::removeOption('disk');


		//UnRegisterModuleDependences
		UnRegisterModuleDependences("main", "OnAfterRegisterModule", "main", "disk", "installUserFields", "/modules/disk/install/index.php"); // check UF

		UnRegisterModuleDependences("main", "OnAfterUserAdd", "disk", "\\Thurly\\Disk\\SocialnetworkHandlers", "onAfterUserAdd");
		UnRegisterModuleDependences("main", "OnAfterUserAdd", "disk", "\\Thurly\\Disk\\SocialnetworkHandlers", "onUserDelete");
		UnRegisterModuleDependences("main", "OnAfterUserUpdate", "disk", "\\Thurly\\Disk\\SocialnetworkHandlers", "onAfterUserUpdate");

		UnRegisterModuleDependences('main', 'OnUserTypeBuildList', 'disk', 'Thurly\\Disk\\Uf\\FileUserType', 'GetUserTypeDescription');
		UnRegisterModuleDependences('main', 'OnUserTypeBuildList', 'disk', 'Thurly\\Disk\\Uf\\VersionUserType', 'GetUserTypeDescription');

		UnRegisterModuleDependences("search", "OnReindex", "disk", "\\Thurly\\Disk\\Search\\IndexManager", "onSearchReindex");
		UnRegisterModuleDependences("search", "OnSearchGetURL", "disk", "\\Thurly\\Disk\\Search\\IndexManager", "onSearchGetUrl");

		UnRegisterModuleDependences('socialnetwork', 'OnSocNetFeaturesAdd', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetFeaturesAdd');
		UnRegisterModuleDependences('socialnetwork', 'OnSocNetFeaturesUpdate', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetFeaturesUpdate');
		UnRegisterModuleDependences('socialnetwork', 'OnSocNetUserToGroupAdd', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetUserToGroupAdd');
		UnRegisterModuleDependences('socialnetwork', 'OnSocNetUserToGroupUpdate', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetUserToGroupUpdate');
		UnRegisterModuleDependences('socialnetwork', 'OnSocNetUserToGroupDelete', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetUserToGroupDelete');
		UnRegisterModuleDependences('socialnetwork', 'OnSocNetGroupDelete', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetGroupDelete');
		UnRegisterModuleDependences('socialnetwork', 'OnSocNetGroupAdd', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onSocNetGroupAdd');
		UnRegisterModuleDependences("socialnetwork", "OnSocNetGroupUpdate", "disk", "\\Thurly\\Disk\\SocialnetworkHandlers", "onSocNetGroupUpdate");
		UnRegisterModuleDependences('socialnetwork', 'OnAfterFetchDiskUfEntity', 'disk', "\\Thurly\\Disk\\SocialnetworkHandlers", 'onAfterFetchDiskUfEntity');

		UnRegisterModuleDependences("iblock", "OnBeforeIBlockDelete", "disk", "disk", "OnBeforeIBlockDelete");
		UnRegisterModuleDependences("perfmon", "OnGetTableSchema", "disk", "disk", "OnGetTableSchema");

		UnRegisterModuleDependences("im", "OnBeforeConfirmNotify", "disk", "\\Thurly\\Disk\\Sharing", "OnBeforeConfirmNotify");
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "disk", "\\Thurly\\Disk\\Integration\\NotifySchema", "onGetNotifySchema");

		UnRegisterModuleDependences("rest", "OnRestServiceBuildDescription", "disk", "\\Thurly\\Disk\\Rest\\RestManager", "onRestServiceBuildDescription");
		UnRegisterModuleDependences("rest", "onRestGetModule", "disk", "\\Thurly\\Disk\\Rest\\RestManager", "onRestGetModule");
		UnRegisterModuleDependences("rest", "OnRestAppDelete", "disk", "\\Thurly\\Disk\\Rest\\RestManager", "onRestAppDelete");

		UnRegisterModuleDependences("iblock", "OnIBlockPropertyBuildList", "disk", "\\Thurly\\Disk\\Integration\\FileDiskProperty", "GetUserTypeDescription");

		UnRegisterModule("disk");

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		global $APPLICATION;
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/admin", $_SERVER["DOCUMENT_ROOT"]."/thurly/admin", true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/components", $_SERVER["DOCUMENT_ROOT"]."/thurly/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/js", $_SERVER["DOCUMENT_ROOT"]."/thurly/js", true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/disk/install/tools/', $_SERVER['DOCUMENT_ROOT'].'/thurly/tools', true, true);
			CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/disk/install/services/', $_SERVER['DOCUMENT_ROOT'].'/thurly/services', true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/images", $_SERVER["DOCUMENT_ROOT"]."/thurly/images", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/public/docs", $_SERVER["DOCUMENT_ROOT"]."/docs", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/public/templates", $_SERVER["DOCUMENT_ROOT"]."/thurly/templates", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/webdav", $_SERVER["DOCUMENT_ROOT"]."/thurly/webdav", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/activities", $_SERVER["DOCUMENT_ROOT"]."/thurly/activities", true, true);

			CUrlRewriter::add(
					array(
						"CONDITION" => "#^/docs/pub/(?<hash>[0-9a-f]{32})/(?<action>[0-9a-zA-Z]+)/\?#",
						"RULE" => "hash=$1&action=$2&",
						"ID" => "thurly:disk.external.link",
						"PATH" => "/docs/pub/index.php"
					)
			);

			CUrlRewriter::add(
				array(
					"CONDITION" => "#^/disk/(?<action>[0-9a-zA-Z]+)/(?<fileId>[0-9]+)/\?#",
					"RULE" => "action=$1&fileId=$2&",
					"ID" => "thurly:disk.services",
					"PATH" => "/thurly/services/disk/index.php",
				)
			);

			$APPLICATION->SetFileAccessPermission('/thurly/tools/disk/', array('*' => 'R'));
			$APPLICATION->SetFileAccessPermission('/thurly/services/disk/', array('*' => 'R'));
			$APPLICATION->SetFileAccessPermission('/docs/pub/', array('*' => 'R'));
			$APPLICATION->SetFileAccessPermission('/thurly/admin/disk_bizproc_activity_settings.php', array('2' => 'R'));
			$APPLICATION->SetFileAccessPermission('/thurly/admin/disk_bizproc_selector.php', array('2' => 'R'));
			$APPLICATION->SetFileAccessPermission('/thurly/admin/disk_bizproc_wf_settings.php', array('2' => 'R'));

			\Thurly\Main\UrlPreview\Router::setRouteHandler(
				'/disk/#action#/#fileId#/',
				'disk',
				'\Thurly\Disk\Ui\Preview\File',
				array(
					'action' => '$action',
					'fileId' => '$fileId',
				)
			);

			\Thurly\Main\UrlPreview\Router::setRouteHandler(
					'/docs/pub/#hash#/#action#/',
					'disk',
					'\Thurly\Disk\Ui\Preview\ExternalLink',
					array(
							'action' => '$action',
							'hash' => '$hash',
					)
			);
		}

		return true;
	}
	function UnInstallFiles()
	{
		global $APPLICATION;
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/admin", $_SERVER["DOCUMENT_ROOT"]."/thurly/admin");
			DeleteDirFilesEx("/thurly/js/disk/");
			DeleteDirFilesEx("/thurly/tools/disk/");
			DeleteDirFilesEx("/thurly/services/disk/");
		}
		$APPLICATION->SetFileAccessPermission('/thurly/tools/disk/', array('*' => 'D'));
		$APPLICATION->SetFileAccessPermission('/thurly/services/disk/', array('*' => 'D'));

		return true;
	}

	function InstallUserFields($moduleId = "all")
	{}

	function UnInstallUserFields()
	{
		$ent = new CUserTypeEntity;
		foreach(array("disk_file", "disk_version") as $type)
		{
			$rsData = CUserTypeEntity::GetList(array("ID" => "ASC"), array("USER_TYPE_ID" => $type));
			if ($rsData && ($arRes = $rsData->Fetch()))
			{
				do {
					$ent->Delete($arRes['ID']);
				} while ($arRes = $rsData->Fetch());
			}
		}
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->InstallFiles();
		$this->InstallDB();
		$this->InstallEvents();

		$APPLICATION->IncludeAdminFile(GetMessage("DISK_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/step1.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->errors = array();

		$step = IntVal($step);
		if($step<2)
		{
			if (isModuleInstalled('webdav') && Option::get('disk', 'process_converted', false) === 'Y')
			{
				$this->errors[] = GetMessage("DISK_UNINSTALL_ERROR_MIGRATE_PROCESS");
			}

			$GLOBALS["disk_installer_errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("DISK_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();

			$this->UnInstallEvents();

			$GLOBALS["disk_installer_errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("DISK_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/disk/install/unstep2.php");
		}
	}

	function OnGetTableSchema()
	{
		return array(
			"disk" => array(
				'b_disk_object' => array(
					'ID' => array(
						'b_disk_attached_object' => 'OBJECT_ID',
						'b_disk_deleted_log' => 'OBJECT_ID',
						'b_disk_edit_session' => 'OBJECT_ID',
						'b_disk_object' => 'REAL_OBJECT_ID',
						'b_disk_object^' => 'PARENT_ID',
						'b_disk_object_path' => 'OBJECT_ID',
						'b_disk_object_path^' => 'PARENT_ID',
						'b_disk_right' => 'OBJECT_ID',
						'b_disk_sharing' => 'LINK_OBJECT_ID',
						'b_disk_sharing^' => 'REAL_OBJECT_ID',
						'b_disk_simple_right' => 'OBJECT_ID',
						'b_disk_storage' => 'ROOT_OBJECT_ID',
						'b_disk_version' => 'OBJECT_ID',
						'b_disk_external_link' => 'OBJECT_ID',
						'b_disk_cloud_import' => 'OBJECT_ID',
						'b_disk_object_lock' => 'OBJECT_ID',
					),
				),
				'b_disk_sharing' => array(
					'ID' => array(
						'b_disk_sharing' => 'PARENT_ID',
					)
				),
				'b_disk_storage' => array(
					'ID' => array(
						'b_disk_object' => 'STORAGE_ID',
						'b_disk_sharing' => 'REAL_STORAGE_ID',
						'b_disk_sharing^' => 'LINK_STORAGE_ID',
						'b_disk_deleted_log' => 'STORAGE_ID',
					)
				),
				'b_disk_version' => array(
					'ID' => array(
						'b_disk_attached_object' => 'VERSION_ID',
						'b_disk_external_link' => 'VERSION_ID',
						'b_disk_edit_session' => 'VERSION_ID',
						'b_disk_cloud_import' => 'VERSION_ID',
					)
				),
				'b_disk_tmp_file' => array(
					'ID' => array(
						'b_disk_cloud_import' => 'TMP_FILE_ID',
					)
				),
			),
			"main" => array(
				"b_file" => array(
					"ID" => array(
						"b_disk_object" => "FILE_ID",
						"b_disk_version" => "FILE_ID",
					)
				),
				"b_user" => array(
					"ID" => array(
						'b_disk_object' => 'CREATED_BY',
						'b_disk_object^' => 'UPDATED_BY',
						'b_disk_object^^' => 'DELETED_BY',
						'b_disk_version' => 'CREATED_BY',
						'b_disk_version^' => 'OBJECT_CREATED_BY',
						'b_disk_version^^' => 'OBJECT_UPDATED_BY',
						'b_disk_attached_object' => 'CREATED_BY',
						'b_disk_external_link' => 'CREATED_BY',
						'b_disk_sharing' => 'CREATED_BY',
						'b_disk_edit_session' => 'USER_ID',
						'b_disk_edit_session^' => 'OWNER_ID',
						'b_disk_deleted_log' => 'USER_ID',
						'b_disk_cloud_import' => 'USER_ID',
						'b_disk_object_lock' => 'CREATED_BY',
					)
				),
				"b_task" => array(
					"ID" => array(
						"b_disk_right" => "TASK_ID",
					)
				),
				"b_iblock_element" => array(
					"ID" => array(
						"b_disk_object" => "WEBDAV_ELEMENT_ID",
					)
				),
				"b_iblock_section" => array(
					"ID" => array(
						"b_disk_object" => "WEBDAV_SECTION_ID",
					)
				),
				"b_iblock" => array(
					"ID" => array(
						"b_disk_object" => "WEBDAV_IBLOCK_ID",
					)
				),
			),
		);
	}
	
	public static function OnBeforeIBlockDelete($id)
	{
		$id = (int)$id;
		$query = CIBlock::GetList(array('ID' => 'ASC'), array('TYPE' => 'library', 'ID' => $id));
		if(!$query)
		{
			return;
		}

		$iblock = $query->fetch();
		if(!$iblock)
		{
			return;
		}
		if(\Thurly\Disk\Configuration::isSuccessfullyConverted())
		{
			return false;
		}

		return;
	}

}
?>