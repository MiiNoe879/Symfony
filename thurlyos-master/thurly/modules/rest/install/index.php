<?
IncludeModuleLangFile(__FILE__);

if(class_exists("rest")) return;
class rest extends CModule
{
	var $MODULE_ID = "rest";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";

	private $errors = false;

	function rest()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("REST_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("REST_MODULE_DESCRIPTION");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $APPLICATION;

		$this->errors = false;

		// Database tables creation
		if(strtolower($DB->type) !== 'mysql')
		{
			$this->errors = array(
				GetMessage('REST_DB_NOT_SUPPORTED'),
			);
		}
		elseif(!$DB->Query("SELECT 'x' FROM b_rest_app WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/db/".strtolower($DB->type)."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		RegisterModule("rest");

		COption::SetOptionString("rest", "server_path", "/rest");

		$eventManager = \Thurly\Main\EventManager::getInstance();

		$eventManager->registerEventHandler("main", "OnBeforeProlog", "rest", "CRestEventHandlers", "OnBeforeProlog", 49);

		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', 'CThurlyRestEntity', 'OnRestServiceBuildDescription');
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Thurly\Rest\Api\User', 'onRestServiceBuildDescription');
		$eventManager->registerEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Thurly\Rest\Api\Placement', 'onRestServiceBuildDescription');

		$eventManager->registerEventHandler("main", "OnApplicationsBuildList", "main", '\Thurly\Rest\APAuth\Application', "onApplicationsBuildList", 100, "modules/rest/lib/apauth/application.php");

		$eventManager->registerEventHandler("im", "OnAfterConfirmNotify", "rest", "\\Thurly\\Rest\\NotifyIm", "receive");

		$eventManager->registerEventHandler("rest", "\\Thurly\\Rest\\APAuth\\Password::OnDelete", "rest", "\\Thurly\\Rest\\APAuth\\PermissionTable", "onPasswordDelete");

		if(CModule::IncludeModule('iblock'))
		{
			COption::SetOptionString("rest", "entity_iblock_type", "rest_entity");

			$arFields = array(
				'ID' => 'rest_entity',
				'SECTIONS' => 'Y',
				'IN_RSS' => 'N',
				'SORT' => 1000,
				'LANG' => array(
					'en' => array(
						'NAME' => 'MP applications entity storage',
						'SECTION_NAME' => 'Sections',
						'ELEMENT_NAME' => 'Elements'
					)
				)
			);

			$dbRes = CIBlockType::GetByID($arFields['ID']);
			if(!$dbRes->Fetch())
			{
				$obBlocktype = new CIBlockType;
				$obBlocktype->Add($arFields);
			}
		}

		if(!\Thurly\Main\ModuleManager::isModuleInstalled("oauth"))
		{
			$eventManager->registerEventHandler("rest", "onRestCheckAuth", "rest", "\\Thurly\\Rest\\OAuth\\Auth", "onRestCheckAuth");
		}

		$eventManager->registerEventHandler("rest", "onRestCheckAuth", "rest", "\\Thurly\\Rest\\APAuth\\Auth", "onRestCheckAuth");
		$eventManager->registerEventHandler("rest", "onRestCheckAuth", "rest", "\\Thurly\\Rest\\SessionAuth\\Auth", "onRestCheckAuth");

		CAgent::AddAgent("Thurly\\Rest\\Marketplace\\Client::getNumUpdates();", "rest", "N", 86400);

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $APPLICATION;

		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/db/".strtolower($DB->type)."/uninstall.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		$eventManager = \Thurly\Main\EventManager::getInstance();

		$eventManager->unRegisterEventHandler("main", "OnBeforeProlog", "rest", "CRestEventHandlers", "OnBeforeProlog");

		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', 'CThurlyRestEntity', 'OnRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Thurly\Rest\Api\User', 'onRestServiceBuildDescription');
		$eventManager->unRegisterEventHandler('rest', 'OnRestServiceBuildDescription', 'rest', '\Thurly\Rest\Api\Placement', 'onRestServiceBuildDescription');

		$eventManager->unRegisterEventHandler("rest", "onRestCheckAuth", "rest", "\\Thurly\\Rest\\OAuth\\Auth", "onRestCheckAuth");

		$eventManager->unRegisterEventHandler("rest", "onRestCheckAuth", "rest", "\\Thurly\\Rest\\APAuth\\Auth", "onRestCheckAuth");

		$eventManager->unRegisterEventHandler("rest", "onRestCheckAuth", "rest", "\\Thurly\\Rest\\SessionAuth\\Auth", "onRestCheckAuth");

		$eventManager->unRegisterEventHandler("main", "OnApplicationsBuildList", "main", '\Thurly\Rest\APAuth\Application', "onApplicationsBuildList", "modules/rest/lib/apauth/application.php");

		$eventManager->unRegisterEventHandler("im", "OnAfterConfirmNotify", "rest", "\\Thurly\\Rest\\NotifyIm", "receive");

		$eventManager->unRegisterEventHandler("rest", "\\Thurly\\Rest\\APAuth\\Password::OnDelete", "rest", "\\Thurly\\Rest\\APAuth\\PermissionTable", "onPasswordDelete");

		CAgent::RemoveModuleAgents("rest");

		UnRegisterModule("rest");

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
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/components", $_SERVER["DOCUMENT_ROOT"]."/thurly/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/js", $_SERVER["DOCUMENT_ROOT"]."/thurly/js", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/tools", $_SERVER["DOCUMENT_ROOT"]."/thurly/tools", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/services", $_SERVER["DOCUMENT_ROOT"]."/thurly/services", true, true);

		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/public", $_SERVER["DOCUMENT_ROOT"]."/", true, true);

		// delete old urlrewrite rule
		CUrlRewriter::Delete(array(
			'CONDITION' => '#^/rest/#',
			'PATH' => '/rest/index.php'
		));

		CUrlRewriter::Add(array(
			"CONDITION" => "#^/rest/#",
			"RULE" => "",
			"PATH" => "/thurly/services/rest/index.php",
		));

		CUrlRewriter::Add(array(
			"CONDITION" => "#^/marketplace/#",
			"RULE" => "",
			"ID" => "thurly:rest.marketplace",
			"PATH" => "/marketplace/index.php",
		));

		CUrlRewriter::Add(array(
			"CONDITION" => "#^/marketplace/local/#",
			"RULE" => "",
			"ID" => "thurly:rest.marketplace.localapp",
			"PATH" => "/marketplace/local/index.php",
		));

		CUrlRewriter::Add(array(
			"CONDITION" => "#^/marketplace/app/#",
			"RULE" => "",
			"ID" => "thurly:app.layout",
			"PATH" => "/marketplace/app/index.php",
		));

		CUrlRewriter::Add(array(
			"CONDITION" => "#^/marketplace/hook/#",
			"RULE" => "",
			"ID" => "thurly:rest.hook",
			"PATH" => "/marketplace/hook/index.php",
		));

		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $USER, $step, $DB;
		$step = IntVal($step);

		if(!$USER->IsAdmin())
			return;

		if(strtolower($DB->type) !== 'mysql')
		{
			$APPLICATION->ThrowException(GetMessage('REST_DB_NOT_SUPPORTED'));

			$APPLICATION->IncludeAdminFile(GetMessage("REST_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/step1.php");
		}
		else
		{
			if(!check_thurly_sessid())
			{
				$step = 1;
			}

			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("REST_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/step1.php");
			}
			elseif($step == 2)
			{
				$this->InstallDB(array());
				$this->InstallFiles(array());

				$GLOBALS["errors"] = $this->errors;

				$APPLICATION->IncludeAdminFile(GetMessage("REST_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/step2.php");
			}
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $USER, $step;
		if($USER->IsAdmin())
		{
			$step = IntVal($step);
			if($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("REST_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/unstep1.php");
			}
			elseif($step == 2)
			{
				$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
				));
				$this->UnInstallFiles();

				$GLOBALS["errors"] = $this->errors;

				$APPLICATION->IncludeAdminFile(GetMessage("REST_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/rest/install/unstep2.php");
			}
		}
	}
}
?>