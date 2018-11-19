<?php
require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/include.php"))."/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/start.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/classes/general/virtual_io.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/classes/general/virtual_file.php");

$instance = \Thurly\Main\Application::getInstance();
$instance->initializeExtendedKernel(
    array("get" => $_GET, 
        "post" => $_POST, 
        "files" => $_FILES, 
        "cookie" => $_COOKIE, 
        "server" => $_SERVER, 
        "env" => $_ENV)
);

$APPLICATION = new CMain;

if (defined("SITE_ID")) define("LANG", SITE_ID);
if (defined("LANG")) {
    if (defined("ADMIN_SECTION") && ADMIN_SECTION === true) 
        $db_lang = CLangAdmin::GetByID(LANG);
    else 
        $db_lang = CLang::GetByID(LANG);
    $arLang = $db_lang->Fetch();
    if (!$arLang) {
        throw new \Thurly\Main\SystemException("Incorrect site: ".LANG.".");
    }
} else {
    $arLang = $APPLICATION->GetLang();
    define("LANG", $arLang["LID"]);
}

$_LID = $arLang["LID"];

if (!defined("SITE_ID")) 
    define("SITE_ID", $arLang["LID"]); 

define("SITE_DIR", $arLang["DIR"]);
define("SITE_SERVER_NAME", $arLang["SERVER_NAME"]);
define("SITE_CHARSET", $arLang["CHARSET"]);
define("FORMAT_DATE", $arLang["FORMAT_DATE"]);
define("FORMAT_DATETIME", $arLang["FORMAT_DATETIME"]);
define("LANG_DIR", $arLang["DIR"]);
define("LANG_CHARSET", $arLang["CHARSET"]);
define("LANG_ADMIN_LID", $arLang["LANGUAGE_ID"]);
define("LANGUAGE_ID", $arLang["LANGUAGE_ID"]);

$context = $instance->getContext();
$context->setLanguage(LANGUAGE_ID);
$context->setCulture(new \Thurly\Main\Context\Culture($arLang));

$request = $context->getRequest();
if (!$request->isAdminSection()) {
    $context->setSite(SITE_ID);
}

$instance->start();
$APPLICATION->reinitPath();

if (!defined("POST_FORM_ACTION_URI")) {
    define("POST_FORM_ACTION_URI", htmlspecialcharsbx(GetRequestUri()));
}

// Include lang files
$MESS = array();
$ALL_LANG_FILES = array();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/tools.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/database.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/main.php");
IncludeModuleLangFile(__FILE__);

// Set error reporting
error_reporting(COption::GetOptionInt("main", "error_reporting", E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR | E_PARSE) & ~E_STRICT & ~E_DEPRECATED);

if (!defined("BX_COMP_MANAGED_CACHE") && COption::GetOptionString("main", "component_managed_cache_on", "Y") <> "N") {
    define("BX_COMP_MANAGED_CACHE", true);
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/filter_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/ajax_tools.php");

define("INTRANET_EDITION", "Y");

/****************************
 *    CBX Fatures Class     *
 ****************************/
class CBXFeatures {
    private static $tryDaysCount = 30;
    private static $arBXFeatures = array(
        "Portal" => array(
            "CompanyCalendar", 
            "CompanyPhoto", 
            "CompanyVideo",
            "CompanyCareer",
            "StaffChanges",
            "StaffAbsence",
            "CommonDocuments",
            "MeetingRoomBookingSystem",
            "Wiki",
            "Learning",
            "Vote",
            "WebLink",
            "Subscribe",
            "Friends",
            "PersonalFiles",
            "PersonalBlog",
            "PersonalPhoto",
            "PersonalForum",
            "Blog",
            "Forum",
            "Gallery",
            "Board",
            "MicroBlog",
            "WebMessenger",
        ), 
        "Communications" => array(
            "Tasks",
            "Calendar",
            "Workgroups",
            "Jabber",
            "VideoConference",
            "Extranet",
            "SMTP",
            "Requests",
            "DAV",
            "intranet_sharepoint",
            "timeman",
            "Idea",
            "Meeting",
            "EventList",
            "Salary",
            "XDImport",
        ),
        "Enterprise" => array(
            "BizProc",
            "Lists",
            "Support",
            "Analytics",
            "crm",
            "Controller",
        ), 
        "Holding" => array(
            "Cluster",
            "MultiSites",
        ),
    );
    private static $xmlFeatures = false;
    private static $arCpfMapValue = false;
    private static function _init() {
        if (self::$xmlFeatures == false) {
            self::$xmlFeatures = array();
            foreach(self::$arBXFeatures as $key => $arAllFeatures) {
                foreach($arAllFeatures as $f)
                    self::$xmlFeatures[$f] = $key;
            }
        }
        if (self::$arCpfMapValue == false) {
            self::$arCpfMapValue = array();
            $cpf_map_value = COption::GetOptionString("main", "~cpf_map_value", "");
            if (strlen($cpf_map_value) > 0) {
                $cpf_map_value = base64_decode($cpf_map_value);
                self::$arCpfMapValue = unserialize($cpf_map_value);
                if (!is_array(self::$arCpfMapValue))
                    self::$arCpfMapValue = array();
            }
            if (count(self::$arCpfMapValue) <= 0) 
                self::$arCpfMapValue = array("e" => array(), "f" => array());
        }
    }
    public static function InitiateEditionsSettings($_419611900) {
        self::_init();
        $arFeatures = array();
        foreach(self::$arBXFeatures as $key => $arAllFeatures) {
            $by = in_array($key, $_419611900);
            self::$arCpfMapValue["e"][$key] = ($by ? array("F") : array("X"));
            foreach($arAllFeatures as $f) {
                self::$arCpfMapValue["f"][$f] = $by;
                if (!$by) 
                    $arFeatures[] = array($f, false);
            }
        }
        $cpf_map_value = serialize(self::$arCpfMapValue);
        $cpf_map_value = base64_encode($cpf_map_value);
        COption::SetOptionString("main", "~cpf_map_value", $cpf_map_value);
        foreach($arFeatures as $feature)
            self::ChangeFeatureSettings($feature[0], $feature[1]);
    }

    public static function IsFeatureEnabled($f) {
        if (strlen($f) <= 0)
            return true;
        self::_init();
        if (!array_key_exists($f, self::$xmlFeatures))
            return true;
        if (self::$xmlFeatures[$f] == "Portal")
            $arFeatureSettings = array("F");
        elseif(array_key_exists(self::$xmlFeatures[$f], self::$arCpfMapValue["e"]))
            $arFeatureSettings = self::$arCpfMapValue["e"][self::$xmlFeatures[$f]];
        else
            $arFeatureSettings = array("X");
        if ($arFeatureSettings[0] != "F" && $arFeatureSettings[0] != "D") {
            return false;
        }
        elseif($arFeatureSettings[0] == "D") {
            if ($arFeatureSettings[1] < mktime(0, 0, 0, date("m"), date("d") - self::$tryDaysCount, date("Y"))) {
                if (!isset($arFeatureSettings[2]) || !$arFeatureSettings[2]) 
                    self::__1432439879(self::$xmlFeatures[$f]);
                return false;
            }
        }
        return !array_key_exists($f, self::$arCpfMapValue["f"]) || self::$arCpfMapValue["f"][$f];
    }

    public static function IsFeatureInstalled($f) {
        if (strlen($f) <= 0) return true;
        self::_init();
        return (array_key_exists($f, self::$arCpfMapValue["f"]) && self::$arCpfMapValue["f"][$f]);
    }

    public static function IsFeatureEditable($f) {
        if (strlen($f) <= 0)
            return true;
        self::_init();
        if (!array_key_exists($f, self::$xmlFeatures))
            return true;
        if (self::$xmlFeatures[$f] == "Portal") $arFeatureSettings = array("F");
        elseif(array_key_exists(self::$xmlFeatures[$f], self::$arCpfMapValue["e"])) $arFeatureSettings = self::$arCpfMapValue["e"][self::$xmlFeatures[$f]];
        else $arFeatureSettings = array("X");
        if ($arFeatureSettings[0] != "F" && $arFeatureSettings[0] != "D") {
            return false;
        }
        elseif($arFeatureSettings[0] == "D") {
            if ($arFeatureSettings[1] < mktime(0, 0, 0, date("m"), date("d") - self::$tryDaysCount, date("Y"))) {
                if (!isset($arFeatureSettings[2]) || !$arFeatureSettings[2])
                    self::__1432439879(self::$xmlFeatures[$f]);
                return false;
            }
        }
        return true;
    }

    private static function ChangeFeatureSettings($f, $settings) {
        if (method_exists("CBXFeatures", "On" . $f . "SettingsChange"))
            call_user_func_array(array("CBXFeatures", "On" . $f . "SettingsChange"), array($f, $settings));
        $events = GetModuleEvents("main", "On" . $f . "SettingsChange");
        while ($event = $events->Fetch())
            ExecuteModuleEventEx($event, array($f, $settings));
    }

    public static function SetFeatureEnabled($f, $installFlag = true, $modify = true) {
        if (strlen($f) <= 0)
            return;
        if (!self::IsFeatureEditable($f))
            $installFlag = false;
        $installFlag = ($installFlag ? true : false);
        self::_init();
        $needModify = (!array_key_exists($f, self::$arCpfMapValue["f"]) && $installFlag || array_key_exists($f, self::$arCpfMapValue["f"]) && $installFlag != self::$arCpfMapValue["f"][$f]);
        self::$arCpfMapValue["f"][$f] = $installFlag;
        $cpf_map_value = serialize(self::$arCpfMapValue);
        $cpf_map_value = base64_encode($cpf_map_value);
        COption::SetOptionString("main", "~cpf_map_value", $cpf_map_value);
        if ($needModify && $modify)
            self::ChangeFeatureSettings($f, $installFlag);
    }

    private static function __1432439879($key) {
        if (strlen($key) <= 0 || $key == "Portal")
            return;
        self::_init();
        if (!array_key_exists($key, self::$arCpfMapValue["e"]) || array_key_exists($key, self::$arCpfMapValue["e"]) && self::$arCpfMapValue["e"][$key][0] != "D")
            return;
        if (isset(self::$arCpfMapValue["e"][$key][2]) && self::$arCpfMapValue["e"][$key][2])
            return;
        $arFeatures = array();
        if (array_key_exists($key, self::$arBXFeatures) && is_array(self::$arBXFeatures[$key])) {
            foreach(self::$arBXFeatures[$key] as $f) {
                if (array_key_exists($f, self::$arCpfMapValue["f"]) && self::$arCpfMapValue["f"][$f]) {
                    self::$arCpfMapValue["f"][$f] = false;
                    $arFeatures[] = array($f, false);
                }
            }
            self::$arCpfMapValue["e"][$key][2] = true;
        }
        $cpf_map_value = serialize(self::$arCpfMapValue);
        $cpf_map_value = base64_encode($cpf_map_value);
        COption::SetOptionString("main", "~cpf_map_value", $cpf_map_value);
        foreach($arFeatures as $feature) 
            self::ChangeFeatureSettings($feature[0], $feature[1]);
    }

    public static function ModifyFeaturesSettings($_419611900, $arAllFeatures) {
        self::_init();
        foreach($_419611900 as $key => $_1903910598)
            self::$arCpfMapValue["e"][$key] = $_1903910598;
        $arFeatures = array();
        foreach($arAllFeatures as $f => $value) {
            if (!array_key_exists($f, self::$arCpfMapValue["f"]) && $value || array_key_exists($f, self::$arCpfMapValue["f"]) && $value != self::$arCpfMapValue["f"][$f])
                $arFeatures[] = array($f, $value);
            self::$arCpfMapValue["f"][$f] = $value;
        }
        $cpf_map_value = serialize(self::$arCpfMapValue);
        $cpf_map_value = base64_encode($cpf_map_value);
        COption::SetOptionString("main", "~cpf_map_value", $cpf_map_value);
        self::$arCpfMapValue = false;
        foreach($arFeatures as $feature)
            self::ChangeFeatureSettings($feature[0], $feature[1]);
    }

    public static function SaveFeaturesSettings($_974564902, $_1932622630) {
        self::_init();
        $_1241254477 = array("e" => array(), "f" => array());
        if (!is_array($_974564902))
            $_974564902 = array();
        if (!is_array($_1932622630))
            $_1932622630 = array();
        if (!in_array("Portal", $_974564902))
            $_974564902[] = "Portal";
        foreach(self::$arBXFeatures as $key => $arAllFeatures) {
            if (array_key_exists($key, self::$arCpfMapValue["e"]))
                $arKeyMap = self::$arCpfMapValue["e"][$key];
            else
                $arKeyMap = ($key == "Portal") ? array("F") : array("X");
            if ($arKeyMap[0] == "F" || $arKeyMap[0] == "D") {
                $_1241254477["e"][$key] = $arKeyMap;
            } else {
                if (in_array($key, $_974564902))
                    $_1241254477["e"][$key] = array("D", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
                else $_1241254477["e"][$key] = array("X");
            }
        }
        $arFeatures = array();
        foreach(self::$xmlFeatures as $f => $key) {
            if ($_1241254477["e"][$key][0] != "F" && $_1241254477["e"][$key][0] != "D") {
                $_1241254477["f"][$f] = false;
            } else {
                if ($_1241254477["e"][$key][0] == "D" && $_1241254477["e"][$key][1] < mktime(0, 0, 0, date("m"), date("d") - self::$tryDaysCount, date("Y")))
                    $_1241254477["f"][$f] = false;
                else
                    $_1241254477["f"][$f] = in_array($f, $_1932622630);
                if (!array_key_exists($f, self::$arCpfMapValue["f"]) && $_1241254477["f"][$f] || array_key_exists($f, self::$arCpfMapValue["f"]) && $_1241254477["f"][$f] != self::$arCpfMapValue["f"][$f])
                    $arFeatures[] = array($f, $_1241254477["f"][$f]);
            }
        }
        $cpf_map_value = serialize($_1241254477);
        $cpf_map_value = base64_encode($cpf_map_value);
        COption::SetOptionString("main", "~cpf_map_value", $cpf_map_value);
        self::$arCpfMapValue = false;
        foreach($arFeatures as $feature)
            self::ChangeFeatureSettings($feature[0], $feature[1]);
    }

    public static function GetFeaturesList() {
        self::_init();
        $arFeaturesList = array();
        foreach(self::$arBXFeatures as $key => $arAllFeatures) {
            if (array_key_exists($key, self::$arCpfMapValue["e"]))
                $arKeyMap = self::$arCpfMapValue["e"][$key];
            else
                $arKeyMap = ($key == "Portal") ? array("F") : array("X");
            $arFeaturesList[$key] = array("TYPE" => $arKeyMap[0],
                "DATE" => $arKeyMap[1],
                "FEATURES" => array(),
            );
            $arFeaturesList[$key]["EXPIRED"] = false;
            if ($arFeaturesList[$key]["TYPE"] == "D") {
                $arFeaturesList[$key]["TRY_DAYS_COUNT"] = intval((time() - $arFeaturesList[$key]["DATE"])/86400);
                if ($arFeaturesList[$key]["TRY_DAYS_COUNT"] > self::$tryDaysCount)
                    $arFeaturesList[$key]["EXPIRED"] = true;
            }
            foreach($arAllFeatures as $f)
                $arFeaturesList[$key]["FEATURES"][$f] = (!array_key_exists($f, self::$arCpfMapValue["f"]) || self::$arCpfMapValue["f"][$f]);
        }
        return $arFeaturesList;
    }

    private static function DoInstall($module_id, $installFlag) {
        if (IsModuleInstalled($module_id) == $installFlag) return true;
        $install_php_path = $_SERVER["DOCUMENT_ROOT"]."/thurly/modules/".$module_id."/install/index.php";
        if (!file_exists($install_php_path)) return false;
        include_once($install_php_path);
        $module_class_name = str_replace(".", "_", $module_id);
        if (!class_exists($module_class_name)) return false;
        $module = new $module_class_name;
        if ($installFlag) {
            if (!$module->InstallDB()) return false;
            $module->InstallEvents();
            if (!$module->InstallFiles()) return false;
        } else {
            if (CModule::IncludeModule("search"))
                CSearch::DeleteIndex($module_id);
            UnRegisterModule($module_id);
        }
        return true;
    }

    protected static function OnRequestsSettingsChange($f, $installFlag) {
        self::DoInstall("form", $installFlag);
    }

    protected static function OnLearningSettingsChange($f, $installFlag) {
        self::DoInstall("learning", $installFlag);
    }

    protected static function OnJabberSettingsChange($f, $installFlag) {
        self::DoInstall("xmpp", $installFlag);
    }

    protected static function OnVideoConferenceSettingsChange($f, $installFlag) {
        self::DoInstall("video", $installFlag);
    }

    protected static function OnBizProcSettingsChange($f, $installFlag) {
        self::DoInstall("bizprocdesigner", $installFlag);
    }

    protected static function OnListsSettingsChange($f, $installFlag) {
        self::DoInstall("lists", $installFlag);
    }

    protected static function OnWikiSettingsChange($f, $installFlag) {
        self::DoInstall("wiki", $installFlag);
    }

    protected static function OnSupportSettingsChange($f, $installFlag) {
        self::DoInstall("support", $installFlag);
    }

    protected static function OnControllerSettingsChange($f, $installFlag) {
        self::DoInstall("controller", $installFlag);
    }

    protected static function OnAnalyticsSettingsChange($f, $installFlag) {
        self::DoInstall("statistic", $installFlag);
    }

    protected static function OnVoteSettingsChange($f, $installFlag) {
        self::DoInstall("vote", $installFlag);
    }

    protected static function OnFriendsSettingsChange($f, $installFlag) {
        if ($installFlag) $allow_flag = "Y";
        else $allow_flag = "N";
        $rsSite = CSite::GetList(($by = ""), ($order = ""), array("ACTIVE" => "Y"));
        while ($arSite = $rsSite->Fetch()) {
            if (COption::GetOptionString("socialnetwork", "allow_frields", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_frields", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_frields", $allow_flag);
            }
        }
    }

    protected static function OnMicroBlogSettingsChange($f, $installFlag) {
        if ($installFlag) $allow_flag = "Y";
        else $allow_flag = "N";
        $rsSite = CSite::GetList(($by = ""), ($order = ""), array("ACTIVE" => "Y"));
        while ($arSite = $rsSite->Fetch()) {
            if (COption::GetOptionString("socialnetwork", "allow_microblog_user", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_microblog_user", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_microblog_user", $allow_flag);
            }
            if (COption::GetOptionString("socialnetwork", "allow_microblog_group", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_microblog_group", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_microblog_group", $allow_flag);
            }
        }
    }

    protected static function OnPersonalFilesSettingsChange($f, $installFlag) {
        if ($installFlag) $allow_flag = "Y";
        else $allow_flag = "N";
        $rsSite = CSite::GetList(($by = ""), ($order = ""), array("ACTIVE" => "Y"));
        while ($arSite = $rsSite->Fetch()) {
            if (COption::GetOptionString("socialnetwork", "allow_files_user", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_files_user", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_files_user", $allow_flag);
            }
        }
    }

    protected static function OnPersonalBlogSettingsChange($f, $installFlag) {
        if ($installFlag) $allow_flag = "Y";
        else $allow_flag = "N";
        $rsSite = CSite::GetList(($by = ""), ($order = ""), array("ACTIVE" => "Y"));
        while ($arSite = $rsSite->Fetch()) {
            if (COption::GetOptionString("socialnetwork", "allow_blog_user", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_blog_user", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_blog_user", $allow_flag);
            }
        }
    }

    protected static function OnPersonalPhotoSettingsChange($f, $installFlag) {
        if ($installFlag) $allow_flag = "Y";
        else $allow_flag = "N";
        $rsSite = CSite::GetList(($by = ""), ($order = ""), array("ACTIVE" => "Y"));
        while ($arSite = $rsSite->Fetch()) {
            if (COption::GetOptionString("socialnetwork", "allow_photo_user", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_photo_user", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_photo_user", $allow_flag);
            }
        }
    }

    protected static function OnPersonalForumSettingsChange($f, $installFlag) {
        if ($installFlag) $allow_flag = "Y";
        else $allow_flag = "N";
        $rsSite = CSite::GetList(($by = ""), ($order = ""), array("ACTIVE" => "Y"));
        while ($arSite = $rsSite->Fetch()) {
            if (COption::GetOptionString("socialnetwork", "allow_forum_user", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_forum_user", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_forum_user", $allow_flag);
            }
        }
    }

    protected static function OnTasksSettingsChange($f, $installFlag) {
        if ($installFlag) $allow_flag = "Y";
        else $allow_flag = "N";
        $rsSite = CSite::GetList(($by = ""), ($order = ""), array("ACTIVE" => "Y"));
        while ($arSite = $rsSite->Fetch()) {
            if (COption::GetOptionString("socialnetwork", "allow_tasks_user", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_tasks_user", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_tasks_user", $allow_flag);
            }
            if (COption::GetOptionString("socialnetwork", "allow_tasks_group", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_tasks_group", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_tasks_group", $allow_flag);
            }
        }
        self::DoInstall("tasks", $installFlag);
    }

    protected static function OnCalendarSettingsChange($f, $installFlag) {
        if ($installFlag) $allow_flag = "Y";
        else $allow_flag = "N";
        $rsSite = CSite::GetList(($by = ""), ($order = ""), array("ACTIVE" => "Y"));
        while ($arSite = $rsSite->Fetch()) {
            if (COption::GetOptionString("socialnetwork", "allow_calendar_user", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_calendar_user", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_calendar_user", $allow_flag);
            }
            if (COption::GetOptionString("socialnetwork", "allow_calendar_group", "Y", $arSite["ID"]) != $allow_flag) {
                COption::SetOptionString("socialnetwork", "allow_calendar_group", $allow_flag, false, $arSite["ID"]);
                COption::SetOptionString("socialnetwork", "allow_calendar_group", $allow_flag);
            }
        }
    }

    protected static function OnSMTPSettingsChange($f, $installFlag) {
        self::DoInstall("mail", $installFlag);
    }

    protected static function OnExtranetSettingsChange($f, $installFlag) {
        $extranet_site = COption::GetOptionString("extranet", "extranet_site", "");
        if ($extranet_site) {
            $SITE = new CSite;
            $SITE->Update($extranet_site, array("ACTIVE" => ($installFlag ? "Y" : "N")));
        }
        self::DoInstall("extranet", $installFlag);
    }

    protected static function OnDAVSettingsChange($f, $installFlag) {
        self::DoInstall("dav", $installFlag);
    }

    protected static function OntimemanSettingsChange($f, $installFlag) {
        self::DoInstall("timeman", $installFlag);
    }

    protected static function Onintranet_sharepointSettingsChange($f, $installFlag) {
        if ($installFlag) {
            RegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem");
            RegisterModuleDependences("iblock", "OnAfterIBlockElementUpdate", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem");
            CAgent::AddAgent("CIntranetSharepoint::AgentLists();", "intranet", "N", 500);
            CAgent::AddAgent("CIntranetSharepoint::AgentQueue();", "intranet", "N", 300);
            CAgent::AddAgent("CIntranetSharepoint::AgentUpdate();", "intranet", "N", 3600);
        } else {
            UnRegisterModuleDependences("iblock", "OnAfterIBlockElementAdd", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem");
            UnRegisterModuleDependences("iblock", "OnAfterIBlockElementUpdate", "intranet", "CIntranetEventHandlers", "SPRegisterUpdatedItem");
            CAgent::RemoveAgent("CIntranetSharepoint::AgentLists();", "intranet");
            CAgent::RemoveAgent("CIntranetSharepoint::AgentQueue();", "intranet");
            CAgent::RemoveAgent("CIntranetSharepoint::AgentUpdate();", "intranet");
        }
    }

    protected static function OncrmSettingsChange($f, $installFlag) {
        if ($installFlag) COption::SetOptionString("crm", "form_features", "Y");
        self::DoInstall("crm", $installFlag);
    }

    protected static function OnClusterSettingsChange($f, $installFlag) {
        self::DoInstall("cluster", $installFlag);
    }

    protected static function OnMultiSitesSettingsChange($f, $installFlag) {
        if ($installFlag) RegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", 100, "/modules/intranet/panel_button.php");
        else UnRegisterModuleDependences("main", "OnBeforeProlog", "main", "CWizardSolPanelIntranet", "ShowPanel", "/modules/intranet/panel_button.php");
    }

    protected static function OnIdeaSettingsChange($f, $installFlag) {
        self::DoInstall("idea", $installFlag);
    }

    protected static function OnMeetingSettingsChange($f, $installFlag) {
        self::DoInstall("meeting", $installFlag);
    }

    protected static function OnXDImportSettingsChange($f, $installFlag) {
        self::DoInstall("xdimport", $installFlag);
    }
}


// Check Expired Date if the version is DEMO version.

$_msg_expire = GetMessage("expire_mess2");
$days_after_trial = 14;
define("DEMO", "Y");
$_end_time = 1;
unset($_admin_pwd_h);
$_site_expire_date = sprintf( "%010s", "EEXPIR");
$_admin_pwd_h = COption::GetOptionString("main", "admin_passwordh");


while ($_admin_pwd_h) {
    $_1505049562 = "H4u67fhw87Vhytos";
    $_396573762 = base64_decode($_admin_pwd_h);
    $_1197332044 = "";
    $_1505049562 = substr("thR".$_1505049562, 0, -5)."7Hyr12Hwy0rFr";
    $_1208740567 = strlen($_1505049562);
    $_pos = 0;
    for ($_i = 0; $_i < strlen($_396573762); $_i++) {
        $_1197332044 .= chr(ord($_396573762[$_i]) ^ ord($_1505049562[$_pos]));
        if ($_pos == $_1208740567 - 1) $_pos = 0;
        else $_pos = $_pos + 1;
    }
    $_end_time = mktime(0, 0, 0, intval($_1197332044[6].$_1197332044[3]), intval($_1197332044[1].$_1197332044[14]), intval($_1197332044[10].$_1197332044[18].$_1197332044[7].$_1197332044[12]));
    unset($_1505049562);
    break;
}

$_str_biz = "T_STEAL";
$url_update = "http://thurlysoft.com/thurly/bs.php";
$_old_site_expire_date = "OLDSITEEXPIREDATE";

@include($_SERVER["DOCUMENT_ROOT"]."/"."thurly/modules/main/admin/define.php");
$_expire_end_time = 2;
while (defined("TEMPORARY_CACHE")) {
    $_temporary_cache = base64_decode(constant("TEMPORARY_CACHE"));

    $_str_date_enc = "";
    //$_str_biz = strrev("ON_OD").sprintf( "%s%s", $_str_biz, "_OUR_BUS");
    $_str_biz = "DO_NOT_STEAL_OUR_BUS";
    $_str_biz_len = strlen($_str_biz);
    $_pos = 0;
    for ($_i = 0; $_i < strlen($_temporary_cache); $_i++) {
        $_str_date_enc .= chr(ord($_temporary_cache[$_i]) ^ ord($_str_biz[$_pos]));
        if ($_pos == $_str_biz_len - 1) $_pos = 0;
        else $_pos = $_pos + 1;
    }
    
    $_expire_end_time = mktime(0, 0, 0, intval($_str_date_enc[6].$_str_date_enc[16]), intval($_str_date_enc[9].$_str_date_enc[2]), intval($_str_date_enc[12].$_str_date_enc[7].$_str_date_enc[14].$_str_date_enc[3]));
    unset($_str_biz);
    break;
}

$_expire_end_time = mktime(0, 0, 0, date("m"), date("d") + 30, date("Y"));
$_site_expire_date = "SITEEXPIEDATE";
while (!defined("DEMO")) {
    function __f($_1379103819) {
        return $_1379103819 + __f($_1379103819);
    }
    __f(1);
};
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/thurly/"."config.php")) {

    $bxProductConfig = array();
    include($_SERVER["DOCUMENT_ROOT"]."/thurly/"."config.php");
    if (isset($bxProductConfig["saas"]["days_after_trial"])) {
        $_days_after_trial = intval($bxProductConfig["saas"]["days_after_trial"]);

        if ($_days_after_trial >= 0 && $_days_after_trial < 14)
            $days_after_trial = $_days_after_trial;
    }
    if ($bxProductConfig["saas"]["trial_stopped"] <>"")
        $_msg_expire = $bxProductConfig["saas"]["trial_stopped"];
}
/*
for ($_i = 0, $_569456661 = (time() < mktime(0, 0, 0, 5, 1, 2010) || $_end_time <= 10), $_424981453 = ($_end_time < mktime(0, 0, 0, date("m"), date("d") - $days_after_trial, date("Y"))); $_i < 10, $_569456661 || $_424981453 || $_end_time != $_expire_end_time; $_i++, WriteFinalMessage($_msg_expire));
*/
define($_old_site_expire_date, $_expire_end_time);
define("SITEEXPIREDATE", $_expire_end_time);
$GLOBALS["SiteExpireDate"] = OLDSITEEXPIREDATE;
$GLOBALS["arCustomTemplateEngines"] = array();
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/urlrewriter.php");
\Thurly\Main\Loader::registerAutoLoadClasses("main", 
    array("CSiteTemplate" => "classes/general/site_template.php",
        "CThurlyComponent" => "classes/general/component.php",
        "CComponentEngine" => "classes/general/component_engine.php",
        "CComponentAjax" => "classes/general/component_ajax.php",
        "CThurlyComponentTemplate" => "classes/general/component_template.php",
        "CComponentUtil" => "classes/general/component_util.php",
        "CControllerClient" => "classes/general/controller_member.php",
        "PHPParser" => "classes/general/php_parser.php",
        "CDiskQuota" => "classes/".$DBType."/quota.php",
        "CEventLog" => "classes/general/event_log.php",
        "CEventMain" => "classes/general/event_log.php",
        "CAdminFileDialog" => "classes/general/file_dialog.php",
        "WLL_User" => "classes/general/liveid.php",
        "WLL_ConsentToken" => "classes/general/liveid.php",
        "WindowsLiveLogin" => "classes/general/liveid.php",
        "CAllFile" => "classes/general/file.php",
        "CFile" => "classes/".$DBType."/file.php",
        "CTempFile" => "classes/general/file_temp.php",
        "CFavorites" => "classes/".$DBType."/favorites.php",
        "CUserOptions" => "classes/general/user_options.php",
        "CGridOptions" => "classes/general/grids.php",
        "CUndo" => "/classes/general/undo.php",
        "CAutoSave" => "/classes/general/undo.php",
        "CRatings" => "classes/".$DBType."/ratings.php",
        "CRatingsComponentsMain" => "classes/".$DBType."/ratings_components.php",
        "CRatingRule" => "classes/general/rating_rule.php",
        "CRatingRulesMain" => "classes/".$DBType."/rating_rules.php",
        "CTopPanel" => "public/top_panel.php",
        "CEditArea" => "public/edit_area.php",
        "CComponentPanel" => "public/edit_area.php",
        "CTextParser" => "classes/general/textparser.php",
        "CPHPCacheFiles" => "classes/general/cache_files.php",
        "CDataXML" => "classes/general/xml.php",
        "CXMLFileStream" => "classes/general/xml.php",
        "CRsaProvider" => "classes/general/rsasecurity.php",
        "CRsaSecurity" => "classes/general/rsasecurity.php",
        "CRsaBcmathProvider" => "classes/general/rsabcmath.php",
        "CRsaOpensslProvider" => "classes/general/rsaopenssl.php",
        "CASNReader" => "classes/general/asn.php",
        "CBXShortUri" => "classes/".$DBType."/short_uri.php",
        "CFinder" => "classes/general/finder.php",
        "CAccess" => "classes/general/access.php",
        "CAuthProvider" => "classes/general/authproviders.php",
        "IProviderInterface" => "classes/general/authproviders.php",
        "CGroupAuthProvider" => "classes/general/authproviders.php",
        "CUserAuthProvider" => "classes/general/authproviders.php",
        "CTableSchema" => "classes/general/table_schema.php",
        "CCSVData" => "classes/general/csv_data.php",
        "CSmile" => "classes/general/smile.php",
        "CSmileGallery" => "classes/general/smile.php",
        "CSmileSet" => "classes/general/smile.php",
        "CGlobalCounter" => "classes/general/global_counter.php",
        "CUserCounter" => "classes/".$DBType."/user_counter.php",
        "CUserCounterPage" => "classes/".$DBType."/user_counter.php",
        "CHotKeys" => "classes/general/hot_keys.php",
        "CHotKeysCode" => "classes/general/hot_keys.php",
        "CBXSanitizer" => "classes/general/sanitizer.php",
        "CBXArchive" => "classes/general/archive.php",
        "CAdminNotify" => "classes/general/admin_notify.php",
        "CBXFavAdmMenu" => "classes/general/favorites.php",
        "CAdminInformer" => "classes/general/admin_informer.php",
        "CSiteCheckerTest" => "classes/general/site_checker.php",
        "CSqlUtil" => "classes/general/sql_util.php",
        "CFileUploader" => "classes/general/uploader.php",
        "LPA" => "classes/general/lpa.php",
        "CAdminFilter" => "interface/admin_filter.php",
        "CAdminList" => "interface/admin_list.php",
        "CAdminUiList" => "interface/admin_ui_list.php",
        "CAdminUiResult" => "interface/admin_ui_list.php",
        "CAdminUiContextMenu" => "interface/admin_ui_list.php",
        "CAdminListRow" => "interface/admin_list.php",
        "CAdminTabControl" => "interface/admin_tabcontrol.php",
        "CAdminForm" => "interface/admin_form.php",
        "CAdminFormSettings" => "interface/admin_form.php",
        "CAdminTabControlDrag" => "interface/admin_tabcontrol_drag.php",
        "CAdminDraggableBlockEngine" => "interface/admin_tabcontrol_drag.php",
        "CJSPopup" => "interface/jspopup.php",
        "CJSPopupOnPage" => "interface/jspopup.php",
        "CAdminCalendar" => "interface/admin_calendar.php",
        "CAdminViewTabControl" => "interface/admin_viewtabcontrol.php",
        "CAdminTabEngine" => "interface/admin_tabengine.php",
        "CHTMLPagesCache" => "lib/composite/helper.php",
        "StaticHtmlMemcachedResponse" => "lib/composite/responder.php",
        "StaticHtmlFileResponse" => "lib/composite/responder.php",
        "Thurly\Main\Page\Frame" => "lib/composite/engine.php",
        "Thurly\Main\Page\FrameStatic" => "lib/composite/staticarea.php",
        "Thurly\Main\Page\FrameBuffered" => "lib/composite/bufferarea.php",
        "Thurly\Main\Page\FrameHelper" => "lib/composite/bufferarea.php",
        "Thurly\Main\Data\StaticHtmlCache" => "lib/composite/page.php",
        "Thurly\Main\Data\StaticHtmlStorage" => "lib/composite/data/abstractstorage.php",
        "Thurly\Main\Data\StaticHtmlFileStorage" => "lib/composite/data/filestorage.php",
        "Thurly\Main\Data\StaticHtmlMemcachedStorage" => "lib/composite/data/memcachedstorage.php",
        "Thurly\Main\Data\StaticCacheProvider" => "lib/composite/data/cacheprovider.php",
        "Thurly\Main\Data\AppCacheManifest" => "lib/composite/appcache.php",
    )
);
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/agent.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/user.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/event.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/menu.php");
AddEventHandler("main", "OnAfterEpilog", array("\Thurly\Main\Data\ManagedCache", "finalize"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".$DBType."/usertype.php");
if (file_exists(($_update_db_updater = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/update_db_updater.php"))) {
    $US_HOST_PROCESS_MAIN = False;
    include($_update_db_updater);
}
if (file_exists(($_update_db_updater = $_SERVER["DOCUMENT_ROOT"]."/thurly/init.php")))
    include_once($_update_db_updater);
if (($_update_db_updater = getLocalPath("php_interface/init.php", BX_PERSONAL_ROOT)) !== false)
    include_once($_SERVER["DOCUMENT_ROOT"].$_update_db_updater);
if (($_update_db_updater = getLocalPath("php_interface/".SITE_ID."/init.php", BX_PERSONAL_ROOT)) !== false)
    include_once($_SERVER["DOCUMENT_ROOT"].$_update_db_updater);
if (!defined("BX_FILE_PERMISSIONS")) define("BX_FILE_PERMISSIONS", 420);
if (!defined("BX_DIR_PERMISSIONS")) define("BX_DIR_PERMISSIONS", 493);

$GLOBALS["sDocPath"] = $APPLICATION->GetCurPage();
if ((!(defined("STATISTIC_ONLY") && STATISTIC_ONLY && substr($APPLICATION->GetCurPage(), 0, strlen(BX_ROOT."/admin/")) != BX_ROOT."/admin/")) && COption::GetOptionString("main", "include_charset", "Y") == "Y" && strlen(LANG_CHARSET) > 0)
    header("Content-Type: text/html; charset=".LANG_CHARSET);

if (COption::GetOptionString("main", "set_p3p_header", "Y") == "Y")
    header('P3P: policyref="/thurly/p3p.xml", CP = "NON DSP COR CUR ADM DEV PSA PSD OUR UNR BUS UNI COM NAV INT DEM STA"');
header("X-Powered-CMS: Thurly Site Manager(".(LICENSE_KEY == "DEMO" ? "DEMO" : md5(THURLY.LICENSE_KEY.LICENCE)).")");
if (COption::GetOptionString("main", "update_devsrv", "") == "Y") header("X-DevSrv-CMS: Thurly");
define("BX_CRONTAB_SUPPORT", defined("BX_CRONTAB"));
if (COption::GetOptionString("main", "check_agents", "Y") == "Y") {
    define("START_EXEC_AGENTS_1", microtime());
    $BX_STATE = "AG";
    $DB->StartUsingMasterOnly();
    CAgent::CheckAgents();
    $DB->StopUsingMasterOnly();
    define("START_EXEC_AGENTS_2", microtime());
    $BX_STATE = "PB";
}
ini_set("session.cookie_httponly", 1);
if ($_1725823741 = $APPLICATION->GetCookieDomain())
    ini_set("session.cookie_domain", $_1725823741);

if (COption::GetOptionString("security", "session", "N") === "Y" && 
    CModule::IncludeModule("security")){
    CSecuritySession::Init();
}

session_start();
foreach(GetModuleEvents("main", "OnPageStart", true) as $event)
    ExecuteModuleEventEx($event);
$USER = new CUser;
$userSecurityPolicy = $USER->GetSecurityPolicy();
$current_time = time();
if (($_SESSION["SESS_IP"] && strlen($userSecurityPolicy["SESSION_IP_MASK"]) > 0 && ((ip2long($userSecurityPolicy["SESSION_IP_MASK"]) & ip2long($_SESSION["SESS_IP"])) != (ip2long($userSecurityPolicy["SESSION_IP_MASK"]) & ip2long($_SERVER["REMOTE_ADDR"])))) || ($userSecurityPolicy["SESSION_TIMEOUT"] > 0 && $_SESSION["SESS_TIME"] > 0 && $current_time - $userSecurityPolicy["SESSION_TIMEOUT"] * 60 > $_SESSION["SESS_TIME"]) || (isset($_SESSION["BX_SESSION_TERMINATE_TIME"]) && $_SESSION["BX_SESSION_TERMINATE_TIME"] > 0 && $current_time > $_SESSION["BX_SESSION_TERMINATE_TIME"]) || (isset($_SESSION["BX_SESSION_SIGN"]) && $_SESSION["BX_SESSION_SIGN"] <> thurly_sess_sign()) || (isSessionExpired())) {
    $_SESSION = array();
    @session_destroy();
    if (COption::GetOptionString("security", "session", "N") === "Y" && CModule::IncludeModule("security")) CSecuritySession::Init();
    session_id(md5(uniqid(rand(), true)));
    session_start();
    $USER = new CUser;
}
$_SESSION["SESS_IP"] = $_SERVER["REMOTE_ADDR"];
$_SESSION["SESS_TIME"] = time();
if (!isset($_SESSION["BX_SESSION_SIGN"]))
    $_SESSION["BX_SESSION_SIGN"] = thurly_sess_sign();
if ((COption::GetOptionString("main", "use_session_id_ttl", "N") == "Y") && (COption::GetOptionInt("main", "session_id_ttl", 0) > 0) && !defined("BX_SESSION_ID_CHANGE")) {
    if (!array_key_exists("SESS_ID_TIME", $_SESSION)) {
        $_SESSION["SESS_ID_TIME"] = $_SESSION["SESS_TIME"];
    } else if(($_SESSION["SESS_ID_TIME"] + COption::GetOptionInt("main", "session_id_ttl")) < $_SESSION["SESS_TIME"]) {
        if (COption::GetOptionString("security", "session", "N") === "Y" && CModule::IncludeModule("security")) {
            CSecuritySession::UpdateSessID();
        } else {
            session_regenerate_id();
        }
        $_SESSION["SESS_ID_TIME"] = $_SESSION["SESS_TIME"];
    }
}
define("BX_STARTED", true);
if (isset($_SESSION["BX_ADMIN_LOAD_AUTH"])) {
    define("ADMIN_SECTION_LOAD_AUTH", 1);
    unset($_SESSION["BX_ADMIN_LOAD_AUTH"]);
}
if (!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS !== true) {
    $logout_request = isset($_REQUEST["logout"]) && (strtolower($_REQUEST["logout"]) == yes);
    if ($logout_request && $USER->IsAuthorized()) {
        $USER->Logout();
        LocalRedirect($APPLICATION->GetCurPageParam("", array("logout")));
    }
    if (!$USER->IsAuthorized()) {
        $USER->LoginByCookies();
    }
    $_user_auth = false;
    if (($user_auth = $USER->LoginByHttpAuth()) !== null) {
        $_user_auth = $user_auth;
        $APPLICATION->SetAuthResult($_user_auth);
    }
    if (isset($_REQUEST["AUTH_FORM"]) && $_REQUEST["AUTH_FORM"] <> "") {
        $_1463970877 = false;
        if (COption::GetOptionString("main", "use_encrypted_auth", "N") == "Y") {
            $rsa_security = new CRsaSecurity();
            if (($rsa_key = $rsa_security->LoadKeys())) {
                $rsa_security->SetKeys($rsa_key);
                $_sess_check = $rsa_security->AcceptFromForm(array("USER_PASSWORD", "USER_CONFIRM_PASSWORD"));
                if ($_sess_check == CRsaSecurity::ERROR_SESS_CHECK)
                    $_user_auth = array("MESSAGE" => GetMessage("main_include_decode_pass_sess"), "TYPE" => "ERROR");
                else if($_sess_check < 0)
                    $_user_auth = array("MESSAGE" => GetMessage("main_include_decode_pass_err", array("#ERRCODE#" => $_sess_check)), "TYPE" => "ERROR");
                if ($_sess_check < 0) $_1463970877 = true;
            }
        }
        if ($_1463970877 == false) {
            if (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true) $_725812785 = LANG;
            else $_725812785 = false;
            if ($_REQUEST["TYPE"] == "AUTH") {
                $_user_auth = $USER->Login($_REQUEST["USER_LOGIN"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_REMEMBER"]);
            }
            elseif($_REQUEST["TYPE"] == "OTP") {
                $_user_auth = $USER->LoginByOtp($_REQUEST["USER_OTP"], $_REQUEST["OTP_REMEMBER"], $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
            }
            elseif($_REQUEST["TYPE"] == "SEND_PWD") {
                $_user_auth = CUser::SendPassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_EMAIL"], $_725812785, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
            }
            elseif($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["TYPE"] == "CHANGE_PWD") {
                $_user_auth = $USER->ChangePassword($_REQUEST["USER_LOGIN"], $_REQUEST["USER_CHECKWORD"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $_725812785, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
            }
            elseif(COption::GetOptionString("main", "new_user_registration", "N") == "Y" && $_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["TYPE"] == "REGISTRATION" && (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true)) {
                $_user_auth = $USER->Register($_REQUEST["USER_LOGIN"], $_REQUEST["USER_NAME"], $_REQUEST["USER_LAST_NAME"], $_REQUEST["USER_PASSWORD"], $_REQUEST["USER_CONFIRM_PASSWORD"], $_REQUEST["USER_EMAIL"], $_725812785, $_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]);
            }
            if ($_REQUEST["TYPE"] == "AUTH" || $_REQUEST["TYPE"] == "OTP") {
                if ($_user_auth === true && defined("ADMIN_SECTION") && ADMIN_SECTION === true) {
                    $APPLICATION->StoreCookies();
                    $_SESSION["BX_ADMIN_LOAD_AUTH"] = true;
                    echo "<script type='text/javascript'> window.onload = function() {
                                            top.BX.AUTHAGENT.setAuthResult(false);
                                        }; </script>";
                    die();
                }
            }
        }
        $APPLICATION->SetAuthResult($_user_auth);
    }
    elseif(!$USER->IsAuthorized()) {
        $USER->LoginHitByHash();
    }
}

$USER->CheckAuthActions();
if (($application_id = $USER->GetParam("APPLICATION_ID")) !== null) {
    $instance = \Thurly\Main\Authentication\ApplicationManager::getInstance();
    if ($instance->checkScope($application_id) !== true) {
        $scope_error_event = new\Thurly\Main\Event("main", "onApplicationScopeError", Array("APPLICATION_ID" => $application_id));
        $scope_error_event->send();
        CHTTP::SetStatus("403 Forbidden");
        die();
    }
}

if (!defined("ADMIN_SECTION") || ADMIN_SECTION !== true) {
    $site_template_id = "";
    if (is_string($_REQUEST["thurly_preview_site_template"]) && $_REQUEST["thurly_preview_site_template"] <> "" && $USER->CanDoOperation("view_other_settings")) {
        $security_signer = new Thurly\Main\Security\Sign\Signer();
        try {
            $_1072567021 = $security_signer->unsign($_REQUEST["thurly_preview_site_template"], "template_preview".thurly_sessid());
            $_1222077316 = CSiteTemplate::GetByID($_1072567021);
            if ($_2072535194 = $_1222077316->Fetch()) {
                $site_template_id = $_2072535194["ID"];
                if (isset($_GET["bx_template_preview_mode"]) && $_GET["bx_template_preview_mode"] == "Y" && $USER->CanDoOperation("edit_other_settings")) {
                    define("SITE_TEMPLATE_PREVIEW_MODE", true);
                }
            }
        } catch (\Thurly\Main\Security\Sign\BadSignatureException $_544998809) {}
    }
    if ($site_template_id == "") {
        $site_template_id = CSite::GetCurTemplate();
    }
    define("SITE_TEMPLATE_ID", $site_template_id);
    define("SITE_TEMPLATE_PATH", getLocalPath("templates/".SITE_TEMPLATE_ID, BX_PERSONAL_ROOT));
}
if (isset($_GET["show_page_exec_time"])) {
    if ($_GET["show_page_exec_time"] == "Y" || $_GET["show_page_exec_time"] == "N") $_SESSION["SESS_SHOW_TIME_EXEC"] = $_GET["show_page_exec_time"];
}
if (isset($_GET["show_include_exec_time"])) {
    if ($_GET["show_include_exec_time"] == "Y" || $_GET["show_include_exec_time"] == "N") $_SESSION["SESS_SHOW_INCLUDE_TIME_EXEC"] = $_GET["show_include_exec_time"];
}
if (isset($_GET["thurly_include_areas"]) && $_GET["thurly_include_areas"] <> "") $APPLICATION->SetShowIncludeAreas($_GET["thurly_include_areas"] == "Y");
if ($USER->IsAuthorized()) {
    $_199052949 = COption::GetOptionString("main", "cookie_name", THURLY_SM);
    if (!isset($_COOKIE[$_199052949."_SOUND_LOGIN_PLAYED"])) $APPLICATION->set_cookie("SOUND_LOGIN_PLAYED", "Y", 0);
}
\Thurly\Main\Composite\Engine::shouldBeEnabled();
if (defined("BX_CHECK_SHORT_URI") && BX_CHECK_SHORT_URI && CBXShortUri::CheckUri()) {
    die();
}
foreach(GetModuleEvents("main", "OnBeforeProlog", true) as $event)
    ExecuteModuleEventEx($event);
if ((!defined("NOT_CHECK_PERMISSIONS") || NOT_CHECK_PERMISSIONS !== true) && (!defined("NOT_CHECK_FILE_PERMISSIONS") || NOT_CHECK_FILE_PERMISSIONS !== true)) {
    $_1996776951 = $request->getScriptFile();
    if (!$USER->CanDoFileOperation("fm_view_file", array(SITE_ID, $_1996776951)) || (defined("NEED_AUTH") && NEED_AUTH && !$USER->IsAuthorized())) {
        if ($USER->IsAuthorized() && $_user_auth["MESSAGE"] == "")
            $_user_auth = array("MESSAGE" => GetMessage("ACCESS_DENIED")."".GetMessage("ACCESS_DENIED_FILE", array("#FILE#" => $_1996776951)), "TYPE" => "ERROR");
        if (defined("ADMIN_SECTION") && ADMIN_SECTION == true) {
            if ($_REQUEST["mode"] == "list" || $_REQUEST["mode"] == "settings") {
                echo '<script> top.location = '.$APPLICATION->GetCurPage()."?".DeleteParam(array("mode")).'; </script>';
                die();
            }
            elseif($_REQUEST["mode"] == "frame") {
                echo '<script type="text/javascript">
                        var w = (opener ? opener.window : parent.window);
                        w.location.href = '.$APPLICATION->GetCurPage()."?".DeleteParam(array("mode")).';
                      </script>';
                die();
            }
            elseif(defined("MOBILE_APP_ADMIN") && MOBILE_APP_ADMIN == true) {
                echo json_encode(Array("status" => "failed"));
                die();
            }
        }
        $APPLICATION->AuthForm($_user_auth);
    }
}
if (mt_rand(1, 20) == 7) {
    $query = $DB->Query("SELECT VALUE FROM b_option WHERE NAME = '~PARAM_MAX_USERS'
            AND MODULE_ID = 'main'
            AND SITE_ID IS NULL", true);
    if ($row = $query->Fetch()) {
        $value = $row["VALUE"];
        list($max_users, $salt) = explode(".", $value);
        $_1787515856 = pack("H*", $max_users);
        $_license = "thurly".md5(constant('LICENSE_KEY'));
        $hash_code = hash_hmac("sha256", $salt, $_license, true);
        if (strcmp($hash_code, $_1787515856) !== 0) {
            if (isset($USER) && is_object($USER) && $USER->IsAuthorized() && !$USER->IsAdmin()) {
                $APPLICATION->RestartBuffer();
                LocalRedirect("/licence_restriction.php", true);
            }
        }
    } else {
        \Thurly\Main\Config\Option::set('main', 'PARAM_MAX_USERS', 1000);
    }
}

while (!defined("OLDSITEEXPIREDATE") || strlen(OLDSITEEXPIREDATE) <= 0 || OLDSITEEXPIREDATE != SITEEXPIREDATE)
    die(GetMessage("expire_mess2"));
if (isset($_1881802773) && $_1881802773 == 404) {
    if (COption::GetOptionString("main", "header_200", "N") == "Y") CHTTP::SetStatus("200 OK");
} 
?>