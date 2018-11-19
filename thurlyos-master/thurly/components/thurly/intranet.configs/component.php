<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (
	!(
		\Thurly\Main\Loader::includeModule("intranet") && $GLOBALS['USER']->IsAdmin()
		|| \Thurly\Main\Loader::includeModule("thurlyos") && $GLOBALS['USER']->CanDoOperation('thurlyos_config')
	)
)
{
	$APPLICATION->AuthForm(GetMessage("CONFIG_ACCESS_DENIED"));
}

use Thurly\Main\Localization\CultureTable;

$arParams["CONFIG_PAGE"] = $APPLICATION->GetCurPageParam("", array("success", "otp"));
$arParams["CONFIG_PATH_TO_POST"] = SITE_DIR."company/personal/user/".$USER->getId()."/blog/edit/new/";

$arResult["ERROR"] = "";
$arResult["IS_THURLY24"] = IsModuleInstalled("thurlyos");

if(!function_exists("ThurlyOSSaveLogo"))
{
	function ThurlyOSSaveLogo($arFile, $arRestriction = Array(), $deleteFileId = "")
	{
		$oldFileID = COption::GetOptionInt("thurlyos", "client_logo", "");

		$arFile = $arFile + Array(
				"del" => ($oldFileID ? "Y" : ""),
				"old_file" => (intval($oldFileID) > 0 ? intval($oldFileID): 0 ),
				"MODULE_ID" => "thurlyos"
			);

		$max_file_size = (array_key_exists("max_file_size", $arRestriction) ? intval($arRestriction["max_file_size"]) : 0);
		$max_width = (array_key_exists("max_width", $arRestriction) ? intval($arRestriction["max_width"]) : 0);
		$max_height = (array_key_exists("max_height", $arRestriction) ? intval($arRestriction["max_height"]) : 0);
		$extensions = (array_key_exists("extensions", $arRestriction) && strlen($arRestriction["extensions"]) > 0 ? trim($arRestriction["extensions"]) : false);

		$error = CFile::CheckFile($arFile, /*$max_file_size*/0, false, $extensions);
		if (strlen($error)>0)
		{
			return $error;
		}

		if ($max_width > 0 || $max_height > 0)
		{
			$error = CFile::CheckImageFile($arFile, /*$max_file_size*/0, $max_width, $max_height);
			if (strlen($error)>0)
			{
				return $error;
			}
		}

		$arFile["name"] = "logo_".randString(8).".png";
		$fileID = (int)CFile::SaveFile($arFile, "thurlyos");
		if (intval($fileID) && $deleteFileId)
			CFile::Delete($deleteFileId);
		return $fileID;
	}
}

if (\Thurly\Main\Loader::includeModule("thurlyos"))
{
	$arResult["LICENSE_TYPE"] = CThurlyOS::getLicenseType();
	$arResult["IS_LICENSE_PAID"] = CThurlyOS::IsLicensePaid();
	$arResult['SHOW_GOOGLE_API_KEY_FIELD'] = \CThurlyOS::isCustomDomain();
}
elseif(\Thurly\Main\Loader::includeModule('fileman') && class_exists('Thurly\Fileman\UserField\Address'))
{
	$arResult['SHOW_GOOGLE_API_KEY_FIELD'] = true;
}

$arResult["DATE_FORMATS"] = array("DD.MM.YYYY", "DD/MM/YYYY", "MM.DD.YYYY", "MM/DD/YYYY", "YYYY/MM/DD");
$arResult["TIME_FORMATS"] = array("HH:MI:SS", "H:MI:SS T");
$arResult["NAME_FORMATS"] = CSite::GetNameTemplates();
$arResult["ORGANIZATION_TYPE"] = COption::GetOptionString("intranet", "organization_type", "");

$rsSite = CSite::GetByID(SITE_ID);
if ($arSite = $rsSite->Fetch())
{
	$arResult["CUR_DATE_FORMAT"] = $arSite["FORMAT_DATE"];
	$arResult["CUR_TIME_FORMAT"] = str_replace($arResult["CUR_DATE_FORMAT"]." ", "", $arSite["FORMAT_DATETIME"]);
	$arResult["WEEK_START"] = $arSite["WEEK_START"];
	$arResult["CULTURE_ID"] = $arSite["CULTURE_ID"];
	$arResult["CUR_NAME_FORMAT"] = $arSite["FORMAT_NAME"];
}

if (\Thurly\Main\Loader::includeModule("calendar"))
{
	$arResult["WORKTIME_LIST"] = array();
	for ($i = 0; $i < 24; $i++)
	{
		$arResult["WORKTIME_LIST"][strval($i)] = CCalendar::FormatTime($i, 0);
		$arResult["WORKTIME_LIST"][strval($i).'.30'] = CCalendar::FormatTime($i, 30);
	}
	$arResult["CALENDAT_SET"] = CCalendar::GetSettings(array('getDefaultForEmpty' => false));
	$arResult["WEEK_DAYS"] = Array('MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU');
}

// phone number default country
$countriesReference = GetCountryArray();
$arResult['COUNTRIES'] = array();
foreach ($countriesReference['reference_id'] as $k => $v)
{
	$arResult['COUNTRIES'][$v] = $countriesReference['reference'][$k];
}
$phoneNumberDefaultCountry = Thurly\Main\PhoneNumber\Parser::getDefaultCountry();
$arResult['PHONE_NUMBER_DEFAULT_COUNTRY'] = GetCountryIdByCode($phoneNumberDefaultCountry);

$arResult["IS_DISK_CONVERTED"] = COption::GetOptionString('disk', 'successfully_converted', false) == 'Y';
$arResult["IS_TRANSFORMER_INSTALLED"] = \Thurly\Main\ModuleManager::isModuleInstalled('transformer');

$arResult["DISK_VIEWER_SERVICE"] = array();
if (Thurly\Main\Loader::includeModule("disk"))
{
	$documentHandlersManager = \Thurly\Disk\Driver::getInstance()->getDocumentHandlersManager();

	$optionList = array();
	foreach($documentHandlersManager->getHandlersForView() as $handler)
	{
		$optionList[$handler->getCode()] = $handler->getName();
	}
	unset($handler);
	$arResult["DISK_VIEWER_SERVICE"] = $optionList;

	$arResult["DISK_VIEWER_SERVICE_DEFAULT"] = \Thurly\Disk\Configuration::getDefaultViewerServiceCode();

	$arResult["DISK_LIMIT_PER_FILE_SELECTED"] = COption::GetOptionInt("disk", 'disk_version_limit_per_file', 0);
	$arResult["DISK_LIMIT_PER_FILE"] = array(0 => GetMessage('DISK_VERSION_LIMIT_PER_FILE_UNLIMITED'), 3=> 3, 10 => 10, 25 => 25, 50 => 50, 100 => 100, 500 => 500);
}

//logo for thurlyos
if ($_SERVER["REQUEST_METHOD"] == "POST" && check_thurly_sessid())
{
	\Thurly\Intranet\Composite\CacheProvider::deleteUserCache();

	if (isset($_FILES["client_logo"]))
	{
		$error = "";
		$APPLICATION->RestartBuffer();
		$arFile = $_FILES["client_logo"];

		$APPLICATION->RestartBuffer();
		if ($arFile["name"])
		{
			$fileID = ThurlyOSSaveLogo($arFile, array("extensions" => "png", "max_height" => 55, "max_width" => 222));
			if (intval($fileID))
			{
				if ($arResult["IS_THURLY24"])
				{
					COption::SetOptionInt("thurlyos", "client_logo", $fileID);
				}
				else
				{
					COption::SetOptionInt("thurlyos", "client_logo", $fileID, false, SITE_ID);
				}

				$ar["path"] =  CFile::GetPath($fileID);
			}
			else
			{
				$error = str_replace("<br>", "", $fileID);
				$ar["error"] =  $error;
			}

			echo \Thurly\Main\Web\Json::encode($ar);
		}
		die();
	}

	if (isset($_POST["client_delete_logo"]) && $_POST["client_delete_logo"] == "Y")
	{
		$fileId = COption::GetOptionInt("thurlyos", "client_logo", "");
		CFile::Delete($fileId);
		if ($arResult["IS_THURLY24"])
		{
			COption::SetOptionInt("thurlyos", "client_logo", "");
		}
		else
		{
			COption::SetOptionInt("thurlyos", "client_logo", "", false, SITE_ID);
		}

		$APPLICATION->RestartBuffer();
		die();
	}
}

if ($arResult["IS_THURLY24"])
{
	$arResult["IP_RIGHTS"] = array();
	$dbIpRights = Thurly\ThurlyOS\OptionTable::getList(array(
		"filter" => array("=NAME" => "ip_access_rights")
	));
	if ($arIpRights = $dbIpRights->Fetch())
	{
		$arResult["IP_RIGHTS"] = unserialize($arIpRights["VALUE"]);
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST"  && (isset($_POST["save_settings"]) )&& check_thurly_sessid())
{
	if (defined("BX_COMP_MANAGED_CACHE"))
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->ClearByTag("thurlyos_left_menu");
	}

	if (isset($_REQUEST["site_title"]))
	{
		if ($arResult["IS_THURLY24"])
		{
			COption::SetOptionString("thurlyos", "site_title", $_REQUEST["site_title"]);
		}
		else
		{
			COption::SetOptionString("thurlyos", "site_title", $_REQUEST["site_title"], false, SITE_ID);
		}
	}

	if ($arResult["IS_THURLY24"])
	{
		\Thurly\Intranet\Composite\CacheProvider::deleteUserCache();

		if (isset($_REQUEST["logo_name"]) && strlen($_REQUEST["logo_name"])>0)
		{
			COption::SetOptionString("main", "site_name", $_REQUEST["logo_name"]);
			$iblockID = COption::GetOptionInt("intranet", "iblock_structure");
			$db_up_department = CIBlockSection::GetList(Array(), Array("SECTION_ID"=>0, "IBLOCK_ID"=>$iblockID));
			if ($ar_up_department = $db_up_department->Fetch())
			{
				$up_dep_id = $ar_up_department['ID'];
				if (CIBlockRights::UserHasRightTo($iblockID, $up_dep_id, 'section_edit'))
				{
					$section = new CIBlockSection;
					$res = $section->Update($up_dep_id, array("NAME" => $_REQUEST["logo_name"]));
				}
			}
		}

		if (strlen($_POST["email_from"])>0 && check_email($_POST["email_from"]))
		{
			COption::SetOptionString("main", "email_from", $_POST["email_from"]);
			COption::SetOptionString("forum", "FORUM_FROM_EMAIL", $_POST["email_from"]);
		}
		else
			$activateError = GetMessage("CONFIG_EMAIL_ERROR");

		if (\CThurlyOS::IsNetworkAllowed())
		{
			if (CModule::IncludeModule('socialservices'))
			{
				$socnetObj = new \Thurly\Socialservices\Network();
				if (strlen($_POST["network_avaiable"])>0)
				{
					$socnetObj->setEnable(true);
				}
				else
				{
					$socnetObj->setEnable(false);
				}
			}
		}

		//self register
		if (\Thurly\Main\Loader::includeModule("socialservices"))
		{
			\Thurly\Socialservices\Network::setRegisterSettings(array(
				"REGISTER" => isset($_POST["allow_register"]) ? "Y" : "N",
			));
		}

		//allow invite users
		if (strlen($_POST["allow_invite_users"])>0)
			COption::SetOptionString("thurlyos", "allow_invite_users", "Y");
		else
			COption::SetOptionString("thurlyos", "allow_invite_users", "N");
	}

	if (
		$arResult["IS_THURLY24"] && in_array($arResult["LICENSE_TYPE"], array("team", "company", "edu"))
		|| !$arResult["IS_THURLY24"]
	)
	{
		if (strlen($_POST["logo24"])>0)
			COption::SetOptionString("thurlyos", "logo24show", "Y");
		else
			COption::SetOptionString("thurlyos", "logo24show", "N");
	}

	if (strlen($_POST["rating_text_like_y"])>0)
		COption::SetOptionString("main", "rating_text_like_y", htmlspecialcharsbx($_POST["rating_text_like_y"]));
	/*if (strlen($_POST["rating_text_like_n"])>0)
		COption::SetOptionString("main", "rating_text_like_n", htmlspecialcharsbx($_POST["rating_text_like_n"]));*/

//date/time format, week start
	if (
		strlen($_POST["date_format"])>0
		|| strlen($_POST["time_format"]) > 0
		|| strlen($_POST["WEEK_START"]) > 0
	)
	{
		$arFields = array();
		if (in_array($_POST["date_format"], $arResult["DATE_FORMATS"]))
		{
			$arFields["FORMAT_DATE"] = $_POST["date_format"];

			if (in_array($_POST["time_format"], $arResult["TIME_FORMATS"]))
			{
				$arFields["FORMAT_DATETIME"] = $_POST["date_format"]." ".$_POST["time_format"];
			}
		}

		if (isset($_POST["WEEK_START"]))
		{
			$arFields["WEEK_START"] = $_POST["WEEK_START"];
		}
		if (isset($_POST["FORMAT_NAME"]))
		{
			if (!preg_match('/^(?:#TITLE#|#NAME#|#LAST_NAME#|#SECOND_NAME#|#NAME_SHORT#|#LAST_NAME_SHORT#|#SECOND_NAME_SHORT#|#EMAIL#|#ID#|\s|,)+$/D', $_POST["FORMAT_NAME"]))
			{
				$arResult["ERROR"] = GetMessage("CONFIG_FORMAT_NAME_ERROR");
			}
			else
			{
				$arFields["FORMAT_NAME"] = $_POST["FORMAT_NAME"];
			}
		}

		if(!empty($arFields))
		{
			$result = CultureTable::update($arResult["CULTURE_ID"] , $arFields);

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				global $CACHE_MANAGER;
				$CACHE_MANAGER->ClearByTag('sonet_group');
			}
		}
	}

	$SET = array();
	if (isset($_POST["work_time_start"]) && !empty($_POST["work_time_start"]))
		$SET["work_time_start"] = $_POST["work_time_start"];

	if (isset($_POST["work_time_end"]) && !empty($_POST["work_time_end"]))
		$SET["work_time_end"] = $_POST["work_time_end"];

	if (isset($_POST["week_holidays"]))
		$SET["week_holidays"] = implode('|',$_POST['week_holidays']);
	else
		$SET["week_holidays"] = "";

	if (isset($_POST["year_holidays"]))
		$SET["year_holidays"] = $_POST["year_holidays"];
	else
		$SET["year_holidays"] = "";

	if (!empty($SET) && CModule::IncludeModule("calendar"))
	{
		CCalendar::SetSettings($SET);
	}

	if($_POST['phone_number_default_country'] > 0)
	{
		COption::SetOptionInt('main', 'phone_number_default_country', $_POST['phone_number_default_country']);
	}

	if (isset($_POST["organization"]) && in_array($_POST["organization"], array("", "public_organization", "gov_organization")))
	{
		COption::SetOptionString("intranet", "organization_type", $_POST["organization"]);
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag('intranet_ustat');
		}
	}

	if (strlen($_POST["default_viewer_service"])>0 && in_array($_POST["default_viewer_service"], array_keys($arResult["DISK_VIEWER_SERVICE"])))
		COption::SetOptionString("disk", "default_viewer_service", $_POST["default_viewer_service"]);

	if ($arResult["IS_DISK_CONVERTED"])
	{
		if (strlen($_POST["disk_allow_edit_object_in_uf"])>0)
			COption::SetOptionString("disk", "disk_allow_edit_object_in_uf", "Y");
		else
			COption::SetOptionString("disk", "disk_allow_edit_object_in_uf", "N");

		if (strlen($_POST["disk_allow_autoconnect_shared_objects"])>0)
			COption::SetOptionString("disk", "disk_allow_autoconnect_shared_objects", "Y");
		else
			COption::SetOptionString("disk", "disk_allow_autoconnect_shared_objects", "N");

		if($arResult["IS_TRANSFORMER_INSTALLED"])
		{
			if (strlen($_POST["disk_allow_document_transformation"])>0)
				COption::SetOptionString("disk", "disk_allow_document_transformation", "Y");
			else
				COption::SetOptionString("disk", "disk_allow_document_transformation", "N");

			if (strlen($_POST["disk_allow_video_transformation"])>0)
				COption::SetOptionString("disk", "disk_allow_video_transformation", "Y");
			else
				COption::SetOptionString("disk", "disk_allow_video_transformation", "N");

			if (strlen($_POST["disk_transform_files_on_open"])>0)
				COption::SetOptionString("disk", "disk_transform_files_on_open", "Y");
			else
				COption::SetOptionString("disk", "disk_transform_files_on_open", "N");
		}
	}
	else
	{
		if (strlen($_POST["webdav_global"])>0)
			COption::SetOptionString("webdav", "webdav_allow_ext_doc_services_global", "Y");
		else
			COption::SetOptionString("webdav", "webdav_allow_ext_doc_services_global", "N");

		if (strlen($_POST["webdav_local"])>0)
			COption::SetOptionString("webdav", "webdav_allow_ext_doc_services_local", "Y");
		else
			COption::SetOptionString("webdav", "webdav_allow_ext_doc_services_local", "N");

		if (strlen($_POST["webdav_autoconnect_share_group_folder"])>0)
			COption::SetOptionString("webdav", "webdav_allow_autoconnect_share_group_folder", "Y");
		else
			COption::SetOptionString("webdav", "webdav_allow_autoconnect_share_group_folder", "N");
	}

	if (!$arResult["IS_THURLY24"] || $arResult["IS_THURLY24"] && in_array($arResult["LICENSE_TYPE"], array("team", "company", "nfr", "demo", "edu")))
	{
		if (strlen($_POST["disk_version_limit_per_file"])>0 && in_array($_POST["disk_version_limit_per_file"], array_keys($arResult["DISK_LIMIT_PER_FILE"])))
			COption::SetOptionString("disk", "disk_version_limit_per_file", $_POST["disk_version_limit_per_file"]);

		if (strlen($_POST["disk_allow_use_external_link"])>0)
			COption::SetOptionString("disk", "disk_allow_use_external_link", "Y");
		else
			COption::SetOptionString("disk", "disk_allow_use_external_link", "N");

		if (strlen($_POST["disk_object_lock_enabled"])>0)
			COption::SetOptionString("disk", "disk_object_lock_enabled", "Y");
		else
			COption::SetOptionString("disk", "disk_object_lock_enabled", "N");
	}

	if (strlen($_POST["allow_livefeed_toall"])>0)
		COption::SetOptionString("socialnetwork", "allow_livefeed_toall", "Y");
	else
		COption::SetOptionString("socialnetwork", "allow_livefeed_toall", "N");

	if (strlen($_POST["default_livefeed_toall"])>0)
		COption::SetOptionString("socialnetwork", "default_livefeed_toall", "Y");
	else
		COption::SetOptionString("socialnetwork", "default_livefeed_toall", "N");

	if (
		is_array($_POST["livefeed_toall_rights"])
		&& count($_POST["livefeed_toall_rights"]) > 0
	)
		$val = serialize($_POST["livefeed_toall_rights"]);
	else
		$val = serialize(array("AU"));

	COption::SetOptionString("socialnetwork", "livefeed_toall_rights", $val);

	if (strlen($_POST["allow_new_user_lf"])>0)
		COption::SetOptionString("intranet", "BLOCK_NEW_USER_LF_SITE", "N", false, SITE_ID);
	else
		COption::SetOptionString("intranet", "BLOCK_NEW_USER_LF_SITE", "Y", false, SITE_ID);

//im chat
	if (CModule::IncludeModule('im'))
	{
		if (isset($_POST["allow_general_chat_toall"]))
		{
			if (in_array('AU', $_POST["imchat_toall_rights"]) || empty($_POST["imchat_toall_rights"]))
			{
				CIMChat::SetAccessToGeneralChat(true);
			}
			else
			{
				CIMChat::SetAccessToGeneralChat(false, $_POST["imchat_toall_rights"]);
			}
		}
		else
		{
			CIMChat::SetAccessToGeneralChat(false);
		}
	}

	if (strlen($_POST["general_chat_message_join"])>0)
		COption::SetOptionString("im", "general_chat_message_join", true);
	else
		COption::SetOptionString("im", "general_chat_message_join", false);

	if (strlen($_POST["general_chat_message_leave"])>0)
		COption::SetOptionString("im", "general_chat_message_leave", true);
	else
		COption::SetOptionString("im", "general_chat_message_leave", false);

	if (strlen($_POST["url_preview_enable"])>0)
		COption::SetOptionString("main", "url_preview_enable", "Y");
	else
		COption::SetOptionString("main", "url_preview_enable", "N");

//security
	if (Thurly\Main\Loader::includeModule("security"))
	{
		$otpGetParam = false;
		if (isset($_POST["security_otp_days"]))
		{
			$numDays = intval($_POST["security_otp_days"]);
			if ($numDays)
				Thurly\Security\Mfa\Otp::setSkipMandatoryDays($numDays);
		}
		if (isset($_POST["security_otp"]) && CModule::IncludeModule("security"))
		{
			$currentMandatory = CSecurityUser::IsOtpMandatory();
			if (!$currentMandatory)
			{
				$otpGetParam = true;
			}
		}
		Thurly\Security\Mfa\Otp::setMandatoryUsing(isset($_POST["security_otp"]) ? true : false);
	}

	if ($arResult["IS_THURLY24"])
	{
		//features
		if (isset($_POST["feature_crm"]) && !IsModuleInstalled("crm"))
		{
			COption::SetOptionString("thurlyos", "feature_crm", "Y");
			\Thurly\Main\ModuleManager::add("crm");
		}
		elseif (!isset($_POST["feature_crm"]) && IsModuleInstalled("crm"))
		{
			COption::SetOptionString("thurlyos", "feature_crm", "N");
			\Thurly\Main\ModuleManager::delete("crm");
		}

		if (in_array($arResult["LICENSE_TYPE"], array("team", "company", "nfr", "edu")))
		{
			if (isset($_POST["feature_extranet"]) && !IsModuleInstalled("extranet"))
			{
				COption::SetOptionString("thurlyos", "feature_extranet", "Y");
				CThurlyOS::updateExtranetUsersActivity(true);
				\Thurly\Main\ModuleManager::add("extranet");
			}
			elseif (!isset($_POST["feature_extranet"]) && IsModuleInstalled("extranet"))
			{
				COption::SetOptionString("thurlyos", "feature_extranet", "N");
				CThurlyOS::updateExtranetUsersActivity(false);
				\Thurly\Main\ModuleManager::delete("extranet");
			}
		}
		if (in_array($arResult["LICENSE_TYPE"], array("company", "nfr", "edu")))
		{
			$arModules = array("timeman", "meeting", "lists");
			foreach($arModules as $module_id)
			{
				if (isset($_POST["feature_".$module_id]) && !IsModuleInstalled($module_id))
				{
					COption::SetOptionString("thurlyos", "feature_".$module_id, "Y");
					\Thurly\Main\ModuleManager::add($module_id);
				}
				elseif (!isset($_POST["feature_".$module_id]) && IsModuleInstalled($module_id))
				{
					COption::SetOptionString("thurlyos", "feature_".$module_id, "N");
					\Thurly\Main\ModuleManager::delete($module_id);
				}
			}
		}

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag('sonet_group');
		}

		//ip
		$arIpSettings = array();
		foreach ($_POST as $key=>$arItem)
		{
			if (strpos($key, "ip_access_rights_") !== false)
			{
				$right = str_replace("ip_access_rights_", "", $key);
				if (is_array($arItem))
				{
					foreach ($arItem as $ip)
					{
						$ip = trim($ip);
						if ($ip)
						{
							if (strpos($ip, "-") !== false)
							{
								$ipRange = explode("-", $ip);
								preg_match('#^(?:\d{1,3}\.){3}\d{1,3}$#', $ipRange[0], $matches1);
								preg_match('#^(?:\d{1,3}\.){3}\d{1,3}$#', $ipRange[1], $matches2);
								if ($matches1[0] && $matches2[0])
									$arIpSettings[$right][] = $ip;
								else
									$arResult["ERROR"] = GetMessage("CONFIG_IP_ERROR");
							}
							else
							{
								preg_match('#^(?:\d{1,3}\.){3}\d{1,3}$#', $ip, $matches);
								if ($matches[0])
									$arIpSettings[$right][] = $ip;
								else
									$arResult["ERROR"] = GetMessage("CONFIG_IP_ERROR");
							}
						}
					}
				}
			}
		}
	}

	if($arResult['SHOW_GOOGLE_API_KEY_FIELD'])
	{
		if($arResult['IS_THURLY24'])
		{
			\Thurly\Main\Config\Option::set('thurlyos', 'google_map_api_key', $_POST['google_api_key']);
			\Thurly\Main\Config\Option::set('thurlyos', 'google_map_api_key_host', BX24_HOST_NAME);
		}
		else
		{
			\Thurly\Main\Config\Option::set('fileman', 'google_map_api_key', $_POST['google_api_key']);
		}
	}

	if (!$arResult["ERROR"])
	{
		if ($arResult["IS_THURLY24"])
		{
			if (empty($arIpSettings))
			{
				Thurly\ThurlyOS\OptionTable::delete("ip_access_rights");
			}
			else
			{
				$arIpSettingsSer = serialize($arIpSettings);
				if (!empty($arResult["IP_RIGHTS"]))
				{
					Thurly\ThurlyOS\OptionTable::update("ip_access_rights", array("VALUE" => $arIpSettingsSer));
				}
				else
				{
					Thurly\ThurlyOS\OptionTable::add(
						array(
							"NAME" => "ip_access_rights",
							"VALUE" => $arIpSettingsSer
						)
					);
				}
			}
			$arResult["IP_RIGHTS"] = $arIpSettings;
		}

		if ($otpGetParam)
			$url = $APPLICATION->GetCurPageParam("success=Y&otp=Y");
		else
			$url = $APPLICATION->GetCurPageParam("success=Y");
		LocalRedirect($url);
	}
}

if (Thurly\Main\Loader::includeModule("security"))
{
	$arResult["SECURITY_IS_USER_OTP_ACTIVE"] = CSecurityUser::IsUserOtpActive($USER->GetID());
	$arResult["SECURITY_OTP"] = Thurly\Security\Mfa\Otp::isMandatoryUsing();
	$arResult["SECURITY_OTP_DAYS"] = Thurly\Security\Mfa\Otp::getSkipMandatoryDays();
}

if ($arResult["IS_THURLY24"])
{
	$arResult['CREATOR_CONFIRMED'] = \CThurlyOS::isEmailConfirmed();
	$arResult['ALLOW_DOMAIN_CHANGE'] = !\CThurlyOS::isDomainChanged();

	if($arResult['ALLOW_DOMAIN_CHANGE'])
	{
		\CJSCore::Init(array('b24_rename'));
	}

	$arResult["ALLOW_SELF_REGISTER"] = "N";
	if(\Thurly\Main\Loader::includeModule("socialservices"))
	{
		$registerSettings = \Thurly\Socialservices\Network::getRegisterSettings();
		$arResult["ALLOW_SELF_REGISTER"] = $registerSettings["REGISTER"] == "Y" ? "Y" : "N";
	}

	$arResult["ALLOW_INVITE_USERS"] = COption::GetOptionString("thurlyos", "allow_invite_users", "N");
	$arResult["ALLOW_NEW_USER_LF"] = (
	COption::GetOptionString("intranet", "BLOCK_NEW_USER_LF_SITE", "N", SITE_ID) == 'Y'
		? 'N'
		: 'Y'
	);

	$arResult['ALLOW_NETWORK_CHANGE'] = \CThurlyOS::IsNetworkAllowed() ? 'Y' : 'N';

	$arResult["NETWORK_AVAILABLE"] = 'N';
	if ($arResult['CREATOR_CONFIRMED'] && CModule::IncludeModule('socialservices'))
	{
		$socnetObj = new \Thurly\Socialservices\Network();
		$arResult["NETWORK_AVAILABLE"] = $socnetObj->isOptionEnabled() ? "Y" : "N";
	}

	$billingCurrency = CThurlyOS::BillingCurrency();
	$arProductPrices = CThurlyOS::getPrices($billingCurrency);
	$arResult["PROJECT_PRICE"] = CThurlyOS::ConvertCurrency($arProductPrices["TF1"]["PRICE"], $billingCurrency);
}

if($arResult['SHOW_GOOGLE_API_KEY_FIELD'])
{
	$arResult['GOOGLE_API_KEY'] = \Thurly\Fileman\UserField\Address::getApiKey();

	if($arResult['IS_THURLY24'])
	{
		$arResult['GOOGLE_API_KEY_HOST'] = \Thurly\Main\Config\Option::get('thurlyos', 'google_map_api_key_host');
	}
}

$this->IncludeComponentTemplate();
?>
