<?php
IncludeModuleLangFile(__FILE__);

if(class_exists("translate")) return;

Class translate extends CModule
{
	var $MODULE_ID = "translate";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function translate()
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

		$this->MODULE_NAME = GetMessage("TRANS_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("TRANS_MODULE_DESCRIPTION");
		$this->MODULE_CSS = "/thurly/modules/translate/translate.css";
	}

	function InstallDB()
	{
		RegisterModule("translate");
		RegisterModuleDependences('main', 'OnPanelCreate', 'translate', 'CTranslateEventHandlers', 'TranslatOnPanelCreate');
		return true;
	}

	function UnInstallDB()
	{
		COption::RemoveOption("translate");
		UnRegisterModuleDependences('main', 'OnPanelCreate', 'translate', 'CTranslateEventHandlers', 'TranslatOnPanelCreate');
		UnRegisterModule("translate");
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
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/translate/install/admin", $_SERVER["DOCUMENT_ROOT"]."/thurly/admin", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/translate/install/images", $_SERVER["DOCUMENT_ROOT"]."/thurly/images/translate", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/translate/install/themes", $_SERVER["DOCUMENT_ROOT"]."/thurly/themes", true, true);
		}
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/translate/install/admin", $_SERVER["DOCUMENT_ROOT"]."/thurly/admin");
		DeleteDirFilesEx("/thurly/images/translate/");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/translate/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/thurly/themes/.default");//css
		DeleteDirFilesEx("/thurly/themes/.default/icons/translate/");//icons
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallDB();
		$this->InstallFiles();
		$APPLICATION->IncludeAdminFile(GetMessage("TRANSLATE_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/translate/install/step.php");
	}

	function DoUninstall()
	{
		global $APPLICATION;
		$this->UnInstallFiles();
		$this->UnInstallDB();
		$APPLICATION->IncludeAdminFile(GetMessage("TRANSLATE_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/translate/install/unstep.php");
	}
}