<?
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
if (class_exists("thurlycloud"))
	return;

class thurlycloud extends CModule
{
	var $MODULE_ID = "thurlycloud";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";
	var $errors = false;

	function thurlycloud()
	{
		$arModuleVersion = array();
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("BCL_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("BCL_MODULE_DESCRIPTION");
	}

	function GetModuleTasks()
	{
		return array(
			'thurlycloud_deny' => array(
				'LETTER' => 'D',
				'BINDING' => 'module',
				'OPERATIONS' => array(
				)
			),
			'thurlycloud_control' => array(
				'LETTER' => 'W',
				'BINDING' => 'module',
				'OPERATIONS' => array(
					'thurlycloud_monitoring',
					'thurlycloud_backup',
					'thurlycloud_cdn',
				)
			),
		);
	}

	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		// Database tables creation
		if (!$DB->Query("SELECT 'x' FROM b_thurlycloud_option WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/db/".strtolower($DB->type)."/install.sql");
		}
		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			$this->InstallTasks();
			RegisterModule("thurlycloud");
			RegisterModuleDependences("main", "OnAdminInformerInsertItems", "thurlycloud", "CThurlyCloudCDN", "OnAdminInformerInsertItems");
			RegisterModuleDependences("main", "OnAdminInformerInsertItems", "thurlycloud", "CThurlyCloudBackup", "OnAdminInformerInsertItems");
			RegisterModuleDependences("mobileapp", "OnBeforeAdminMobileMenuBuild", "thurlycloud", "CThurlyCloudMobile", "OnBeforeAdminMobileMenuBuild");

			CModule::IncludeModule("thurlycloud");
		}
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;
		$this->errors = false;
		UnRegisterModuleDependences("main", "OnEndBufferContent", "thurlycloud", "CThurlyCloudCDN", "OnEndBufferContent");
		UnRegisterModuleDependences("main", "OnAdminInformerInsertItems", "thurlycloud", "CThurlyCloudCDN", "OnAdminInformerInsertItems");
		UnRegisterModuleDependences("main", "OnAdminInformerInsertItems", "thurlycloud", "CThurlyCloudBackup", "OnAdminInformerInsertItems");
		UnRegisterModuleDependences("mobileapp", "OnBeforeAdminMobileMenuBuild", "thurlycloud", "CThurlyCloudMobile", "OnBeforeAdminMobileMenuBuild");
		if (!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/db/".strtolower($DB->type)."/uninstall.sql");
		}
		UnRegisterModule("thurlycloud");
		if ($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
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

	function InstallFiles($arParams = array())
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/admin", $_SERVER["DOCUMENT_ROOT"]."/thurly/admin", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/gadgets", $_SERVER["DOCUMENT_ROOT"]."/thurly/gadgets", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/components", $_SERVER["DOCUMENT_ROOT"]."/thurly/components", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/js", $_SERVER["DOCUMENT_ROOT"]."/thurly/js", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/thurly/admin");
			DeleteDirFilesEx("/thurly/js/thurlycloud/");
		}
		return true;
	}

	function DoInstall()
	{
		global $USER, $APPLICATION, $step;
		if ($USER->IsAdmin())
		{
			$step = IntVal($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("BCL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/step1.php");
			}
			elseif ($step == 2)
			{
				if ($this->InstallDB())
				{
					$this->InstallEvents();
					$this->InstallFiles();
				}
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("BCL_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/step2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $USER, $APPLICATION, $step;
		if ($USER->IsAdmin())
		{
			$step = IntVal($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("BCL_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/unstep1.php");
			}
			elseif ($step == 2)
			{
				$this->UnInstallDB(array(
					"save_tables" => $_REQUEST["save_tables"],
				));
				//message types and templates
				if ($_REQUEST["save_templates"] != "Y")
				{
					$this->UnInstallEvents();
				}
				$this->UnInstallFiles();
				$GLOBALS["errors"] = $this->errors;
				$APPLICATION->IncludeAdminFile(GetMessage("BCL_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlycloud/install/unstep2.php");
			}
		}
	}
}
?>
