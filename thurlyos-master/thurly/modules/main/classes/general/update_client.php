<?
define('UPDATE_SYSTEM_VERSION_A', '17.5.0');
if (!defined('BX_DIR_PERMISSIONS')) define('BX_DIR_PERMISSIONS', 0);

define('DEFAULT_UPDATE_SERVER', 'mysql.smn');

IncludeModuleLangFile(__FILE__);

if (!function_exists('file_get_contents'))
{
    function file_get_contents($filename)
    {
        $fd = fopen("$filename", "rb");
        $content = fread($fd, filesize($filename));
        fclose($fd);
        return $content;
    }
}
if (extension_loaded('zlib'))
{
    if (!function_exists('gzopen') && function_exists('gzopen64'))
    {
        function gzopen($_path, $_2102989199, $_394066553 = 0)
        {
            return gzopen64($_path, $_2102989199, $_394066553);
        }
    }
}
if (!function_exists('htmlspecialcharsbx'))
{
    function htmlspecialcharsbx($_string, $_flag = ENT_COMPAT)
    {
        return htmlspecialchars($_string, $_flag, (defined('BX_UTF') ? 'UTF-8' : 'ISO-8859-1'));
    }
}
if (!function_exists('bx_accelerator_reset'))
{
    function bx_accelerator_reset()
    {
        if (function_exists('accelerator_reset')) accelerator_reset();
        elseif (function_exists('wincache_refresh_if_changed')) wincache_refresh_if_changed();
    }
}
if (!defined('US_SHARED_KERNEL_PATH')) define('US_SHARED_KERNEL_PATH', '/thurly');
if (!defined('US_CALL_TYPE')) define('US_CALL_TYPE', 'ALL');
if (!defined('US_BASE_MODULE')) define('US_BASE_MODULE', 'main');

$GLOBALS['UPDATE_STRONG_UPDATE_CHECK'] = '';
$GLOBALS['CACHE4UPDATESYS_LICENSE_KEY'] = '';

require_once ($_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/main/classes/general/update_class.php');

class CUpdateClient
{
    public static function executeCounters($_arr_data)
    {
        CUpdateClient::AddMessage2Log("exec CUpdateClient::executeCounters");
        $_cur_time = CUpdateClient::getmicrotime();
        if (empty($_arr_data)) return false;
        $strError_tmp = '';
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp);
        if (empty($_str_collect_data) && empty($strError_tmp)) 
            $strError_tmp = '[RV01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        if (empty($strError_tmp))
        {
            $_str_collect_data .= '&query_type=counter';
            foreach ($_arr_data as $_data)
            {
                try
                {
                    $_str_result = eval($_data["#"]["cdata-section"][0]["#"]);
                }
                catch(Exception $_e)
                {
                    $_str_result = '[' . $_e->getCode() . ']' . $_e->getMessage();
                }
                $_str_collect_data .= '&cntr_result[' . intval($_data['@']['ID']) . ']=' . urlencode($_str_result);
            }
        }
        if (empty($strError_tmp))
        {
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_content = CUpdateClient::GetHTTPPage('ACTIV', $_str_collect_data, $strError_tmp);
            if (empty($_content) && empty($strError_tmp)) 
                $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
        }
        CUpdateClient::AddMessage2Log('TIME executeCounters' . round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        if (!empty($strError_tmp))
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'EC');
            return false;
        }
        else return true;
    }
    public static function getNewLicenseSignedKey()
    {
        $_str_key = "~new_license17_5_sign";
        return $_str_key;
    }
    public static function getModuleValue($_module_id, $_module_name, $_default_value = "")
    {
        global $DB;
        $_rows = $DB->Query('SELECT VALUE FROM b_option WHERE SITE_ID IS NULL AND MODULE_ID = ' . $DB->ForSql($_module_id) . ' AND NAME = ' . $DB->ForSql($_module_name));
        if ($_row = $_rows->Fetch()) return $_row['VALUE'];
        return $_default_value;
    }
    public static function Lock()
    {
        global $DB, $APPLICATION;
        $_server_uniq_id = $APPLICATION->GetServerUniqID();
        if ($DB->type == 'MYSQL')
        {
            $_query = $DB->Query("SELECT GET_LOCK('" . $DB->ForSql($_server_uniq_id) . "_UpdateSystem', 0) as L", false, " File: " . __FILE__ . 'Line: ' . __LINE__);
            $_row = $_query->Fetch();
            if ($_row['L'] == 1) return true;
            else return false;
        }
        elseif ($DB->type == 'ORACLE')
        {
            return true;
        }
        else
        {
            $_i = 0;
            $DB->Query("DELETE FROM B_OPTION WHERE MODULE_ID = 'main' AND NAME = '" . $DB->ForSql($_server_uniq_id) . "_UpdateSystem' AND SITE_ID IS NULL AND DATEDIFF(SECOND, CONVERT(DATETIME, DESCRIPTION), GETDATE()) > " . $_i, false, "File: " . __FILE__ . "Line: " . __LINE__);
            $DB->Query("SET LOCK_TIMEOUT 1", false, "File: " . __FILE__ . "Line: " . __LINE__);
            $_query = $DB->Query("INSERT INTO B_OPTION(MODULE_ID, NAME, SITE_ID, VALUE, DESCRIPTION) VALUES('main', '" . $DB->ForSql($_server_uniq_id) . "_UpdateSystem', NULL, NULL, CONVERT(VARCHAR(128), GETDATE()))", true);
            $DB->Query("SET LOCK_TIMEOUT - 1", false, "File: " . __FILE__ . "Line: " . __LINE__);
            return ($_query !== false);
        }
    }
    public static function UnLock()
    {
        global $DB, $APPLICATION;
        $_server_uniq_id = $APPLICATION->GetServerUniqID();
        if ($DB->type == 'MYSQL')
        {
            $_query = $DB->Query("SELECT RELEASE_LOCK('" . $DB->ForSql($_server_uniq_id) . "_UpdateSystem') as L", false, "File: " . __FILE__ . "Line: " . __LINE__);
            $_row = $_query->Fetch();
            if ($_row["L"] == 0) return false;
            else return true;
        }
        elseif ($DB->type == "ORACLE")
        {
            return true;
        }
        else
        {
            $DB->Query("DELETE FROM B_OPTION WHERE MODULE_ID = 'main' AND NAME = '" . $DB->ForSql($_server_uniq_id) . "_UpdateSystem' AND SITE_ID IS NULL", false, "File: " . __FILE__ . "Line: " . __LINE__);
            return true;
        }
    }
    public static function Repair($type, $_stable_versions_only, $_lang = false)
    {
        if ($type == "include")
        {
            if (CUpdateClient::RegisterVersion($errorMessage, $_lang, $_stable_versions_only)) 
                CUpdateClient::AddMessage2Log("Include repaired");
            else 
                CUpdateClient::AddMessage2Log("Include repair error: " . $errorMessage);
        }
    }
    public static function IsUpdateAvailable(&$_update_module, &$strError)
    {
        $_update_module = array();
        $strError = '';
        $_stable_versions_only = COption::GetOptionString('main', 'stable_versions_only', 'Y');
        $_update_list = CUpdateClient::GetUpdatesList($strError, 'LANG', $_stable_versions_only);
        if (!$_update_list) return false;
        if (isset($_update_list['ERROR']))
        {
            $_count_error = count($_update_list['ERROR']);
            for ($_i = 0; $_i < $_count_error; $_i++) 
                $strError .= '[' . $_update_list['ERROR'][$_i]['@']['TYPE'] . ']' . $_update_list['ERROR'][$_i]['#'];
            return false;
        }
        if (isset($_update_list['MODULES']) && is_array($_update_list['MODULES']) && isset($_update_list['MODULES'][0]['#']['MODULE']) && is_array($_update_list['MODULES'][0]['#']['MODULE']))
        {
            $_update_module = $_update_list['MODULES'][0]['#']['MODULE'];
            return true;
        }
        if (isset($_update_list['UPDATE_SYSTEM'])) return true;
        return false;
    }
    public static function SubscribeMail($_email, &$strError, $_lang = false, $_stable_versions_only = "Y")
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log("exec CUpdateClient::SubscribeMail");
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, array() , array() , array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) $strError_tmp = '[RV01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            $_str_collect_data .= '&email=' . UrlEncode($_email) . '&query_type=mail';
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('ACTIV', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME SubscribeMail(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_arRes = Array();
            CUpdateClient::ParseServerData($_content, $_arRes, $strError_tmp);
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (isset($_arRes['DATA']['#']['ERROR']) && is_array($_arRes['DATA']['#']['ERROR']) && count($_arRes['DATA']['#']['ERROR']) > 0)
            {
                $_error_count = count($_arRes['DATA']['#']['ERROR']);
                for ($_i = 0; $_i < $_error_count; $_i++)
                {
                    if (strlen($_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE']) > 0) 
                        $strError_tmp .= '[' . $_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE'] . ']';
                    $strError_tmp .= $_arRes['DATA']['#']['ERROR'][$_i]['#'] . '.';
                }
            }
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'SM');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function ActivateCoupon($_coupon, &$strError, $_lang = false, $_stable_versions_only = "Y")
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::ActivateCoupon');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, array() , array() , array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) $strError_tmp = '[RV01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            $_str_collect_data .= '&coupon=' . UrlEncode($_coupon) . '&query_type=coupon';
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('ACTIV', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME ActivateCoupon(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_arRes = Array();
            CUpdateClient::ParseServerData($_content, $_arRes, $strError_tmp);
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (isset($_arRes['DATA']['#']['ERROR']) && is_array($_arRes['DATA']['#']['ERROR']) && count($_arRes['DATA']['#']['ERROR']) > 0)
            {
                $_error_count = count($_arRes['DATA']['#']['ERROR']);
                for ($_i = 0; $_i < $_error_count; $_i++)
                {
                    if (strlen($_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE']) > 0) $strError_tmp .= '[' . $_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE'] . ']';
                    $strError_tmp .= $_arRes['DATA']['#']['ERROR'][$_i]['#'] . '.';
                }
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (isset($_arRes['DATA']['#']['RENT']) && is_array($_arRes['DATA']['#']['RENT']))
            {
                COption::SetOptionString('main', '~SAAS_MODE', 'Y');
                CUpdateClient::__ApplyLicenseInfo($_arRes['DATA']['#']['RENT'][0]['@']);
            }
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'AC');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function __ApplyLicenseInfo($_options)
    {
        if (array_key_exists("V1", $_options) && array_key_exists("V2", $_options))
        {
            COption::SetOptionString('main', 'admin_passwordh', $_options["V1"]);
            $_file_p = fopen($_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/main/admin/define.php', 'w');
            fwrite($_file_p, '<' . '? Define("TEMPORARY_CACHE", "' . $_options['V2'] . '"); ?' . '>');
            fclose($_file_p);
        }
        if (array_key_exists(DATE_TO_SOURCE, $_options)) COption::SetOptionString(US_BASE_MODULE, '~support_finish_date', $_options[DATE_TO_SOURCE]);
        if (array_key_exists(MAX_SITES, $_options)) COption::SetOptionString('main', 'PARAM_MAX_SITES', IntVal($_options[MAX_SITES]));
        if (array_key_exists(MAX_USERS, $_options)) COption::SetOptionString('main', 'PARAM_MAX_USERS', IntVal($_options[MAX_USERS]));
        if (array_key_exists(MAX_USERS_STRING, $_options)) COption::SetOptionString('main', '~PARAM_MAX_USERS', $_options[MAX_USERS_STRING]);
        if (array_key_exists(MAX_SERVERS, $_options)) COption::SetOptionString('main', '~PARAM_MAX_SERVERS', IntVal($_options[MAX_SERVERS]));
        if (array_key_exists(COMPOSITE, $_options)) COption::SetOptionString('main', '~PARAM_COMPOSITE', $_options[COMPOSITE]);
        if (array_key_exists(PHONE_SIP, $_options)) COption::SetOptionString('main', '~PARAM_PHONE_SIP', $_options[PHONE_SIP]);
        if (array_key_exists(PARTNER_ID, $_options)) COption::SetOptionString('main', '~PARAM_PARTNER_ID', $_options[PARTNER_ID]);
        if (array_key_exists('L', $_options))
        {
            $_to_serialize = array();
            $_cpf_map_value = COption::GetOptionString('main', '~cpf_map_value', '');
            if (strlen($_cpf_map_value) > 0)
            {
                $_cpf_map_value = base64_decode($_cpf_map_value);
                $_to_serialize = unserialize($_cpf_map_value);
                if (!is_array($_to_serialize)) $_to_serialize = array();
            }
            if (count($_to_serialize) <= 0) $_to_serialize = array(
                'e' => array() ,
                'f' => array()
            );
            $_keys = explode(',', $_options['L']);
            foreach ($_keys as $_key) $_to_serialize['e'][$_key] = array(
                'F'
            );
            $_arr_keys = array_keys($_to_serialize['e']);
            foreach ($_arr_keys as $_arr_key)
            {
                if (in_array($_arr_key, $_keys) || $_arr_key == 'Portal')
                {
                    $_to_serialize['e'][$_arr_key] = array(
                        'F'
                    );
                }
                else
                {
                    if ($_to_serialize['e'][$_arr_key][0] != 'D') $_to_serialize['e'][$_arr_key] = array(
                        'X'
                    );
                }
            }
            $_cpf_map_value = serialize($_to_serialize);
            $_cpf_map_value = base64_encode($_cpf_map_value);
            COption::SetOptionString('main', '~cpf_map_value', $_cpf_map_value);
        }
        elseif (array_key_exists('L1', $_options))
        {
            $_to_serialize = array();
            $_keys = explode(',', $_options['L1']);
            foreach ($_keys as $_key) $_to_serialize[] = $_key;
            $_cpf_map_value = serialize($_to_serialize);
            $_cpf_map_value = base64_encode($_cpf_map_value);
            COption::SetOptionString('main', '~cpf_map_value', $_cpf_map_value);
        }
    }
    public static function UpdateUpdate(&$strError, $_lang = false, $_stable_versions_only = "Y")
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::UpdateUpdate');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, array() , array() , array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) $strError_tmp = '[RV01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            $_str_collect_data .= '& query_type=updateupdate';
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('REG', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME UpdateUpdate(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!($_file_p = fopen($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz', 'wb'))) 
                $strError_tmp .= ['URV02'] . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!fwrite($_file_p, $_content)) 
                $strError_tmp .= '[URV03]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz', GetMessage(SUPP_RV_WRT_TEMP_FILE)) . '.';
            @fclose($_file_p);
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_106222773 = '';
            if (!CUpdateClient::UnGzipArchive($_106222773, $strError_tmp, 'Y')) 
                $strError_tmp .= '[URV04]' . GetMessage(SUPP_RV_BREAK) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_update_full_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' . $_106222773;
            if (!file_exists($_update_full_path . '/update_info.xml') || !is_file($_update_full_path . '/update_info.xml')) 
                $strError_tmp .= '[URV05]' . str_replace('#FILE#', $_update_full_path . '/update_info.xml', GetMessage(SUPP_RV_ER_DESCR_FILE)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!is_readable($_update_full_path . '/update_info.xml')) 
                $strError_tmp .= '[URV06]' . str_replace('#FILE#', $_update_full_path . '/update_info.xml', GetMessage(SUPP_RV_READ_DESCR_FILE)) . '.';
        }
        if (strlen($strError_tmp) <= 0) $_content = file_get_contents($_update_full_path . '/update_info.xml');
        if (strlen($strError_tmp) <= 0)
        {
            $_arRes = Array();
            CUpdateClient::ParseServerData($_content, $_arRes, $strError_tmp);
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (isset($_arRes['DATA']['#']['ERROR']) && is_array($_arRes['DATA']['#']['ERROR']) && count($_arRes['DATA']['#']['ERROR']) > 0)
            {
                $_error_count = count($_arRes['DATA']['#']['ERROR']);
                for ($_i = 0; $_i < $_error_count; $_i++)
                {
                    if (strlen($_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE']) > 0) 
                        $strError_tmp .= '[' . $_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE'] . ']';
                    $strError_tmp .= $_arRes['DATA']['#']['ERROR'][$_i]['#'] . '.';
                }
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_module_path = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main';
            CUpdateClient::CheckDirPath($_module_path . '/', true);
            if (!file_exists($_module_path) || !is_dir($_module_path)) 
                $strError_tmp .= '[UUK04]' . str_replace('#MODULE_DIR#', $_module_path, GetMessage(SUPP_UK_NO_MODIR)) . '.';
            if (strlen($strError_tmp) <= 0) 
                if (!is_writable($_module_path)) 
                    $strError_tmp .= '[UUK05]' . str_replace('#MODULE_DIR#', $_module_path, GetMessage(SUPP_UK_WR_MODIR)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            CUpdateClient::CopyDirFiles($_update_full_path . '/main', $_module_path, $strError_tmp);
        }
        if (strlen($strError_tmp) <= 0)
        {
            CUpdateClient::AddMessage2Log('Update updated successfully!', 'CURV');
            CUpdateClient::DeleteDirFilesEx($_update_full_path);
            bx_accelerator_reset();
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'UU');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function GetPHPSources(&$strError, $_lang, $_stable_versions_only, $_req_modules)
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::GetPHPSources');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, $_req_modules, array() , array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('SRC', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME GetPHPSources(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (StrLen($strError_tmp) <= 0)
        {
            if (!($_file_p = fopen($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz', 'wb'))) $strError_tmp = '[GNSU03]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            fwrite($_file_p, $_content);
            fclose($_file_p);
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'GNSU00');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function GetSupportFullLoad(&$strError, $_lang, $_stable_versions_only, $_req_modules)
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::GetSupportFullLoad');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, $_req_modules, array() , array());
        if ($_str_collect_data === False || strlen($_str_collect_data) <= 0 || strlen($strError_tmp) > 0)
        {
            if (strlen($strError_tmp) <= 0) $strError_tmp = '[GSFLU01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_str_collect_data .= '&support_full_load=Y';
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('SRC', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (strlen($strError_tmp) <= 0) $strError_tmp = '[GSFL02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME GetSupportFullLoad(request)' . round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!($_file_p = fopen($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz', 'wb'))) $strError_tmp = '[GSFL03]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            fwrite($_file_p, $_content);
            fclose($_file_p);
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'GSFL00');
            $strError .= $strError_tmp;
            return false;
        }
        else return true;
    }
    public static function RegisterVersion(&$strError, $_lang = false, $_stable_versions_only = "Y")
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::RegisterVersion');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, array() , array() , array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) 
                $strError_tmp = '[RV01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            $_str_collect_data .= '&query_type=register';
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('REG', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME RegisterVersion(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!($_file_p = fopen($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz', 'wb'))) 
                $strError_tmp .= '[URV02]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!fwrite($_file_p, $_content)) 
                $strError_tmp .= '[URV03]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz', GetMessage(SUPP_RV_WRT_TEMP_FILE)) . '.';
            @fclose($_file_p);
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_106222773 = '';
            if (!CUpdateClient::UnGzipArchive($_106222773, $strError_tmp, Y)) $strError_tmp .= '[URV04]' . GetMessage(SUPP_RV_BREAK) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_update_full_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' . $_106222773;
            if (!file_exists($_update_full_path . '/update_info.xml') || !is_file($_update_full_path . '/update_info.xml')) 
                $strError_tmp .= '[URV05]' . str_replace('#FILE#', $_update_full_path . '/update_info.xml', GetMessage(SUPP_RV_ER_DESCR_FILE)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!is_readable($_update_full_path . '/update_info.xml')) 
                $strError_tmp .= '[URV06]' . str_replace('#FILE#', $_update_full_path . '/update_info.xml', GetMessage(SUPP_RV_READ_DESCR_FILE)) . '.';
        }
        if (strlen($strError_tmp) <= 0) $_content = file_get_contents($_update_full_path . '/update_info.xml');
        if (strlen($strError_tmp) <= 0)
        {
            $_arRes = Array();
            CUpdateClient::ParseServerData($_content, $_arRes, $strError_tmp);
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (isset($_arRes['DATA']['#']['ERROR']) && is_array($_arRes['DATA']['#']['ERROR']) && count($_arRes['DATA']['#']['ERROR']) > 0)
            {
                $_error_count = count($_arRes['DATA']['#']['ERROR']);
                for ($_i = 0; $_i < $_error_count; $_i++)
                {
                    if (strlen($_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE']) > 0) $strError_tmp .= '[' . $_arRes['DATA']['#'][ERROR][$_i]['@']['TYPE'] . ']';
                    $strError_tmp .= $_arRes['DATA']['#']['ERROR'][$_i]['#'] . '.';
                }
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!file_exists($_update_full_path . '/include.php') || !is_file($_update_full_path . '/include.php')) $strError_tmp .= '[URV07]' . GetMessage(SUPP_RV_NO_FILE) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_1758554868 = @filesize($_update_full_path . '/include.php');
            if (IntVal($_1758554868) != IntVal($_arRes['DATA']['#']['FILE'][0]['@']['SIZE'])) $strError_tmp .= '[URV08]' . GetMessage(SUPP_RV_ER_SIZE) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!is_writeable($_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/include.php')) $strError_tmp .= '[URV09]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/include.php', GetMessage(SUPP_RV_NO_WRITE)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!copy($_update_full_path . '/include.php', $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/include.php')) $strError_tmp .= '[URV10]' . GetMessage(SUPP_RV_ERR_COPY) . '.';
            @chmod($_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/include.php', BX_FILE_PERMISSIONS);
        }
        if (strlen($strError_tmp) <= 0)
        {
            $strongUpdateCheck = COption::GetOptionString(main, strong_update_check, Y);
            if ($strongUpdateCheck == Y)
            {
                $_1327440025 = dechex(crc32(file_get_contents($_update_full_path . '/include.php')));
                $_114995783 = dechex(crc32(file_get_contents($_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/include.php')));
                if ($_114995783 != $_1327440025) $strError_tmp .= '[URV1011]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/include.php', GetMessage(SUPP_UGA_FILE_CRUSH)) . '.';
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            CUpdateClient::AddMessage2Log('Product registered successfully!', 'CURV');
            CUpdateClient::DeleteDirFilesEx($_update_full_path);
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'CURV');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function ActivateLicenseKey($_698992942, &$strError, $_lang = false, $_stable_versions_only = "Y")
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::ActivateLicenseKey');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, array() , array() , array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            $_str_collect_data .= '&query_type=activate';
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            foreach ($_698992942 as $_arr_key => $_arr_value) $_str_collect_data .= '&' . $_arr_key . '=' . urlencode($_arr_value);
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('ACTIV', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME ActivateLicenseKey(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_arRes = Array();
            CUpdateClient::ParseServerData($_content, $_arRes, $strError_tmp);
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (isset($_arRes['DATA']['#']['ERROR']) && is_array($_arRes['DATA']['#']['ERROR']) && count($_arRes['DATA']['#']['ERROR']) > 0)
            {
                $_error_count = count($_arRes['DATA']['#']['ERROR']);
                for ($_i = 0; $_i < $_error_count; $_i++)
                {
                    if (strlen($_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE']) > 0) $strError_tmp .= '[' . $_arRes['DATA']['#']['ERROR'][$_i]['@']['TYPE'] . ']';
                    $strError_tmp .= $_arRes['DATA']['#']['ERROR'][$_i]['#'] . '.';
                }
            }
        }
        if (strlen($strError_tmp) <= 0) CUpdateClient::AddMessage2Log('License key activated successfully!', 'CUALK');
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'CUALK');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function GetNextStepLangUpdates(&$strError, $_lang = false, $_req_langs = array())
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::GetNextStepLangUpdates');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, 'N', array() , $_req_langs, array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('STEPL', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME GetNextStepLangUpdates(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (StrLen($strError_tmp) <= 0)
        {
            if (!($_file_p = fopen($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz', 'wb'))) $strError_tmp = '[GNSU03]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            fwrite($_file_p, $_content);
            fclose($_file_p);
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'GNSLU00');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function GetNextStepHelpUpdates(&$strError, $_lang = false, $_req_helps = array())
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::GetNextStepHelpUpdates');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, 'N', array() , array() , $_req_helps);
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('STEPH', $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log('TIME GetNextStepHelpUpdates(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        }
        if (StrLen($strError_tmp) <= 0)
        {
            if (!($_file_p = fopen($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz', 'wb'))) $strError_tmp = '[GNSU03]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            fwrite($_file_p, $_content);
            fclose($_file_p);
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'GNSHU00');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function getSpd()
    {
        return self::getModuleValue(US_BASE_MODULE, "crc_code", "");
    }
    public static function setSpd($_key)
    {
        if ($_key != "") COption::SetOptionString(US_BASE_MODULE, "crc_code", $_key);
    }
    public static function LoadModulesUpdates(&$errorMessage, &$_1244932944, $_lang = false, $_stable_versions_only = "Y", $_req_modules = array())
    {
        $_1244932944 = array();
        $_str_collect_data = '';
        $_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz';
        $_update_load_timeout = COption::GetOptionString('main', 'update_load_timeout', 30);
        if ($_update_load_timeout < 0) $_update_load_timeout = 0;
        CUpdateClient::AddMessage2Log('exec CUpdateClient::LoadModulesUpdates');
        if (file_exists($_path . '.log'))
        {
            $_content = file_get_contents($_path . '.log');
            CUpdateClient::ParseServerData($_content, $_1244932944, $errorMessage);
        }
        if (count($_1244932944) <= 0 || strlen($errorMessage) > 0)
        {
            $_1244932944 = array();
            if (file_exists($_path . '.tmp')) @unlink($_path . '.tmp');
            if (file_exists($_path . '.log')) @unlink($_path . '.log');
            if (strlen($errorMessage) > 0)
            {
                CUpdateClient::AddMessage2Log($errorMessage, 'LMU001');
                return 'E';
            }
        }
        if (count($_1244932944) <= 0)
        {
            $_str_collect_data = CUpdateClient::CollectRequestData($errorMessage, $_lang, $_stable_versions_only, $_req_modules, array() , array());
            if (empty($_str_collect_data) || strlen($errorMessage) > 0)
            {
                if (strLen($errorMessage) <= 0) $errorMessage = '[GNSU01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
                CUpdateClient::AddMessage2Log($errorMessage, 'LMU002');
                return 'E';
            }
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage('STEPM', $_str_collect_data, $errorMessage);
            if (strlen($_content) <= 0 || strlen($errorMessage) > 0)
            {
                if (strlen($errorMessage) <= 0) $errorMessage = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
                CUpdateClient::AddMessage2Log($errorMessage, 'LMU003');
                return 'E';
            }
            CUpdateClient::AddMessage2Log('TIME LoadModulesUpdates(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
            CUpdateClient::ParseServerData($_content, $_1244932944, $errorMessage);
            if (strlen($errorMessage) > 0)
            {
                CUpdateClient::AddMessage2Log($errorMessage, 'LMU004');
                return 'E';
            }
            if (isset($_1244932944['DATA']['#']['ERROR']))
            {
                $_error_count = count($_1244932944['DATA']['#']['ERROR']);
                for ($_i = 0; $_i < $_error_count; $_i++) 
                    $errorMessage .= '[' . $_1244932944['DATA']['#']['ERROR'][$_i]['@']['TYPE'] . ']' . $_1244932944['DATA']['#']['ERROR'][$_i]['#'];
            }
            if (strlen($errorMessage) > 0)
            {
                CUpdateClient::AddMessage2Log($errorMessage, 'LMU005');
                return 'E';
            }
            if (isset($_1244932944['DATA']['#']['NOUPDATES']))
            {
                CUpdateClient::AddMessage2Log('Finish - NOUPDATES', 'STEP');
                return 'F';
            }
            $_file_p = fopen($_path . '.log', 'wb');
            if (!$_file_p)
            {
                $errorMessage = '[GNSU03]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
                CUpdateClient::AddMessage2Log($errorMessage, 'LMU006');
                return 'E';
            }
            fwrite($_file_p, $_content);
            fclose($_file_p);
            CUpdateClient::AddMessage2Log('STEP', 'S');
            return 'S';
        }
        if (isset($_1244932944['DATA']['#']['FILE'][0]['@']['NAME']))
        {
            if ($_str_collect_data == '')
            {
                $_str_collect_data = CUpdateClient::CollectRequestData($errorMessage, $_lang, $_stable_versions_only, $_req_modules, array() , array());
                if ($_str_collect_data === False || strlen($_str_collect_data) <= 0 || strlen($errorMessage) > 0)
                {
                    if (StrLen($errorMessage) <= 0) $errorMessage = '[GNSU01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
                    CUpdateClient::AddMessage2Log($errorMessage, 'LMU007');
                    return 'E';
                }
            }
            CUpdateClient::AddMessage2Log('loadFileBx');
            $_1505806505 = static ::__450740454($_1244932944["DATA"]["#"]["FILE"][0]["@"]["NAME"], $_1244932944["DATA"]["#"]["FILE"][0]["@"]["SIZE"], $_path, $_update_load_timeout, $_str_collect_data, $errorMessage);
        }
        elseif ($_1244932944['DATA']['#']['FILE'][0]['@']['URL'])
        {
            CUpdateClient::AddMessage2Log('loadFile');
            $_1505806505 = static ::__1534702458($_1244932944["DATA"]["#"]["FILE"][0]["@"]["URL"], $_1244932944["DATA"]["#"]["FILE"][0]["@"]["SIZE"], $_path, $_update_load_timeout, $errorMessage);
        }
        else
        {
            $_1505806505 = 'E';
            $errorMessage .= GetMessage(SUPP_PSD_BAD_RESPONSE);
        }
        if ($_1505806505 == 'E')
        {
            CUpdateClient::AddMessage2Log($errorMessage, 'GNSU001');
            $errorMessage .= $errorMessage;
        }
        elseif ($_1505806505 == U)
        {
            @unlink($_path . '.log');
        }
        CUpdateClient::AddMessage2Log('RETURN', $_1505806505);
        return $_1505806505;
    }
    public static function __450740454($_ufile, $_all_length, $_tmp_file, $_update_load_timeout, $_str_param, &$errorMessage)
    {
        $_update_load_timeout = intval($_update_load_timeout);
        $_cur_time = 0;
        if ($_update_load_timeout > 0) $_cur_time = getmicrotime();
        $_sockInfo = static ::_getSockInfo();
        $_sock_pointer = fsockopen($_sockInfo['SOCKET_IP'], $_sockInfo['SOCKET_PORT'], $_error_no, $_error_str, 0);
        if (!$_sock_pointer)
        {
            $errorMessage .= static ::ConvertToLangCharset($_error_str, $_error_no, $_sockInfo);
            return 'E';
        }
        $_str_header = '';
        if ($_sockInfo['USE_PROXY'])
        {
            $_str_header .= 'POST http://' . $_sockInfo['IP'] . '/thurly/updates/us_updater_modules.php HTTP/1.0';
            if ($_sockInfo['PROXY_USERNAME']) $_str_header .= 'Proxy-Authorization: Basic' . base64_encode($_sockInfo['PROXY_USERNAME'] . ':' . $_sockInfo['PROXY_PASSWORD']) . '';
        }
        else
        {
            $_str_header .= 'POST /thurly/updates/us_updater_modules.php HTTP / 1.0';
        }
        $_crc_code = self::getModuleValue(US_BASE_MODULE, 'crc_code', '');
        $_str_param .= '&spd=' . urlencode($_crc_code);
        $_str_param .= '&utf=' . urlencode(defined(BX_UTF) ? 'Y' : 'N');
        $_db_version = $DB->GetVersion();
        $_str_param .= '&dbv=' . urlencode($_db_version != false ? $_db_version : '');
        $_str_param .= '&NS=' . COption::GetOptionString('main', 'update_site_ns', '');
        $_str_param .= '&KDS=' . COption::GetOptionString('main', 'update_devsrv', '');
        $_str_param .= '&UFILE=' . $_ufile;
        $_tmp_file_size = (file_exists($_tmp_file . '.tmp') ? filesize($_tmp_file . '.tmp') : 0);
        $_str_param .= '&USTART=' . $_tmp_file_size;
        $_str_header .= 'User-Agent: ThurlySMUpdater';
        $_str_header .= 'Accept: */*';
        $_str_header .= 'Host: ' . $_sockInfo['IP'] . '';
        $_str_header .= 'Accept-Language: en';
        $_str_header .= 'Content-type: application/x-www-form-urlencoded';
        $_str_header .= 'Content-length:' . strlen($_str_param) . '';
        $_str_header .= $_str_param;
        $_str_header .= '';
        fputs($_sock_pointer, $_str_header);
        $_sock_contents = '';
        while (($_str_result = fgets($_sock_pointer, 0)) && $_str_result != '') 
            $_sock_contents .= $_str_result;
        $_arr_sock_contents = preg_split( '##', $_sock_contents);
        $_content_length = 0;
        $_content_count = count($_arr_sock_contents);
        for ($_i = 0; $_i < $_content_count; $_i++)
        {
            if (strpos($_arr_sock_contents[$_i], 'Content-Length') !== false)
            {
                $_pos_colon = strpos($_arr_sock_contents[$_i],':');
                $_content_length = intval(trim(substr($_arr_sock_contents[$_i], $_pos_colon , strlen($_arr_sock_contents[$_i]) - $_pos_colon )));
            }
        }
        if (($_content_length + $_tmp_file_size) != $_all_length)
        {
            $errorMessage .= '[ELVL001]' . GetMessage(ELVL001_SIZE_ERROR) . '.';
            return 'E';
        }
        @unlink($_tmp_file . '.tmp1');
        if (file_exists($_tmp_file . '.tmp'))
        {
            if (@rename($_tmp_file . '.tmp', $_tmp_file . '.tmp1'))
            {
                $_file_p = fopen($_tmp_file . '.tmp', 'wb');
                if ($_file_p)
                {
                    $_file_tmp_1_p = fopen($_tmp_file . '.tmp1', 'rb');
                    do
                    {
                        $_file_tmp_1_content = fread($_file_tmp_1_p, 0);
                        if (strlen($_file_tmp_1_content) == 0) break;
                        fwrite($_file_p, $_file_tmp_1_content);
                    }
                    while (true);
                    fclose($_file_tmp_1_p);
                    @unlink($_tmp_file . '.tmp1');
                }
                else
                {
                    $errorMessage .= '[JUHYT002]' . GetMessage(JUHYT002_ERROR_FILE) . '.';
                    return 'E';
                }
            }
            else
            {
                $errorMessage .= '[JUHYT003]' . GetMessage(JUHYT003_ERROR_FILE) . '.';
                return 'E';
            }
        }
        else
        {
            $_file_p = fopen($_tmp_file . '.tmp', 'wb');
            if (!$_file_p)
            {
                $errorMessage .= '[JUHYT004]' . GetMessage(JUHYT004_ERROR_FILE) . '.';
                return 'E';
            }
        }
        $_sock_read_flag = true;
        while (true)
        {
            if ($_update_load_timeout > 0 && (CUpdateClient::getmicrotime() - $_cur_time) > $_update_load_timeout)
            {
                $_sock_read_flag = false;
                break;
            }
            $_str_result = fread($_sock_pointer, 0);
            if ($_str_result == '') 
                break;
            fwrite($_file_p, $_str_result);
        }
        fclose($_file_p);
        fclose($_sock_pointer);
        CUpdateClient::AddMessage2Log('Time - ' . (CUpdateClient::getmicrotime() - $_cur_time) . 'sec', 'DOWNLOAD');
        $_tmp_file_size = (file_exists($_tmp_file . '.tmp') ? filesize($_tmp_file .'.tmp') : 0);
        if ($_tmp_file_size == $_all_length)
        {
            $_sock_read_flag = true;
        }
        if ($_sock_read_flag)
        {
            @unlink($_tmp_file);
            if (!@rename($_tmp_file . '.tmp', $_tmp_file))
            {
                $errorMessage .= '[JUHYT005]' . GetMessage(JUHYT005_ERROR_FILE) . '.';
                return 'E';
            }
            @unlink($_tmp_file . '.tmp');
        }
        else
        {
            return 'S';
        }
        return 'U';
    }
    public function __1534702458($_ufile, $_all_length, $_tmp_file, $_update_load_timeout, &$errorMessage)
    {
        $_update_load_timeout = intval($_update_load_timeout);
        $_cur_time = 0;
        if ($_update_load_timeout > 0) $_cur_time = getmicrotime();
        $_tmp_file_size = file_exists($_tmp_file . '.tmp') ? filesize($_tmp_file . '.tmp') : 0;
        $_sockInfo = static ::_getSockInfo();
        $_sock_pointer = fsockopen($_sockInfo['SOCKET_IP'], $_sockInfo['SOCKET_PORT'], $_error_no, $_error_str, 0);
        if (!$_sock_pointer)
        {
            $errorMessage .= static ::ConvertToLangCharset($_error_str, $_error_no, $_sockInfo);
            return E;
        }
        if (!$_ufile) $_ufile = '/';
        $_str_header ='';
        if (!$_sockInfo['USE_PROXY'])
        {
            $_str_header .= 'GET ' . $_ufile . ' HTTP/1.0';
        	$_str_header .= 'Host: ' . $_sockInfo['IP'] . '';
        }
        else
        {
            $_str_header .= 'GET http://'.$_sockInfo['IP'].$_ufile. 'HTTP/1.0';
            $_str_header .= 'Host: '. $_sockInfo['IP'] .'';
            if ($_sockInfo['PROXY_USERNAME']) 
            	$_str_header .= 'Proxy-Authorization: Basic' . base64_encode($_sockInfo['PROXY_USERNAME'] .':' . $_sockInfo['PROXY_PASSWORD']) .'';
        }
        $_str_header .='User-Agent: ThurlySMUpdater';
        if ($_tmp_file_size > 0) $_str_header .= 'Range: bytes = '. $_tmp_file_size . '-';
            $_str_header .= '';
            fwrite($_sock_pointer, $_str_header);
            $_sock_contents = '';
            while (($_str_result = fgets($_sock_pointer, 0)) && $_str_result !='') 
            	$_sock_contents .= $_str_result;
            $_arr_sock_contents = preg_split('##', $_sock_contents);
            $_1999528990 = 0;
            $_627171301 ='';
            if (preg_match( '#([A-Z]{4})/([0-9.]{3}) ([0-9]{3})#', $_arr_sock_contents[0], $_1579181490)) {
                $_1999528990 = intval($_1579181490[0]);
                $_627171301 = substr($_arr_sock_contents[0], strpos($_arr_sock_contents[0], $_1999528990) + strlen($_1999528990), strlen($_arr_sock_contents[0]) - strpos($_arr_sock_contents[0], $_1999528990));
            }
            if ($_1999528990 != 0 && $_1999528990 != 0 && $_1999528990 != 0 && $_1999528990 != 0)
            {
                $errorMessage .= GetMessage(SUPP_PSD_BAD_RESPONSE) . '(' . $_1999528990 . '-' . $_627171301 .')';
                return 'E';
            }
            $_13086363 = '';
            $_2007601697 = 0;
            for ($_i = 0; $_i < count($_arr_sock_contents); $_i++)
            {
                if (strpos($_arr_sock_contents[$_i], 'Content-Range') !== false) 
                	$_13086363 = trim(substr($_arr_sock_contents[$_i], strpos($_arr_sock_contents[$_i],':'), strlen($_arr_sock_contents[$_i]) - strpos($_arr_sock_contents[$_i],':')));
                elseif (strpos($_arr_sock_contents[$_i], 'Content-Length') !== false) 
                	$_2007601697 = doubleval(Trim(substr($_arr_sock_contents[$_i], strpos($_arr_sock_contents[$_i],':'), strlen($_arr_sock_contents[$_i]) - strpos($_arr_sock_contents[$_i],':'))));
            }
            $_712011901 = true;
            if (strlen($_13086363) > 0)
            {
                if (preg_match( '#*bytes+([0-9]*)*-*([0-9]*)*/*([0-9]*)#i', $_13086363, $_1579181490)) {
                $_1855905457 = doubleval($_1579181490[0]);
                $_1088723865 = doubleval($_1579181490[0]);
                $_1721545435 = doubleval($_1579181490[0]);
                if (($_1855905457 == $_tmp_file_size) && ($_1088723865 == ($_all_length)) && ($_1721545435 == $_all_length))
                {
                    $_712011901 = false;
                }
            }
        }
        if ($_712011901)
        {
            @unlink($_tmp_file . '.tmp');
            $_tmp_file_size = 0;
        }
        if (($_2007601697 + $_tmp_file_size) != $_all_length)
        {
            $errorMessage .= '[ELVL010]' . GetMessage(ELVL001_SIZE_ERROR) . '.';
            return 'E';
        }
        $_file_p = fopen($_tmp_file . '.tmp', 'ab');
        if (!$_file_p)
        {
            $errorMessage .= '[JUHYT010]' . GetMessage(JUHYT002_ERROR_FILE) . '.';
            return 'E';
        }
        $_sock_read_flag = true;
        $_1788858307 = (double)$_tmp_file_size;
        while (true)
        {
            if ($_update_load_timeout > 0 && (getmicrotime() - $_cur_time) > $_update_load_timeout)
            {
                $_sock_read_flag = false;
                break;
            }
            $_str_result = fread($_sock_pointer, 0);
            $_1788858307 += strlen($_str_result);
            if ($_str_result =='') break;
            fwrite($_file_p, $_str_result);
        }
        fclose($_file_p);
        fclose($_sock_pointer);
        $_tmp_file_size = (file_exists($_tmp_file . '.tmp') ? filesize($_tmp_file . '.tmp') : 0);
        if ($_tmp_file_size == $_all_length)
        {
            $_sock_read_flag = true;
        }
        if ($_sock_read_flag)
        {
            @unlink($_tmp_file);
            if (!@rename($_tmp_file . '.tmp', $_tmp_file))
            {
                $errorMessage .= '[JUHYT010]' . GetMessage(JUHYT005_ERROR_FILE) . '.';
                return 'E';
            }
            @unlink($_tmp_file . '.tmp');
        }
        else
        {
            return 'S';
        }
        return 'U';
    }
    public static function LoadLangsUpdates(&$errorMessage, &$_1244932944, $_lang = false, $_stable_versions_only = "Y", $_req_langs = array())
    {
        $_1244932944 = array();
        $_cur_time = CUpdateClient::getmicrotime();
        $strError_tmp ='';
        $_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz';
        $_update_load_timeout = COption::GetOptionString('main', 'update_load_timeout', 30);
        if ($_update_load_timeout < 0) $_update_load_timeout = 0;
        CUpdateClient::AddMessage2Log('exec CUpdateClient::LoadLangsUpdates');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, array() , $_req_langs, array());
        if ($_str_collect_data === False || strlen($_str_collect_data) <= 0 || strlen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) $strError_tmp = '[GNSU01]' . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (file_exists($_path . '.log'))
            {
                $_content = file_get_contents($_path . '.log');
                CUpdateClient::ParseServerData($_content, $_1244932944, $strError_tmp);
            }
            if (count($_1244932944) <= 0 || strlen($strError_tmp) > 0)
            {
                $_1244932944 = array();
                if (file_exists($_path . '.tmp')) @unlink($_path . '.tmp');
                if (file_exists($_path . '.log')) @unlink($_path . '.log');
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (count($_1244932944) <= 0)
            {
                if (file_exists($_path . '.tmp')) @unlink($_path . '.tmp');
                if (file_exists($_path . '.log')) @unlink($_path . '.log');
                CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
                $_cur_time = CUpdateClient::getmicrotime();
                $_content = CUpdateClient::GetHTTPPage('STEPL', $_str_collect_data, $strError_tmp);
                if (strlen($_content) <= 0)
                {
                    if (strlen($strError_tmp) <= 0) $strError_tmp = '[GNSU02]' . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
                }
                CUpdateClient::AddMessage2Log('TIMELoadLangsUpdates(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
                if (strlen($strError_tmp) <= 0) CUpdateClient::ParseServerData($_content, $_1244932944, $strError_tmp);
                if (strlen($strError_tmp) <= 0)
                {
                    if (isset($_1244932944['DATA']['#']['ERROR']))
                    {
                    	$_error_count = count($_1244932944['DATA']['#']['ERROR']);
                        for ($_i = 0; $_i < $_error_count; $_i++) 
                        	$strError_tmp .= '[' . $_1244932944['DATA']['#']['ERROR'][$_i]['@']['TYPE'] . ']' . $_1244932944['DATA']['#']['ERROR'][$_i]['#'];
                    }
                }
                if (strlen($strError_tmp) <= 0)
                {
                    if (isset($_1244932944['DATA']['#']['NOUPDATES']))
                    {
                        CUpdateClient::AddMessage2Log('Finish-NOUPDATES', 'STEP');
                        return 'F';
                    }
                }
                if (strlen($strError_tmp) <= 0)
                {
                    if (!($_file_p = fopen($_path . '.log', 'wb'))) $strError_tmp = '[GNSU03]' . str_replace('#FILE#', $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates', GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
                    if (strlen($strError_tmp) <= 0)
                    {
                        fwrite($_file_p, $_content);
                        fclose($_file_p);
                        return 'S';
                    }
                }
                $errorMessage .= $strError_tmp;
                return 'E';
            }
        }
        $_sockInfo = array();
        if (strlen($strError_tmp) <= 0)
        {
            $_sockInfo = static ::_getSockInfo();
            $_sock_file_p = fsockopen($_sockInfo['SOCKET_IP'], $_sockInfo['SOCKET_PORT'], $_error_no, $_error_str, 0);
            if (!$_sock_file_p)
            {
                $strError_tmp .= static ::ConvertToLangCharset($_error_str, $_error_no, $_sockInfo);
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_str_header ='';
            if ($_sockInfo['USE_PROXY'])
            {
                $_str_header .= 'POST http://'.$_sockInfo['IP'].'/thurly/updates/us_updater_langs.php HTTP/1.0';
                if (strlen($_sockInfo['PROXY_USERNAME']) > 0) $_str_header .= 'Proxy-Authorization: Basic '. base64_encode($_sockInfo['PROXY_USERNAME'] .':' . $_sockInfo['PROXY_PASSWORD']) .'';
            }
            else 
            	$_str_header .= 'POST /thurly/updates/us_updater_langs.php HTTP/1.0';
            $_crc_code = self::getModuleValue(US_BASE_MODULE, 'crc_code','');
            $_url_param = $_str_collect_data . '&spd='. urlencode($_crc_code);
            $_url_param .= '&utf=' . urlencode(defined(BX_UTF) ? 'Y' : 'N');
            $_db_version = $DB->GetVersion();
            $_url_param .= '&dbv=' . urlencode($_db_version != false ? $_db_version : '');
            $_url_param .= '&NS= ' . COption::GetOptionString('main', 'update_site_ns','');
            $_url_param .= '&KDS=' . COption::GetOptionString('main', 'update_devsrv','');
            $_url_param .= '&UFILE=' . $_1244932944['DATA']['#']['FILE'][0]['@']['NAME'];
            $_tmp_file_size = (file_exists($_path . '.tmp') ? filesize($_path . '.tmp') : (0));
            $_url_param .= '&USTART=' . $_tmp_file_size;
            $_str_header .= 'User - Agent: ThurlySMUpdater';
            $_str_header .= 'Accept: */*';
            $_str_header .= 'Host: '. $_sockInfo['IP'] .'';
            $_str_header .= 'Accept-Language: en';
            $_str_header .= 'Content-type: application/x-www-form-urlencoded';
            $_str_header .= 'Content-length: ' . strlen($_url_param) . '';
            $_str_header .= $_url_param;
            $_str_header .= '';
            fputs($_sock_file_p, $_str_header);
            $_sock_contents = '';
            while (($_str_result = fgets($_sock_file_p, 0)) && $_str_result != '') 
            	$_sock_contents .= $_str_result;
            $_sock_contents_arr = preg_split( '##', $_sock_contents);
            $_content_length = 0;
            $_content_count = count($_sock_contents_arr);
            for ($_i = 0; $_i < $_content_count; $_i++)
            {
                if (strpos($_sock_contents_arr[$_i], 'Content-Length') !== false)
                {
                    $_pos_colon = strpos($_sock_contents_arr[$_i],':');
                    $_content_length = intval(trim(substr($_sock_contents_arr[$_i], $_pos_colon, strlen($_sock_contents_arr[$_i]) - $_pos_colon)));
                }
            }
            if (($_content_length + $_tmp_file_size) != $_1244932944['DATA']['#']['FILE'][0]['@']['SIZE']) 
            	$strError_tmp .= '[ELVL001]' . GetMessage(ELVL001_SIZE_ERROR) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            @unlink($_path . '.tmp1');
            if (file_exists($_path . '.tmp'))
            {
                if (@rename($_path . '.tmp', $_path . '.tmp1'))
                {
                    $_file_tmp_p = fopen($_path . '.tmp', 'wb');
                    if ($_file_tmp_p)
                    {
                        $_file_tmp_1_p = fopen($_path . '.tmp1', 'rb');
                        do
                        {
                            $_file_tmp_1_content = fread($_file_tmp_1_p, 0);
                            if (strlen($_file_tmp_1_content) == 0) break;
                            fwrite($_file_tmp_p, $_file_tmp_1_content);
                        }
                        while (true);
                        fclose($_file_tmp_1_p);
                        @unlink($_path . '.tmp1');
                    }
                    else
                    {
                        $strError_tmp .= '[JUHYT002]' . GetMessage(JUHYT002_ERROR_FILE) . '.';
                    }
                }
                else
                {
                    $strError_tmp .= '[JUHYT003]' . GetMessage(JUHYT003_ERROR_FILE) . '.';
                }
            }
            else
            {
                $_file_tmp_p = fopen($_path . '.tmp', 'wb');
                if (!$_file_tmp_p) $strError_tmp .= '[JUHYT004]' . GetMessage(JUHYT004_ERROR_FILE) . '.';
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_sock_read_flag = true;
            while (true)
            {
                if ($_update_load_timeout > 0 && (CUpdateClient::getmicrotime() - $_cur_time) > $_update_load_timeout)
                {
                    $_sock_read_flag = false;
                    break;
                }
                $_str_result = fread($_sock_file_p, 0);
                if ($_str_result == '') break;
                fwrite($_file_tmp_p, $_str_result);
            }
            fclose($_file_tmp_p);
            fclose($_sock_file_p);
        }
        if (strlen($strError_tmp) <= 0)
        {
            CUpdateClient::AddMessage2Log('Time-' . (CUpdateClient::getmicrotime() - $_cur_time) . 'sec', 'DOWNLOAD');
            if ($_sock_read_flag)
            {
                @unlink($_path);
                if (!@rename($_path . '.tmp', $_path)) $strError_tmp .= '[JUHYT005]' . GetMessage(JUHYT005_ERROR_FILE) . '.';
            }
            else
            {
                return 'S';
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            @unlink($_path . '.tmp');
            @unlink($_path . '.log');
            return U;
        }
        if (file_exists($_path . '.tmp')) @unlink($_path . '.tmp');
        if (file_exists($_path . '.log')) @unlink($_path . '.log');
        CUpdateClient::AddMessage2Log($strError_tmp, 'GNSU001');
        $errorMessage .= $strError_tmp;
        return 'E';
    }
    public static function GetNextStepUpdates(&$strError, $_lang = false, $_stable_versions_only = "Y", $_req_modules = array())
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log(execCUpdateClient::GetNextStepUpdates);
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, $_req_modules, array() , array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            if (StrLen($strError_tmp) <= 0) 
                $strError_tmp = "[GNSU01]" . GetMessage(SUPZ_NO_QSTRING) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', "LICENSE_KEY=X", $_str_collect_data));
            $_cur_time = CUpdateClient::getmicrotime();
            $_content = CUpdateClient::GetHTTPPage(STEPM, $_str_collect_data, $strError_tmp);
            if (strlen($_content) <= 0)
            {
                if (StrLen($strError_tmp) <= 0) 
                    $strError_tmp = "[GNSU02]" . GetMessage(SUPZ_EMPTY_ANSWER) . '.';
            }
            CUpdateClient::AddMessage2Log("TIMEGetNextStepUpdates(request)" . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . "sec");
        }
        if (StrLen($strError_tmp) <= 0)
        {
            if (!($_file_p = fopen($_SERVER["DOCUMENT_ROOT"] . "/thurly/updates/update_archive.gz", 'wb'))) 
                $strError_tmp = "[GNSU03]" . str_replace('#FILE#', $_SERVER["DOCUMENT_ROOT"] . "/thurly/updates", GetMessage(SUPP_RV_ER_TEMP_FILE)) . '.';
        }
        if (StrLen($strError_tmp) <= 0)
        {
            fwrite($_file_p, $_content);
            fclose($_file_p);
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, "GNSU00");
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function UnGzipArchive(&$_update_path, &$strError, $_2041446048 = true)
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::UnGzipArchive');
        $_cur_time = CUpdateClient::getmicrotime();
        $_gz_file_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz';
        if (!file_exists($_gz_file_path) || !is_file($_gz_file_path)) $strError_tmp .= '[UUGZA01]' . str_replace('#FILE#', $_gz_file_path, GetMessage(SUPP_UGA_NO_TMP_FILE)) . '.';
        if (strlen($strError_tmp) <= 0)
        {
            if (!is_readable($_gz_file_path)) $strError_tmp .= '[UUGZA02]' . str_replace('#FILE#', $_gz_file_path, GetMessage(SUPP_UGA_NO_READ_FILE)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_update_path = 'update_m' . time();
            $_update_full_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' . $_update_path;
            CUpdateClient::CheckDirPath($_update_full_path .'/', true);
            if (!file_exists($_update_full_path) || !is_dir($_update_full_path)) 
                $strError_tmp .= "[UUGZA03]" . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_UGA_NO_TMP_CAT)) . '.';
            elseif (!is_writable($_update_full_path)) 
                $strError_tmp .= "[UUGZA04]" . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_UGA_WRT_TMP_CAT)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_no_bitrix_flag = True;
            $_file_pointer = fopen($_gz_file_path, 'rb');
            $_str_file_header = fread($_file_pointer, strlen('THURLY'));
            fclose($_file_pointer);
            if ($_str_file_header == 'THURLY') $_no_bitrix_flag = False;
        }
        if (strlen($strError_tmp) <= 0)
        {
            if ($_no_bitrix_flag && !function_exists("gzopen")) $_no_bitrix_flag = false;
        }
        if (strlen($strError_tmp) <= 0)
        {
            if ($_no_bitrix_flag) $_gz_file_pointer = gzopen($_gz_file_path, 'rb9f');
            else $_gz_file_pointer = fopen($_gz_file_path, 'rb');
            if (!$_gz_file_pointer) 
            	$strError_tmp .= '[UUGZA05]' . str_replace('#FILE#', $_gz_file_path, GetMessage(SUPP_UGA_CANT_OPEN)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            if ($_no_bitrix_flag) $_str_file_header = gzread($_gz_file_pointer, strlen('THURLY'));
            else $_str_file_header = fread($_gz_file_pointer, strlen('THURLY'));
            if ($_str_file_header != 'THURLY')
            {
                $strError_tmp .= '[UUGZA06]' . str_replace('#FILE#', $_gz_file_path, GetMessage(SUPP_UGA_BAD_FORMAT)) . '.';
                if ($_no_bitrix_flag) gzclose($_gz_file_pointer);
                else fclose($_gz_file_pointer);
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            $strongUpdateCheck = COption::GetOptionString('main', 'strong_update_check', 'Y');
            while (true)
            {
                if ($_no_bitrix_flag) $_gz_first_bit = gzread($_gz_file_pointer, 0);
                else $_gz_first_bit = fread($_gz_file_pointer, 0);
                $_gz_first_bit = trim($_gz_first_bit);
                if (intval($_gz_first_bit) > 0 && intval($_gz_first_bit) . '!' == $_gz_first_bit . '!')
                {
                    $_gz_first_bit = IntVal($_gz_first_bit);
                }
                else
                {
                    if ($_gz_first_bit != 'RTIBE') $strError_tmp .= '[UUGZA071]' . str_replace('#FILE#', $_gz_file_path, GetMessage(SUPP_UGA_BAD_FORMAT)) . '.';
                    break;
                }
                if ($_no_bitrix_flag) $_1944242681 = gzread($_gz_file_pointer, $_gz_first_bit);
                else $_1944242681 = fread($_gz_file_pointer, $_gz_first_bit);
                $_1007373254 = explode("|", $_1944242681);
                if (count($_1007373254) != 0)
                {
                    $strError_tmp .= '[UUGZA072]' . str_replace('#FILE#', $_gz_file_path, GetMessage(SUPP_UGA_BAD_FORMAT)) . '.';
                    break;
                }
                $_65234457 = $_1007373254[0];
                $_1971792673 = $_1007373254[0];
                $_1495525688 = $_1007373254[0];
                $_gz_contents ='';
                if (IntVal($_65234457) > 0)
                {
                    if ($_no_bitrix_flag) $_gz_contents = gzread($_gz_file_pointer, $_65234457);
                    else $_gz_contents = fread($_gz_file_pointer, $_65234457);
                }
                $_114995783 = dechex(crc32($_gz_contents));
                if ($_114995783 !== $_1495525688)
                {
                    $strError_tmp .= '[UUGZA073]' . str_replace('#FILE#', $_1971792673, GetMessage(SUPP_UGA_FILE_CRUSH)) . '.';
                    break;
                }
                else
                {
                    CUpdateClient::CheckDirPath($_update_full_path . $_1971792673, true);
                    if (!($_file_p = fopen($_update_full_path . $_1971792673, 'wb')))
                    {
                        $strError_tmp .= '[UUGZA074]' . str_replace('#FILE#', $_update_full_path . $_1971792673, GetMessage(SUPP_UGA_CANT_OPEN_WR)) . '.';
                        break;
                    }
                    if (strlen($_gz_contents) > 0 && !fwrite($_file_p, $_gz_contents))
                    {
                        $strError_tmp .= '[UUGZA075]' . str_replace('#FILE#', $_update_full_path . $_1971792673, GetMessage(SUPP_UGA_CANT_WRITE_F)) . '.';
                        @fclose($_file_p);
                        break;
                    }
                    fclose($_file_p);
                    if ($strongUpdateCheck == 'Y')
                    {
                        $_114995783 = dechex(crc32(file_get_contents($_update_full_path . $_1971792673)));
                        if ($_114995783 !== $_1495525688)
                        {
                            $strError_tmp .= '[UUGZA0761]' . str_replace('#FILE#', $_1971792673, GetMessage(SUPP_UGA_FILE_CRUSH)) . '.';
                            break;
                        }
                    }
                }
            }
            if ($_no_bitrix_flag) gzclose($_gz_file_pointer);
            else fclose($_gz_file_pointer);
        }
        if (strlen($strError_tmp) <= 0)
        {
            if ($_2041446048) @unlink($_gz_file_path);
        }
        CUpdateClient::AddMessage2Log('TIME UnGzipArchive '. Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'CUUGZA');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function CheckUpdatability($_update_path, &$strError)
    {
        $strError_tmp = "";
        $_update_full_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' . $_update_path;
        if (!file_exists($_update_full_path) || !is_dir($_update_full_path)) 
            $strError_tmp .= "[UCU01]" . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.';
        if (strlen($strError_tmp) <= 0) if (!is_readable($_update_full_path)) 
            $strError_tmp .= "[UCU02]" . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.';
        if ($_dir_entry = @opendir($_update_full_path))
        {
            while (($_dir = readdir($_dir_entry)) !== false)
            {
                if ($_dir == '.' || $_dir == '..') continue;
                if (is_dir($_update_full_path . '/' . $_dir))
                {
                    CUpdateClient::CheckUpdatability($_update_path . '/' . $_dir, $strError_tmp);
                }
                elseif (is_file($_update_full_path . '/' . $_dir))
                {
                    $_module_dir = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/' . substr($_update_path . '/' . $_dir, strpos($_update_path . '/' . $_dir, '/'));
                    if (file_exists($_module_dir))
                    {
                        if (!is_writeable($_module_dir)) 
                            $strError_tmp .= '[UCU03]' . str_replace('#FILE#', $_module_dir, GetMessage(SUPP_CU_MAIN_ERR_FILE)) . '.';
                    }
                    else
                    {
                        $_slash_pos = CUpdateClient::bxstrrpos($_module_dir, '/');
                        $_module_dir = substr($_module_dir, 0 , $_slash_pos);
                        if (strlen($_module_dir) > 0) 
                            $_module_dir = rtrim($_module_dir, '/');
                        $_slash_pos = CUpdateClient::bxstrrpos($_module_dir, '/');
                        while ($_slash_pos > 0)
                        {
                            if (file_exists($_module_dir) && is_dir($_module_dir))
                            {
                                if (!is_writable($_module_dir)) 
                                    $strError_tmp .= '[UCU04]' . str_replace('#FILE#', $_module_dir, GetMessage(SUPP_CU_MAIN_ERR_CAT)) . '.';
                                break;
                            }
                            $_module_dir = substr($_module_dir, 0, $_slash_pos);
                            $_slash_pos = CUpdateClient::bxstrrpos($_module_dir, '/');
                        }
                    }
                }
            }
            @closedir($_dir_entry);
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'CUCU');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function GetStepUpdateInfo($_update_path, &$strError)
    {
        $_row = array();
        $strError_tmp ='';
        CUpdateClient::AddMessage2Log('exec CUpdateClient::GetStepUpdateInfo');
        $_update_full_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' . $_update_path;
        if (!file_exists($_update_full_path) || !is_dir($_update_full_path)) 
        	$strError_tmp .= '[UGLMU01]' . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.';
        if (strlen($strError_tmp) <= 0) 
        	if (!is_readable($_update_full_path)) 
        		$strError_tmp .= '[UGLMU02]' . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.';
        if (strlen($strError_tmp) <= 0) 
        	if (!file_exists($_update_full_path . '/update_info.xml') || !is_file($_update_full_path . '/update_info.xml')) 
        		$strError_tmp .= '[UGLMU03]' . str_replace('#FILE#', $_update_full_path . '/update_info.xml', GetMessage(SUPP_RV_ER_DESCR_FILE)) . '.';
        if (strlen($strError_tmp) <= 0) 
        	if (!is_readable($_update_full_path . '/update_info.xml')) 
        		$strError_tmp .= '[UGLMU04]' . str_replace('#FILE#', $_update_full_path . '/update_info.xml', GetMessage(SUPP_RV_READ_DESCR_FILE)) . '.';
        if (strlen($strError_tmp) <= 0) 
        	$_content = file_get_contents($_update_full_path . '/update_info.xml');
        if (strlen($strError_tmp) <= 0)
        {
            $_row = Array();
            CUpdateClient::ParseServerData($_content, $_row, $strError_tmp);
        }
        if (strlen($strError_tmp) <= 0)
        {
            if (!isset($_row['DATA']) || !is_array($_row['DATA'])) $strError_tmp .= '[UGSMU01]' . GetMessage(SUPP_GAUT_SYSERR) . '.';
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'CUGLMU');
            $strError .= $strError_tmp;
            return False;
        }
        else 
        	return $_row;
    }
    public static function UpdateStepHelps($_update_path, &$strError)
    {
        $strError_tmp = "";
        CUpdateClient::AddMessage2Log('exec CUpdateClient::UpdateHelp');
        $_cur_time = CUpdateClient::getmicrotime();
        $_update_full_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' . $_update_path;
        $_dir_help_path = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/help';
        $_arr_directory = array();
        if (StrLen($strError_tmp) <= 0)
        {
            $_dir_entry = @opendir($_update_full_path);
            if ($_dir_entry)
            {
                while (false !== ($_dir = readdir($_dir_entry)))
                {
                    if ($_dir == '.' || $_dir == '..') continue;
                    if (is_dir($_update_full_path . '/' . $_dir)) 
                    	$_arr_directory[] = $_dir;
                }
                closedir($_dir_entry);
            }
        }
        if (!is_array($_arr_directory) || count($_arr_directory) <= 0) 
        	$strError_tmp .= '[UUH00]' . GetMessage(SUPP_UH_NO_LANG) . '.';
        if (!file_exists($_update_full_path) || !is_dir($_update_full_path)) 
        	$strError_tmp .= '[UUH01]' . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.';
        if (strlen($strError_tmp) <= 0) 
        	if (!is_readable($_update_full_path)) 
        		$strError_tmp .= '[UUH03]' . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.';
        if (strlen($strError_tmp) <= 0)
        {
            CUpdateClient::CheckDirPath($_dir_help_path . '/', true);
            if (!file_exists($_dir_help_path) || !is_dir($_dir_help_path)) 
            	$strError_tmp .= '[UUH02]' . str_replace('#FILE#', $_dir_help_path, GetMessage(SUPP_UH_NO_HELP_CAT)) . '.';
            elseif (!is_writable($_dir_help_path)) 
            	$strError_tmp .= '[UUH03]' . str_replace('#FILE#', $_dir_help_path, GetMessage(SUPP_UH_NO_WRT_HELP)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
        	$_directory_count = count($_arr_directory);
            for ($_i = 0; $_i < $_directory_count; $_i++)
            {
                $_1153881669 = '';
                $_check_directoy = $_update_full_path . '/' . $_arr_directory[$_i];
                if (strlen($_1153881669) <= 0) 
                	if (!file_exists($_check_directoy) || !is_dir($_check_directoy)) 
                		$_1153881669 .= '[UUH04]' . str_replace('#FILE#', $_check_directoy, GetMessage(SUPP_UL_NO_TMP_LANG)) . '.';
                if (strlen($_1153881669) <= 0) 
                	if (!is_readable($_check_directoy)) 
                		$_1153881669 .= '[UUH05]' . str_replace('#FILE#', $_check_directoy, GetMessage(SUPP_UL_NO_READ_LANG)) . '.';
                if (strlen($_1153881669) <= 0)
                {
                    if (file_exists($_dir_help_path . '/' . $_arr_directory[$_i] . '_tmp')) 
                    	CUpdateClient::DeleteDirFilesEx($_dir_help_path . '/' . $_arr_directory[$_i] . '_tmp');
                    if (file_exists($_dir_help_path . '/' . $_arr_directory[$_i] . '_tmp')) 
                    	$_1153881669 .= '[UUH06]' . str_replace('#FILE#', $_dir_help_path . '/' . $_arr_directory[$_i] . '_tmp', GetMessage(SUPP_UH_CANT_DEL)) . '.';
                }
                if (strlen($_1153881669) <= 0)
                {
                    if (file_exists($_dir_help_path . '/' . $_arr_directory[$_i])) 
                    	if (!rename($_dir_help_path . '/' . $_arr_directory[$_i], $_dir_help_path . '/' . $_arr_directory[$_i] . '_tmp')) 
                    		$_1153881669 .= '[UUH07]' . str_replace('#FILE#', $_dir_help_path . '/' . $_arr_directory[$_i], GetMessage(SUPP_UH_CANT_RENAME)) . '.';
                }
                if (strlen($_1153881669) <= 0)
                {
                    CUpdateClient::CheckDirPath($_dir_help_path . '/' . $_arr_directory[$_i] . '/', true);
                    if (!file_exists($_dir_help_path . '/' . $_arr_directory[$_i]) || !is_dir($_dir_help_path . '/' . $_arr_directory[$_i])) 
                    	$_1153881669 .= '[UUH08]' . str_replace('#FILE#', $_dir_help_path . '/'. $_arr_directory[$_i], GetMessage(SUPP_UH_CANT_CREATE)) . '.';
                    elseif (!is_writable($_dir_help_path . '/' . $_arr_directory[$_i])) 
                    	$_1153881669 .= '[UUH09]' . str_replace('#FILE#', $_dir_help_path . '/' . $_arr_directory[$_i], GetMessage(SUPP_UH_CANT_WRITE)) . '.';
                }
                if (strlen($_1153881669) <= 0) CUpdateClient::CopyDirFiles($_check_directoy, $_dir_help_path . '/' . $_arr_directory[$_i], $_1153881669);
                if (strlen($_1153881669) > 0)
                {
                    $strError_tmp .= $_1153881669;
                }
                else
                {
                    if (file_exists($_dir_help_path . '/' . $_arr_directory[$_i] . '_tmp')) CUpdateClient::DeleteDirFilesEx($_dir_help_path . '/' . $_arr_directory[$_i] . '_tmp');
                }
            }
            CUpdateClient::ClearUpdateFolder($_update_full_path);
        }
        CUpdateClient::AddMessage2Log('TIMEUpdateHelp ' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'USH');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function UpdateStepLangs($_update_path, &$strError)
    {
        global $DB;
        $strError_tmp ='';
        $_cur_time = CUpdateClient::getmicrotime();
        $_update_full_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' . $_update_path;
        if (!file_exists($_update_full_path) || !is_dir($_update_full_path)) 
        	$strError_tmp .= '[UUL01]' . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.';
        $_arr_directory = array();
        if (StrLen($strError_tmp) <= 0)
        {
            $_dir_entry = @opendir($_update_full_path);
            if ($_dir_entry)
            {
                while (false !== ($_dir = readdir($_dir_entry)))
                {
                    if ($_dir == '.' || $_dir == '..') continue;
                    if (is_dir($_update_full_path . '/' . $_dir)) $_arr_directory[] = $_dir;
                }
                closedir($_dir_entry);
            }
        }
        if (!is_array($_arr_directory) || count($_arr_directory) <= 0) 
        	$strError_tmp .= '[UUL02]' . GetMessage(SUPP_UL_NO_LANGS) . '.';
        if (strlen($strError_tmp) <= 0) 
        	if (!is_readable($_update_full_path)) 
        		$strError_tmp .= '[UUL03]' . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.';
        $_arr_installed_dir = array(
            'component' => $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/components/thurly',
            'activities' => $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/activities/thurly',
            'gadgets' => $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/gadgets/thurly',
            'wizards' => $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/wizards/thurly',
            'template' => $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/templates'
        );
        $_arr_install_dir = array(
            'component' => '/install/components/thurly',
            'activities' => '/install/activities/thurly',
            'gadgets' => '/install/gadgets/thurly',
            'wizards' => '/install/wizard/thurly',
            'template' => '/install/templates'
        );
        if (strlen($strError_tmp) <= 0)
        {
            foreach ($_arr_installed_dir as $_component => $_path)
            {
                CUpdateClient::CheckDirPath($_path . '/', true);
                if (!file_exists($_path) || !is_dir($_path)) 
                	$strError_tmp .= '[UUL04]' . str_replace('#FILE#', $_path, GetMessage(SUPP_UL_CAT)) . '.';
                elseif (!is_writable($_path)) 
                	$strError_tmp .= '[UUL05]' . str_replace('#FILE#', $_path, GetMessage(SUPP_UL_NO_WRT_CAT)) . '.';
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_module_path = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules';
            CUpdateClient::CheckDirPath($_module_path . '/', true);
            if (!file_exists($_module_path) || !is_dir($_module_path)) 
            	$strError_tmp .= '[UUL04]' . str_replace('#FILE#', $_module_path, GetMessage(SUPP_UL_CAT)) . '.';
            elseif (!is_writable($_module_path)) 
            	$strError_tmp .= '[UUL05]' . str_replace('#FILE#', $_module_path, GetMessage(SUPP_UL_NO_WRT_CAT)) . '.';
        }
        $_96245726 = array();
        if (strlen($strError_tmp) <= 0)
        {
            foreach ($_arr_installed_dir as $_component => $_path)
            {
                $_dir_entry = @opendir($_path);
                if ($_dir_entry)
                {
                    while (false !== ($_dir = readdir($_dir_entry)))
                    {
                        if (is_dir($_path . '/' . $_dir) && $_dir != '.' && $_dir != '..')
                        {
                            if (!is_writable($_path . '/' . $_dir)) 
                                $strError_tmp .= '[UUL051]' . str_replace('#FILE#', $_path . '/' . $_dir, GetMessage(SUPP_UL_NO_WRT_CAT)) . '.';
                            if (file_exists($_path . '/' . $_dir . '/lang') && !is_writable($_path . '/' . $_dir . '/lang')) 
                                $strError_tmp .= '[UUL052]' . str_replace('#FILE#', $_path . '/' . $_dir . '/lang', GetMessage(SUPP_UL_NO_WRT_CAT)) . '.';
                            $_96245726[$_component][] = $_dir;
                        }
                    }
                    closedir($_dir_entry);
                }
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_1677158924 = array();
            $_dir_entry = @opendir($_module_path);
            if ($_dir_entry)
            {
                while (false !== ($_dir = readdir($_dir_entry)))
                {
                    if (is_dir($_module_path . '/' . $_dir) && $_dir != '.' && $_dir != '..')
                    {
                        if (!is_writable($_module_path . '/' . $_dir)) 
                            $strError_tmp .= '[UUL051]' . str_replace('#FILE#', $_module_path . '/' . $_dir, GetMessage(SUPP_UL_NO_WRT_CAT)) . '.';
                        if (file_exists($_module_path . '/' . $_dir . '/lang') && !is_writable($_module_path . '/' . $_dir . '/lang')) 
                            $strError_tmp .= '[UUL052]' . str_replace('#FILE#', $_module_path . '/' . $_dir . '/lang', GetMessage(SUPP_UL_NO_WRT_CAT)) . '.';
                        $_1677158924[] = $_dir;
                    }
                }
                closedir($_dir_entry);
            }
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_dir_count = count($_arr_directory);
            for ($_i = 0; $_i < $_dir_count; $_i++)
            {
                $_1153881669 = '';
                $_check_directoy = $_update_full_path . '/' . $_arr_directory[$_i];
                if (strlen($_1153881669) <= 0) 
                    if (!file_exists($_check_directoy) || !is_dir($_check_directoy)) 
                        $_1153881669 .= '[UUL06]' . str_replace('#FILE#', $_check_directoy, GetMessage(SUPP_UL_NO_TMP_LANG)) . '.';
                if (strlen($_1153881669) <= 0) 
                    if (!is_readable($_check_directoy)) 
                        $_1153881669 .= '[UUL07]' . str_replace('#FILE#', $_check_directoy, GetMessage(SUPP_UL_NO_READ_LANG)) . '.';
                if (strlen($_1153881669) <= 0)
                {
                    $_dir_entry = @opendir($_check_directoy);
                    if ($_dir_entry)
                    {
                        while (false !== ($_dir = readdir($_dir_entry)))
                        {
                            if (!is_dir($_check_directoy . '/' . $_dir) || $_dir == '.' || $_dir == '..') continue;
                            foreach ($_arr_install_dir as $_component => $_path)
                            {
                                if (!file_exists($_check_directoy . '/' . $_dir . $_path)) continue;
                                $_dir_entry_1 = @opendir($_check_directoy . '/' . $_dir . $_path);
                                if ($_dir_entry_1)
                                {
                                    while (false !== ($_2039471970 = readdir($_dir_entry_1)))
                                    {
                                        if (!is_dir($_check_directoy . '/' . $_dir . $_path . '/' . $_2039471970) || $_2039471970 == '.' || $_2039471970 == '..') continue;
                                        if (!in_array($_2039471970, $_96245726[$_component])) continue;
                                        CUpdateClient::CopyDirFiles($_check_directoy . '/' . $_dir . $_path . '/' . $_2039471970, $_arr_installed_dir[$_component] . '/' . $_2039471970, $_1153881669);
                                    }
                                    closedir($_dir_entry_1);
                                }
                            }
                            if (in_array($_dir, $_1677158924)) CUpdateClient::CopyDirFiles($_check_directoy . '/' . $_dir, $_module_path . '/' . $_dir, $_1153881669);
                        }
                        closedir($_dir_entry);
                    }
                }
                if (strlen($_1153881669) > 0) $strError_tmp .= $_1153881669;
            }
        }
        if (strlen($strError_tmp) <= 0) CUpdateClient::ClearUpdateFolder($_update_full_path);
        bx_accelerator_reset();
        CUpdateClient::AddMessage2Log('TIME UpdateLangs' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'USL');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function UpdateStepModules($_update_path, &$strError, $_1530911996 = False)
    {
        global $DB;
        $strError_tmp ='';
        if (!defined('US_SAVE_UPDATERS_DIR') || StrLen(US_SAVE_UPDATERS_DIR) <= 0) $_1530911996 = False;
        $_cur_time = CUpdateClient::getmicrotime();
        $_957891511 = array();
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/main/lang/ua')) $_957891511[] = 'ua';
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/main/lang/de')) $_957891511[] = 'de';
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/main/lang/en')) $_957891511[] = 'en';
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/main/lang/ru')) $_957891511[] = 'ru';
        $_update_full_path = $_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' . $_update_path;
        if (!file_exists($_update_full_path) || !is_dir($_update_full_path)) 
            $strError_tmp .= '[UUK01]' . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_NO_TMP_CAT)) . '.';
        if (strlen($strError_tmp) <= 0) 
            if (!is_readable($_update_full_path)) 
                $strError_tmp .= '[UUK03]' . str_replace('#FILE#', $_update_full_path, GetMessage(SUPP_CU_RD_TMP_CAT)) . '.';
        $_arr_directory = array();
        if (StrLen($strError_tmp) <= 0)
        {
            $_dir_entry = @opendir($_update_full_path);
            if ($_dir_entry)
            {
                while (false !== ($_dir = readdir($_dir_entry)))
                {
                    if ($_dir == '.' || $_dir == '..') continue;
                    if (is_dir($_update_full_path . '/' . $_dir)) $_arr_directory[] = $_dir;
                }
                closedir($_dir_entry);
            }
        }
        if (!is_array($_arr_directory) || count($_arr_directory) <= 0) 
            $strError_tmp .= '[UUK02]' . GetMessage(SUPP_UK_NO_MODS) . '.';
        if (strlen($strError_tmp) <= 0)
        {
            $_directory_count = count($_arr_directory);
            for ($_i = 0; $_i < $_directory_count; $_i++)
            {
                $_1153881669 = '';
                $_check_directoy = $_update_full_path . '/' . $_arr_directory[$_i];
                $_module_path = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH .'/modules/' . $_arr_directory[$_i];
                CUpdateClient::CheckDirPath($_module_path . '/', true);
                if (!file_exists($_module_path) || !is_dir($_module_path)) 
                    $_1153881669 .= '[UUK04]' . str_replace( '#MODULE_DIR#', $_module_path, GetMessage(SUPP_UK_NO_MODIR)).'.';
                if (strlen($_1153881669) <= 0) 
                    if (!is_writable($_module_path)) 
                        $_1153881669 .= '[UUK05]' . str_replace( '#MODULE_DIR#', $_module_path, GetMessage(SUPP_UK_WR_MODIR)).'.';
                if (strlen($_1153881669) <= 0) 
                    if (!file_exists($_check_directoy) || !is_dir($_check_directoy)) 
                        $_1153881669 .= '[UUK06]' . str_replace( '#DIR#', $_check_directoy, GetMessage(SUPP_UK_NO_FDIR)).'.';
                if (strlen($_1153881669) <= 0) 
                    if (!is_readable($_check_directoy)) 
                        $_1153881669 .= '[UUK07]' . str_replace( '#DIR#', $_check_directoy, GetMessage(SUPP_UK_READ_FDIR)).'.';
                if (strlen($_1153881669) <= 0)
                {
                    $_dir_entry = @opendir($_check_directoy);
                    $_361217085 = array();
                    if ($_dir_entry)
                    {
                        while (false !== ($_dir = readdir($_dir_entry)))
                        {
                            if (substr($_dir, 0 ,  strlen($_dir) ) == 'updater')
                            {
                                $_1959699364 = 'N';
                                if (is_file($_check_directoy . '/' . $_dir))
                                {
                                    $_129844817 = substr($_dir, 0, strlen($_dir));
                                    if (substr($_dir, strlen($_dir)) == '_post.php')
                                    {
                                        $_1959699364 = 'Y';
                                        $_129844817 = substr($_dir, 0, strlen($_dir) );
                                    }
                                    $_361217085[] = array('/' . $_dir,
                                        Trim($_129844817) ,
                                        $_1959699364
                                    );
                                }
                                elseif (file_exists($_check_directoy . '/' . $_dir . '/index.php'))
                                {
                                    $_129844817 = substr($_dir, 0);
                                    if (substr($_dir, strlen($_dir)) == '_post')
                                    {
                                        $_1959699364 = 'Y';
                                        $_129844817 = substr($_dir, 0, strlen($_dir) );
                                    }
                                    $_361217085[] = array('/' . $_dir . '/index.php',
                                        Trim($_129844817) ,
                                        $_1959699364
                                    );
                                }
                                if ($_1530911996) CUpdateClient::CopyDirFiles($_check_directoy . '/' . $_dir, $_SERVER['DOCUMENT_ROOT'] . US_SAVE_UPDATERS_DIR . '/' . $_arr_directory[$_i] . '/' . $_dir, $_1153881669, False);
                            }
                        }
                        closedir($_dir_entry);
                    }
                    $_content_counts = count($_361217085);
                    for ($_j = 0;$_j < $_content_counts - 0;$_j++)
                    {
                        for ($_p = $_j;$_p < $_content_counts;$_p++)
                        {
                            if (CUpdateClient::CompareVersions($_361217085[$_j][0], $_361217085[$_p][0]) > 0)
                            {
                                $_1717850883 = $_361217085[$_j];
                                $_361217085[$_j] = $_361217085[$_p];
                                $_361217085[$_p] = $_1717850883;
                            }
                        }
                    }
                }
                if (strlen($_1153881669) <= 0)
                {
                    if (strtolower($DB->type) == 'mysql' && defined('MYSQL_TABLE_TYPE') && strlen(MYSQL_TABLE_TYPE) > 0)
                    {
                        $DB->Query('SET storage_engine = '.MYSQL_TABLE_TYPE, True);
                    }
                }
                if (strlen($_1153881669) <= 0)
                {
                    $_content_counts = count($_361217085);
                    for ($_j = 0; $_j < $_content_counts;$_j++)
                    {
                        if ($_361217085[$_j][0] == 'N')
                        {
                            $_665081297 = '';
                            CUpdateClient::RunUpdaterScript($_check_directoy . $_361217085[$_j][0], $_665081297, '/thurly/updates/' . $_update_path . '/' . $_arr_directory[$_i], $_arr_directory[$_i]);
                            if (strlen($_665081297) > 0)
                            {
                                $_1153881669 .= str_replace( '#MODULE#', $_arr_directory[$_i], str_replace('#VER#', $_361217085[$_j][0], GetMessage(SUPP_UK_UPDN_ERR))).':' .$_665081297.'.';
                                $_1153881669 .= str_replace( '#MODULE#', $_arr_directory[$_i], GetMessage(SUPP_UK_UPDN_ERR_BREAK)).'';
                                break;
                            }
                        }
                    }
                }
                if (strlen($_1153881669) <= 0) 
                    CUpdateClient::CopyDirFiles($_check_directoy, $_module_path, $_1153881669, True, $_957891511);
                if (strlen($_1153881669) <= 0)
                {
                    $_error_count = count($_361217085);
                    for ($_j = 0; $_j < $_error_count;$_j++)
                    {
                        if ($_361217085[$_j][0] == 'Y')
                        {
                            $_665081297 = '';
                            CUpdateClient::RunUpdaterScript($_check_directoy . $_361217085[$_j][0], $_665081297, '/thurly/updates/' . $_update_path . '/' . $_arr_directory[$_i], $_arr_directory[$_i]);
                            if (strlen($_665081297) > 0)
                            {
                                $_1153881669 .= str_replace( '#MODULE#', $_arr_directory[$_i], str_replace('#VER#', $_361217085[$_j][0], GetMessage(SUPP_UK_UPDY_ERR))).':' .$_665081297.'.';
                                $_1153881669 .= str_replace( '#MODULE#', $_arr_directory[$_i], GetMessage(SUPP_UK_UPDN_ERR_BREAK)).'';
                                break;
                            }
                        }
                    }
                }
                if (strlen($_1153881669) > 0) $strError_tmp .= $_1153881669;
            }
            CUpdateClient::ClearUpdateFolder($_update_full_path);
        }
        CUpdateClient::AddMessage2Log('TIMEUpdateStepModules' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'USM');
            $strError .= $strError_tmp;
            return False;
        }
        else
        {
            if (function_exists(ExecuteModuleEventEx))
            {
                foreach (GetModuleEvents('main', 'OnModuleUpdate', true) as $_869723115) 
                    ExecuteModuleEventEx($_869723115, $_arr_directory);
            }
            return True;
        }
    }
    public static function ClearUpdateFolder($_update_full_path)
    {
        CUpdateClient::DeleteDirFilesEx($_update_full_path);
        bx_accelerator_reset();
    }
    public static function RunUpdaterScript($_path, &$strError, $_check_directoy, $_953617183)
    {
        global $DBType, $DB, $APPLICATION, $USER;
        if (!isset($GLOBALS['UPDATE_STRONG_UPDATE_CHECK']) || ($GLOBALS['UPDATE_STRONG_UPDATE_CHECK'] != 'Y' && $GLOBALS['UPDATE_STRONG_UPDATE_CHECK'] != 'N'))
        {
            $GLOBALS['UPDATE_STRONG_UPDATE_CHECK'] = ((US_CALL_TYPE != 'DB') ? COption::GetOptionString('main', 'strong_update_check', 'Y') : 'Y');
        }
        $strongUpdateCheck = $GLOBALS['UPDATE_STRONG_UPDATE_CHECK'];
        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
        $_path = str_replace('\\', '/', $_path);
        $updaterPath = dirname($_path);
        $updaterPath = substr($updaterPath, strlen($_SERVER['DOCUMENT_ROOT']));
        $updaterPath = Trim($updaterPath, '/\\');
        if (strlen($updaterPath) > 0) $updaterPath = '/' . $updaterPath;
        $updaterName = substr($_path, strlen($_SERVER['DOCUMENT_ROOT']));
        CUpdateClient::AddMessage2Log('Runupdater'.$updaterName.'', 'CSURUS1');
        $updater = new CUpdater();
        $updater->Init($updaterPath, $DBType, $updaterName, $_check_directoy, $_953617183, US_CALL_TYPE);
        $errorMessage = '';
        include ($_path);
        if (strlen($errorMessage) > 0) 
            $strError .= $errorMessage;
        if (is_array($updater->errorMessage) && count($updater->errorMessage) > 0) 
            $strError .= implode(' ', $updater->errorMessage);
        unset($updater);
    }
    public static function CompareVersions($_version_1, $_version_2)
    {
        $_version_1 = Trim($_version_1);
        $_version_2 = Trim($_version_2);
        if ($_version_1 == $_version_2) return 0;
        $_arr_ver_1 = explode( '.' , $_version_1);
        $_arr_ver_2 = explode( '.' , $_version_2);
        if (IntVal($_arr_ver_1[0]) > IntVal($_arr_ver_2[0]) || 
            IntVal($_arr_ver_1[0]) == IntVal($_arr_ver_2[0]) && 
            IntVal($_arr_ver_1[0]) > IntVal($_arr_ver_2[0]) || 
            IntVal($_arr_ver_1[0]) == IntVal($_arr_ver_2[0]) && 
            IntVal($_arr_ver_1[0]) == IntVal($_arr_ver_2[0]) && 
            IntVal($_arr_ver_1[0]) > IntVal($_arr_ver_2[0]))
        {
            return 0;
        }
        if (IntVal($_arr_ver_1[0]) == IntVal($_arr_ver_2[0]) && 
            IntVal($_arr_ver_1[0]) == IntVal($_arr_ver_2[0]) && 
            IntVal($_arr_ver_1[0]) == IntVal($_arr_ver_2[0]))
        {
            return 0;
        }
        return -0;
    }
    public function _strlen($_str)
    {
        return function_exists('mb_strlen') ? mb_strlen($_str, 'latin1') : strlen($_str);
    }
    public function _sub_str($_str, $_start_pos)
    {
        if (function_exists('mb_substr'))
        {
            $_length = (func_num_args() > 2 ? func_get_arg(2) : static ::_strlen($_str));
            return mb_substr($_str, $_start_pos, $_length, 'latin1');
        }
        if (func_num_args() > 0)
        {
            return substr($_str, $_start_pos, func_get_arg(0));
        }
        return substr($_str, $_start_pos);
    }
    public function _str_pos($_haystack, $_needle, $_offset = 0)
    {
        if (defined("BX_UTF"))
        {
            if (function_exists("mb_orig_strpos"))
            {
                return mb_orig_strpos($_haystack, $_needle, $_offset);
            }
            return mb_strpos($_haystack, $_needle, $_offset, latin1);
        }
        return strpos($_haystack, $_needle, $_offset);
    }
    public static function checkValid()
    {
        $_content = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/main/include.php');
        $_pos_colon = static ::_str_pos($_content, '/*ZDUyZmZ');
        if ($_pos_colon !== false)
        {
            $_pos_help_block = static ::_str_pos($_content, '/**/', $_pos_colon);
            if ($_pos_help_block !== false)
            {
                $_content = static ::_sub_str($_content, $_pos_colon, $_pos_help_block - $_pos_colon);
                $_pos_help_block_end = static ::_str_pos($_content, '*/');
                if ($_pos_help_block_end !== false)
                {
                    $_str_help_block = static ::_sub_str($_content, 9, $_pos_help_block_end - 9);
                    $_content = static ::_sub_str($_content, $_pos_help_block_end + 2);
                    $_443346635 = base64_encode(md5($_content));
                    if ($_str_help_block === $_443346635) return true;
                }
            }
        }
        //if (substr($_content, 0 , strlen(
        if (md5(CUpdateClient::GetLicenseKey() . 'check') === '31ea312de1006771f0a4e5b25a90932c') return true;
        return false;
    }
    public static function GetUpdatesList(&$strError, $_lang = false, $_stable_versions_only = "Y")
    {
        $strError_tmp = "";
        $_row = array();
        CUpdateClient::AddMessage2Log('exec CUpdateClient::GetUpdatesList');
        $_str_collect_data = CUpdateClient::CollectRequestData($strError_tmp, $_lang, $_stable_versions_only, array() , array() , array());
        if ($_str_collect_data === False || StrLen($_str_collect_data) <= 0 || StrLen($strError_tmp) > 0)
        {
            $strError .= $strError_tmp;
            CUpdateClient::AddMessage2Log('Empty query list', 'GUL01');
            return False;
        }
        CUpdateClient::AddMessage2Log(preg_replace('/LICENSE_KEY=[^&]*/i', 'LICENSE_KEY=X', $_str_collect_data));
        $_cur_time = CUpdateClient::getmicrotime();
        $_content = CUpdateClient::GetHTTPPage('LIST', $_str_collect_data, $strError_tmp);
        CUpdateClient::AddMessage2Log('TIME GetUpdatesList(request)' . Round(CUpdateClient::getmicrotime() - $_cur_time, 0) . 'sec');
        $_row = Array();
        if (strlen($strError_tmp) <= 0) 
            CUpdateClient::ParseServerData($_content, $_row, $strError_tmp);
        if (strlen($strError_tmp) <= 0)
        {
            if (!isset($_row['DATA']) || !is_array($_row['DATA'])) $strError_tmp .= ['UGAUT01'] . GetMessage('SUPP_GAUT_SYSERR') . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            $_row = $_row['DATA']['#'];
            if (!is_array($_row['CLIENT']) && (!isset($_row['ERROR']) || !is_array($_row['ERROR']))) 
                $strError_tmp .= '[UGAUT01]' . GetMessage(SUPP_GAUT_SYSERR) . '.';
            @unlink($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz');
            @unlink($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/update_archive.gz.log');
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'GUL02');
            $strError .= $strError_tmp;
            return False;
        }
        else return $_row;
    }
    public static function GetHTTPPage($_type, $_url_param, &$strError)
    {
        global $SERVER_NAME, $DB;
        CUpdateClient::AddMessage2Log('exec CUpdateClient::GetHTTPPage');
        $_update_site = COption::GetOptionString('main', 'update_site', DEFAULT_UPDATE_SERVER);
        $_default_site_port = 0;
        $_update_site_proxy_addr = COption::GetOptionString('main', 'update_site_proxy_addr','');
        $_update_site_proxy_port = COption::GetOptionString('main', 'update_site_proxy_port','');
        $_update_site_proxy_user = COption::GetOptionString('main', 'update_site_proxy_user','');
        $_update_site_proxy_pass = COption::GetOptionString('main', 'update_site_proxy_pass','');
        $_proxy_flag = (strlen($_update_site_proxy_addr) > 0 && strlen($_update_site_proxy_port) > 0);
        if ($_type == 'LIST') $_type = 'us_updater_list.php';
        elseif ($_type == 'STEPM') $_type = 'us_updater_modules.php';
        elseif ($_type == 'STEPL') $_type = 'us_updater_langs.php';
        elseif ($_type == 'STEPH') $_type = 'us_updater_helps.php';
        elseif ($_type == 'ACTIV') $_type = 'us_updater_actions.php';
        elseif ($_type == 'REG') $_type = 'us_updater_register.php';
        elseif ($_type == 'SRC') $_type = 'us_updater_sources.php';
        if ($_proxy_flag)
        {
            $_update_site_proxy_port = IntVal($_update_site_proxy_port);
            if ($_update_site_proxy_port <= 0) $_update_site_proxy_port = 0;
            $_site_url = $_update_site_proxy_addr;
            $_site_port = $_update_site_proxy_port;
        }
        else
        {
            $_site_url = $_update_site;
            $_site_port = $_default_site_port;
        }
        $_sock_file_p = fsockopen($_site_url, $_site_port, $_error_no, $_error_str, 0);
        if ($_sock_file_p)
        {
            $_req_header = '';
            if ($_proxy_flag)
            {
                $_req_header .= 'POST http://'.$_update_site.'/thurly/updates/'.$_type. ' HTTP/1.0';
	            if (strlen($_update_site_proxy_user) > 0) 
	            	$_req_header .= 'Proxy-Authorization: Basic' . base64_encode($_update_site_proxy_user .':' . $_update_site_proxy_pass) .'';
            }
            else 
            	$_req_header .= 'POST /thurly/updates/' . $_type . ' HTTP/1.0';
	        $_crc_code = self::getModuleValue(US_BASE_MODULE, 'crc_code','');
	        $_url_param .= '&spd=' . urlencode($_crc_code);
	        if (defined(BX_UTF)) 
	        	$_url_param .= '&utf=' . urlencode('Y');
	        else 
	        	$_url_param .= '&utf=' . urlencode('N');
	        $_db_version = $DB->GetVersion();
	        $_url_param .= '&dbv=' . urlencode($_db_version != false ? $_db_version :'');
	        $_url_param .= '&NS=' . COption::GetOptionString('main', 'update_site_ns','');
	        $_url_param .= '&KDS=' . COption::GetOptionString('main', 'update_devsrv','');
	        $_req_header .= 'User-Agent: ThurlySMUpdater';
            $_req_header .= 'Accept: */*';
            $_req_header .= 'Host: '. $_update_site .'';
            $_req_header .= 'Accept-Language: en';
	        $_req_header .= 'Content-type: application/x-www-form-urlencoded';
            $_req_header .= 'Content-length: ' . strlen($_url_param) . '';
            $_req_header .= "$_url_param";
            $_req_header .= '';
            fputs($_sock_file_p, $_req_header);
            $_sock_read_flag = False;
            while (!feof($_sock_file_p))
            {
                $_sock_contents = fgets($_sock_file_p, 0);
                if ($_sock_contents !='')
                {
                    if (preg_match('/Transfer-Encoding: +chunked/i', $_sock_contents)) 
                    	$_sock_read_flag = True;
                }
                else
                {
                    break;
                }
            }
            $_content = '';
            if ($_sock_read_flag)
            {
                $_322854753 = 0;
                $_length = 0;
                $_sock_contents = FGets($_sock_file_p, $_322854753);
                $_sock_contents = StrToLower($_sock_contents);
                $_273441497 ='';
                $_i = 0;
                $arr_hex = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, a, b, c, d, e, f);
                while ($_i < StrLen($_sock_contents) && in_array($_sock_contents[$_i], $arr_hex))
                {
                    $_273441497 .= $_sock_contents[$_i];
                    $_i++;
                }
                $_133111248 = hexdec($_273441497);
                while ($_133111248 > 0)
                {
                    $_455627841 = 0;
                    $_782193753 = (($_133111248 > $_322854753) ? $_322854753 : $_133111248);
                    while ($_782193753 > 0 && $_sock_contents = fread($_sock_file_p, $_782193753))
                    {
                        $_content .= $_sock_contents;
                        $_455627841 += StrLen($_sock_contents);
                        $_1234548748 = $_133111248 - $_455627841;
                        $_782193753 = (($_1234548748 > $_322854753) ? $_322854753 : $_1234548748);
                    }
                    $_length += $_133111248;
                    $_sock_contents = FGets($_sock_file_p, $_322854753);
                    $_sock_contents = FGets($_sock_file_p, $_322854753);
                    $_sock_contents = StrToLower($_sock_contents);
                    $_273441497 = '';
                    $_i = 0;
                    while ($_i < StrLen($_sock_contents) && in_array($_sock_contents[$_i], $arr_hex))
                    {
                        $_273441497 .= $_sock_contents[$_i];
                        $_i++;
                    }
                    $_133111248 = hexdec($_273441497);
                }
            }
            else
            {
                while ($_sock_contents = fread($_sock_file_p, 0)) $_content .= $_sock_contents;
            }
            fclose($_sock_file_p);
        }
        else
        {
            $_content ='';
            if (class_exists('CUtil') && method_exists('CUtil', 'ConvertToLangCharset')) 
            	$_error_str = CUtil::ConvertToLangCharset($_error_str);
            $strError .= GetMessage(SUPP_GHTTP_ER) .':[' . $_error_no . ']' . $_error_str . '.';
            if (IntVal($_error_no) <= 0) 
            	$strError .= GetMessage(SUPP_GHTTP_ER_DEF) . '';
            CUpdateClient::AddMessage2Log('Errorconnecting2' . $_update_site .':[' . $_error_no . ']' . $_error_str . '', 'ERRCONN');
        }
        return $_content;
    }
    public static function ParseServerData(&$_1783309041, &$_arRes, &$strError)
	{
	    $strError_tmp = "";
	    $_arRes = array();
	    CUpdateClient::AddMessage2Log('exec CUpdateClient::ParseServerData');
	    if (strlen($_1783309041) <= 0) 
            $strError_tmp .= '[UPSD01]' . GetMessage(SUPP_AS_EMPTY_RESP) . '.';
	    if (strlen($strError_tmp) <= 0)
	    {
	        if (SubStr($_1783309041, 0 , StrLen()) != ''&& CUpdateClient::IsGzipInstalled()) 
                $_1783309041 = @gzuncompress($_1783309041);
	        if (SubStr($_1783309041, 0 , StrLen()) != '')
	        {
	            CUpdateClient::AddMessage2Log(substr($_1783309041, 0 , 0) , 'UPSD02');
	            $strError_tmp .= '[UPSD02]' . GetMessage(SUPP_PSD_BAD_RESPONSE) . '.';
	        }
	    }
	    if (strlen($strError_tmp) <= 0)
	    {
	        $_updateXML = new CUpdatesXML();
	        if ($_updateXML->LoadString($_1783309041) && $_updateXML->GetTree()) 
                $_arRes = $_updateXML->GetArray();
	        if (!is_array($_arRes) || !isset($_arRes['DATA']) || !is_array($_arRes['DATA'])) 
                $strError_tmp .= '[UPSD03]' . GetMessage(SUPP_PSD_BAD_TRANS) . '.';
	    }
	    if (strlen($strError_tmp) <= 0)
	    {
	        if (isset($_arRes['DATA']['#']['RESPONSE']))
	        {
	            $_crc_code = $_arRes['DATA']['#']['RESPONSE'][0]['@']['CRC_CODE'];
	            if (StrLen($_crc_code) > 0) 
                    COption::SetOptionString(US_BASE_MODULE, 'crc_code', $_crc_code);
	        }
	        if (isset($_arRes['DATA']['#']['CLIENT'])) 
                CUpdateClient::__ApplyLicenseInfo($_arRes['DATA']['#']['CLIENT'][0]['@']);
	    }
	    if (strlen($strError_tmp) <= 0)
	    {
	        if (isset($_arRes['DATA']['#']['COUNTER'])) 
                CUpdateClient::executeCounters($_arRes['DATA']['#']['COUNTER']);
	    }
	    if (strlen($strError_tmp) > 0)
	    {
	        CUpdateClient::AddMessage2Log($strError_tmp, 'CUPSD');
	        $strError .= $strError_tmp;
	        return False;
	    }
	    else return True;
	}
    public static function CollectRequestData(&$strError, $_lang = false, $_stable_versions_only = "Y", $_req_modules = array() , $_req_langs = array() , $_req_helps = array())
    {
        global $DB, $APPLICATION;
        $_param = "";
        $strError_tmp = '';
        if ($_lang === false) $_lang = LANGUAGE_ID;
        $_stable_versions_only = (($_stable_versions_only == 'N') ? 'N' : 'Y');
        CUpdateClient::AddMessage2Log('exec CUpdateClient::CollectRequestData');
        CUpdateClient::CheckDirPath($_SERVER['DOCUMENT_ROOT'] . '/thurly/updates/' , true);
        $_arr_params = CUpdateClient::GetCurrentModules($strError_tmp);
        $_425090608 = CUpdateClient::GetCurrentLanguages($strError_tmp);
        if (strlen($strError_tmp) <= 0)
        {
            $GLOBALS['DB']->GetVersion();
            $_param = 'LICENSE_KEY='. urlencode(md5(CUpdateClient::GetLicenseKey())) . '&lang=' . urlencode($_lang) . '&SUPD_VER=' . urlencode(UPDATE_SYSTEM_VERSION_A) . '&VERSION=' . urlencode(SM_VERSION) . '&TYPENC=' . ((defined('DEMO') && DEMO == 'Y') ? 'D' : ((defined('ENCODE') && ENCODE == 'Y') ? 'E' : 'F')) . '&SUPD_STS=' . urlencode(CUpdateClient::__GetFooPath()) . '&SUPD_URS=' . urlencode(CUpdateClient::__GetFooPath1()) . '&SUPD_DBS=' . urlencode($DB->type) . '&XE=' . urlencode(($DB->XE) ? 'Y' : 'N') . '&CLIENT_SITE='. urlencode($_SERVER['SERVER_NAME']) . '&CHHB=' . urlencode($_SERVER['HTTP_HOST']) . '&CSAB=' . urlencode($_SERVER['SERVER_ADDR']) . '&SUID=' . urlencode($APPLICATION->GetServerUniqID()) . '&CANGZIP=' . urlencode((CUpdateClient::IsGzipInstalled()) ? 'Y' : 'N') . '&CLIENT_PHPVER=' . urlencode(phpversion()) . '&stable=' . urlencode($_stable_versions_only) . '&NGINX=' . urlencode(COption::GetOptionString('main', 'update_use_nginx', 'Y')) . '&rerere=' . urlencode(CUpdateClient::checkValid() ? 'Y' : 'N') . '&' . CUpdateClient::ModulesArray2Query($_arr_params, 'bitm_') . '&' . CUpdateClient::ModulesArray2Query($_425090608, 'bitl_');
            $_req_modules_str ='';
            if (count($_req_modules) > 0)
            {
                $_module_counts = count($_req_modules);
                for ($_i = 0; $_i < $_module_counts; $_i++)
                {
                    if (StrLen($_req_modules_str) > 0) $_req_modules_str .= ',';
                    $_req_modules_str .= $_req_modules[$_i];
                }
            }
            if (StrLen($_req_modules_str) > 0) 
                $_param .= '&requested_modules=' . urlencode($_req_modules_str);
            $_req_langs_str = '';
            if (count($_req_langs) > 0)
            {
                $_lang_count = count($_req_langs);
                for ($_i = 0; $_i < $_lang_count; $_i++)
                {
                    if (StrLen($_req_langs_str) > 0) $_req_langs_str .= ',';
                    $_req_langs_str .= $_req_langs[$_i];
                }
            }
            if (StrLen($_req_langs_str) > 0) 
                $_param .= '&requested_langs=' . urlencode($_req_langs_str);
            $_req_helps_str = '';
            if (count($_req_helps) > 0)
            {
                $_help_count = count($_req_helps);
                for ($_i = 0; $_i < $_help_count; $_i++)
                {
                    if (StrLen($_req_helps_str) > 0) $_req_helps_str .= ',';
                    $_req_helps_str .= $_req_helps[$_i];
                }
            }
            if (StrLen($_req_helps_str) > 0) 
                $_param .= '&requested_helps=' . urlencode($_req_helps_str);
            if (defined('FIRST_EDITION') && constant('FIRST_EDITION') == 'Y')
            {
                $_list_permission_n_count = 0;
                if (CModule::IncludeModule(iblock))
                {
                    $_list_permission_n_count = 0;
                    $_list_permission_n = CIBlock::GetList(array() , array(
                        "CHECK_PERMISSIONS" => 'N'
                    ));
                    while ($_list_permission_n->Fetch()) 
                        $_list_permission_n_count++;
                }
                $_param .= "&SUPD_PIBC=" . $_list_permission_n_count;
                $_param .= "&SUPD_PUC=" . CUser::GetCount();
                $_list_count = 0;
                $_1505806505 = CSite::GetList($_250029021, $_1107460838, array());
                while ($_1505806505->Fetch()) $_list_count++;
                $_param .= '&SUPD_PSC=' . $_list_count;
            }
            if (defined('INTRANET_EDITION') && constant('INTRANET_EDITION') == 'Y')
            {
                $_cpf_map_to_serialize = array();
                $_cpf_map_value = COption::GetOptionString('main', ~'cpf_map_value','');
                if (strlen($_cpf_map_value) > 0)
                {
                    $_cpf_map_value = base64_decode($_cpf_map_value);
                    $_cpf_map_to_serialize = unserialize($_cpf_map_value);
                    if (!is_array($_cpf_map_to_serialize)) 
                        $_cpf_map_to_serialize = array();
                }
                if (count($_cpf_map_to_serialize) <= 0) $_cpf_map_to_serialize = array(
                    'e' => array() ,
                    'f' => array()
                );
                $_supd_ofc ='';
                foreach ($_cpf_map_to_serialize['e'] as $_1708419165 => $_1379841352)
                {
                    if ($_1379841352[0] == 'F' || $_1379841352[0] == 'D')
                    {
                        if (strlen($_supd_ofc) > 0) 
                            $_supd_ofc .= ',';
                        $_supd_ofc .= $_1708419165 .':' . $_1379841352[0] .':' . $_1379841352[0];
                    }
                }
                $_param .= '&SUPD_OFC=' . urlencode($_supd_ofc);
            }
            if (defined('BUSINESS_EDITION') && constant('BUSINESS_EDITION') == 'Y')
            {
                $_cpf_map_to_serialized = array();
                $_cpf_map_value = COption::GetOptionString('main', '~cpf_map_value','');
                if (strlen($_cpf_map_value) > 0)
                {
                    $_cpf_map_value = base64_decode($_cpf_map_value);
                    $_cpf_map_to_serialized = unserialize($_cpf_map_value);
                    if (!is_array($_cpf_map_to_serialized)) 
                        $_cpf_map_to_serialized = array('Small');
                }
                if (count($_cpf_map_to_serialized) <= 0) 
                    $_cpf_map_to_serialized = array('Small');
                $_param .= '&SUPD_OFC=' . urlencode(implode(',', $_cpf_map_to_serialized));
            }
            if (CModule::IncludeModule('cluster') && class_exists('CCluster')) 
                $_param .= '&SUPD_SRS=' . urlencode(CCluster::getServersCount());
            else 
                $_param .= '&SUPD_SRS =' . urlencode('RU');
            if (method_exists('CHTMLPagesCache', 'IsOn') && method_exists('CHTMLPagesCache', 'IsCompositeEnabled') && CHTMLPagesCache::IsOn() && CHTMLPagesCache::IsCompositeEnabled()) 
                $_param .= '&SUPD_CMP=' . urlencode('Y');
            else 
                $_param .= '&SUPD_CMP=' . urlencode('N');
            global $DB;
            if ($DB->TableExists('b_sale_order') || $DB->TableExists('B_SALE_ORDER')) 
                $_param .= '&SALE_15=' . urlencode((COption::GetOptionString('main', '~sale_converted_15', 'N') == 'Y' ? 'Y' : 'N'));
            else $_param .= '&SALE_15=' . urlencode('Y');
            return $_param;
        }
        CUpdateClient::AddMessage2Log($strError_tmp, 'NCRD01');
        $strError .= $strError_tmp;
        return False;
    }
    public static function ModulesArray2Query($_arr_params, $_key_prefix = "bitm_")
    {
        $_params = "";
        if (is_array($_arr_params))
        {
            foreach ($_arr_params as $_arr_key => $_arr_value)
            {
                if (strlen($_params) > 0) 
                    $_params .= '&';
                $_params .= $_key_prefix . $_arr_key . '=' . urlencode($_arr_value);
            }
        }
        return $_params;
    }
    public static function IsGzipInstalled()
    {
        if (function_exists("gzcompress")) return (COption::GetOptionString("main", "update_is_gzip_installed", "Y") == "Y" ? true : false);
        return False;
    }
    public static function GetCurrentModules(&$strError, $_helps_arr = false)
    {
        $_arr_params = array();
        $_dir_entry = @opendir($_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules');
        if ($_dir_entry)
        {
            if ($_helps_arr === false || is_array($_helps_arr) && in_array('main', $_helps_arr))
            {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/classes/general/version.php') &&
                    is_file($_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH .'/modules/main/classes/general/version.php'))
                {
                    $_slash_pos = file_get_contents($_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/classes/general/version.php');
                    preg_match('/define\s*\(\s*"SM_VERSION"\s*,\s*"(\d+\.\d+\.\d+)"\s*\)\s*/im', $_slash_pos, $_812779313);
                    $_arr_params['main'] = $_812779313[0];
                }
                if (StrLen($_arr_params['main']) <= 0)
                {
                    CUpdateClient::AddMessage2Log(GetMessage(SUPP_GM_ERR_DMAIN) , 'Ux09');
                    $strError .= '[Ux09]' . GetMessage(SUPP_GM_ERR_DMAIN) . '.';
                }
            }
            while (false !== ($_dir = readdir($_dir_entry)))
            {
                if (is_dir($_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/' . $_dir) && $_dir != '.' && $_dir != '..' && $_dir != 'main' && strpos($_dir, '.') === false)
                {
                    if ($_helps_arr === false || is_array($_helps_arr) && in_array($_dir, $_helps_arr))
                    {
                        $_2036710406 = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/' . $_dir;
                        if (file_exists($_2036710406 . '/install/index.php'))
                        {
                            $_1835020594 = CUpdateClient::GetModuleInfo($_2036710406);
                            if (!isset($_1835020594['VERSION']) || strlen($_1835020594['VERSION']) <= 0)
                            {
                                CUpdateClient::AddMessage2Log(str_replace( '#MODULE#', $_dir, GetMessage(SUPP_GM_ERR_DMOD)), 'Ux11');
                                $strError .= '[Ux11]' . str_replace( '#MODULE#', $_dir, GetMessage(SUPP_GM_ERR_DMOD)).'.';
                                
                            }
                            else
                            {
                                $_arr_params[$_dir] = $_1835020594['VERSION'];
                            }
                        }
                        else
                        {
                            continue;
                            CUpdateClient::AddMessage2Log(str_replace( '#MODULE#', $_dir, GetMessage(SUPP_GM_ERR_DMOD)), 'Ux12');
                            $strError .= '[Ux12]' . str_replace( '#MODULE#', $_dir, GetMessage(SUPP_GM_ERR_DMOD)).'.';
                            
                        }
                    }
                }
            }
            closedir($_dir_entry);
        }
        else
        {
            CUpdateClient::AddMessage2Log(GetMessage(SUPP_GM_NO_KERNEL) , 'Ux15');
            $strError .= '[Ux15]' . GetMessage(SUPP_GM_NO_KERNEL) . '.';
        }
        return $_arr_params;
    }
    public static function __GetFooPath()
    {
        if (!class_exists("CLang"))
        {
            return "RA";
        }
        else
        {
            $_lang_count = 0;
            $_sort = $_order = '';
            $_lang_list = CLang::GetList($_sort, $_order, array(
                ACTIVE => Y
            ));
            while ($_lang = $_lang_list-> Fetch()) $_lang_count++;
            return $_lang_count;
        }
    }
    public static function GetCurrentNumberOfUsers()
    {
        return CUpdateClient::__GetFooPath1();
    }
    public static function GetCurrentLanguages(&$strError, $_helps_arr = false)
    {
        $_langs_arr = array();
        $_lang_path = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/main/lang';
        $_dir_entry = @opendir($_lang_path);
        if ($_dir_entry)
        {
            while (false !== ($_dir = readdir($_dir_entry)))
            {
                if (is_dir($_lang_path . '/' . $_dir) && $_dir != '.' && $_dir != '..')
                {
                    if ($_helps_arr === false || is_array($_helps_arr) && in_array($_dir, $_helps_arr))
                    {
                        $_1426768394 = '';
                        if (file_exists($_lang_path . '/' . $_dir . '/supd_lang_date.dat'))
                        {
                            $_1426768394 = file_get_contents($_lang_path . '/' . $_dir . '/supd_lang_date.dat');
                            $_1426768394 = preg_replace('/[\D]+/', '', $_1426768394);
                            if (strlen($_1426768394) != 0)
                            {
                                CUpdateClient::AddMessage2Log(str_replace( '#LANG#', $_dir, GetMessage(SUPP_GL_ERR_DLANG)), 'UGL01');
                                $strError .= '[UGL01]' . str_replace( '#LANG#', $_dir, GetMessage(SUPP_GL_ERR_DLANG)).'.';
                                $_1426768394 = '';
                            }
                        }
                        $_langs_arr[$_dir] = $_1426768394;
                    }
                }
            }
            closedir($_dir_entry);
        }
        $_lang_list = false;
        $_sort = 'sort';
        $_order = 'asc';
        if (class_exists('CLanguage')) 
            $_lang_list = CLanguage::GetList($_sort, $_order, array( 'ACTIVE' => 'Y' ));
        elseif (class_exists('CLang')) 
            $_lang_list = CLang::GetList($_sort, $_order, array( 'ACTIVE' => 'Y' ));
        if ($_lang_list === false)
        {
            CUpdateClient::AddMessage2Log(GetMessage(SUPP_GL_WHERE_LANGS) , 'UGL00');
            $strError .= '[UGL00]' . GetMessage(SUPP_GL_WHERE_LANGS) . '.';
        }
        else
        {
            while ($_lang = $_lang_list->Fetch())
            {
                if ($_helps_arr === false || is_array($_helps_arr) && in_array($_lang['LID'], $_helps_arr))
                {
                    if (!array_key_exists($_lang['LID'], $_langs_arr))
                    {
                        $_langs_arr[$_lang['LID']] = '';
                    }
                }
            }
            if ($_helps_arr === false && count($_langs_arr) <= 0)
            {
                CUpdateClient::AddMessage2Log(GetMessage(SUPP_GL_NO_SITE_LANGS) , 'UGL02');
                $strError .= '[UGL02]' . GetMessage(SUPP_GL_NO_SITE_LANGS) . '.';
            }
        }
        return $_langs_arr;
    }
    public static function __GetFooPath1()
    {
        if (IsModuleInstalled("intranet"))
        {
            $_sql = "SELECT COUNT(U.ID) as C FROM b_user U WHERE U.ACTIVE = 'Y' AND U.LAST_LOGIN IS NOT NULL AND EXISTS(SELECT 'x' FROM b_utm_user UF, b_user_field F WHERE F.ENTITY_ID = 'USER' AND F.FIELD_NAME = 'UF_DEPARTMENT' AND UF.FIELD_ID = F.ID AND UF.VALUE_ID = U.ID AND UF.VALUE_INT IS NOT NULL AND UF.VALUE_INT <> 0)";
            $_query = $GLOBALS['DB']->Query($_sql, true);
            if ($_query && ($_row= $_query->Fetch())) return $_row['C'];
        }
        return 0;
    }
    public static function GetCurrentHelps(&$strError, $_helps_arr = false)
    {
        $_arr_help_tmp = array();
        $_help_path = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/help';
        $_dir_entry = @opendir($_help_path);
        if ($_dir_entry)
        {
            while (false !== ($_dir = readdir($_dir_entry)))
            {
                if (is_dir($_help_path . '/' . $_dir) && $_dir != '.' && $_dir != '..')
                {
                    if ($_helps_arr === false || is_array($_helps_arr) && in_array($_dir, $_helps_arr))
                    {
                        $_lang_content_dat = '';
                        if (file_exists($_help_path . '/' . $_dir . '/supd_lang_date.dat'))
                        {
                            $_lang_content_dat = file_get_contents($_help_path . '/' . $_dir .'/supd_lang_date.dat');
                            $_lang_content_dat = preg_replace('/[\D]+/', '' , $_lang_content_dat);
                            if (strlen($_lang_content_dat) != 0)
                            {
                                CUpdateClient::AddMessage2Log(str_replace('#HELP#', $_dir, GetMessage(SUPP_GH_ERR_DHELP)) , 'UGH01');
                                $strError .= '[UGH01]' . str_replace('#HELP#', $_dir, GetMessage(SUPP_GH_ERR_DHELP)) . '.';
                                $_lang_content_dat ='';
                            }
                        }
                        $_arr_help_tmp[$_dir] = $_lang_content_dat;
                    }
                }
            }
            closedir($_dir_entry);
        }
        $_lang_list = false;
        $_sort = 'sort';
        $_order = 'asc';
        if (class_exists('CLanguage')) 
            $_lang_list = CLanguage::GetList($_sort, $_order, array( 'ACTIVE' => 'Y' ));
        elseif (class_exists('CLang')) 
            $_lang_list = CLang::GetList($_sort, $_order, array( 'ACTIVE' => 'Y' ));
        if ($_lang_list === false)
        {
            CUpdateClient::AddMessage2Log(GetMessage(SUPP_GL_WHERE_LANGS) , 'UGH00');
            $strError .= '[UGH00]' . GetMessage(SUPP_GL_WHERE_LANGS) . '.';
        }
        else
        {
            while ($_lang = $_lang_list-> Fetch())
            {
                if ($_helps_arr === false || is_array($_helps_arr) && in_array($_lang['LID'], $_helps_arr))
                {
                    if (!array_key_exists($_lang['LID'], $_arr_help_tmp))
                    {
                        $_arr_help_tmp[$_lang['LID']] = '';
                    }
                }
            }
            if ($_helps_arr === false && count($_arr_help_tmp) <= 0)
            {
                CUpdateClient::AddMessage2Log(GetMessage(SUPP_GL_NO_SITE_LANGS) , 'UGH02');
                $strError .= '[UGH02]' . GetMessage(SUPP_GL_NO_SITE_LANGS) . '.';
            }
        }
        return $_arr_help_tmp;
    }
    public static function AddMessage2Log($_message, $_message_type = "")
    {
        $_max_file_size = 1000000;
        $_length = 0;
        $_updater_log_path = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/updater.log';
        $_updater_tmp1_path = $_SERVER['DOCUMENT_ROOT'] . US_SHARED_KERNEL_PATH . '/modules/updater_tmp1.log';
        if (strlen($_message) > 0 || strlen($_message_type) > 0)
        {
            $_prev_setting = ignore_user_abort(true);
            if (file_exists($_updater_log_path))
            {
                $_log_file_size = @filesize($_updater_log_path);
                $_log_file_size = IntVal($_log_file_size);
                if ($_log_file_size > $_max_file_size)
                {
                    if (!($_file_log_p = @fopen($_updater_log_path, 'rb')))
                    {
                        ignore_user_abort($_prev_setting);
                        return False;
                    }
                    if (!($_file_tmp1_p = @fopen($_updater_tmp1_path, 'wb')))
                    {
                        ignore_user_abort($_prev_setting);
                        return False;
                    }
                    $_524580008 = IntVal($_log_file_size - $_max_file_size / 2.0);
                    fseek($_file_log_p, $_524580008);
                    do
                    {
                        $_file_log_content = fread($_file_log_p, $_length);
                        if (strlen($_file_log_content) == 0 ) break;
                        @fwrite($_file_tmp1_p, $_file_log_content);
                    }
                    while (true);
                    @fclose($_file_log_p);
                    @fclose($_file_tmp1_p);
                    @copy($_updater_tmp1_path, $_updater_log_path);
                    @unlink($_updater_tmp1_path);
                }
                clearstatcache();
            }
            if ($_file_log_p = @fopen($_updater_log_path, 'ab+'))
            {
                if (flock($_file_log_p, LOCK_EX))
                {
                    @fwrite($_file_log_p, date('Y-m-d H:i:s') . '-' . $_message_type . '-' . $_message . '');
                    @fflush($_file_log_p);
                    @flock($_file_log_p, LOCK_UN);
                    @fclose($_file_log_p);
                }
            }
            ignore_user_abort($_prev_setting);
        }
    }
    public static function CheckDirPath($_path, $_write_permiss = true)
    {
        $_1364691373 = Array();
        $_path = str_replace('\\', '/', $_path);
        $_path = str_replace('//', '/', $_path);
        if ($_path[strlen($_path)] != '/')
        {
            $_slash_pos = CUpdateClient::bxstrrpos($_path, '/');
            $_path = substr($_path, 0, $_slash_pos);
        }
        while (strlen($_path) > 0 && $_path[strlen($_path)] == '/') 
            $_path = substr($_path, 0, strlen($_path));
        $_slash_pos = CUpdateClient::bxstrrpos($_path, '/');
        while ($_slash_pos > 0)
        {
            if (file_exists($_path) && is_dir($_path))
            {
                if ($_write_permiss)
                {
                    if (!is_writable($_path)) @chmod($_path, BX_DIR_PERMISSIONS);
                }
                break;
            }
            $_1364691373[] = substr($_path, $_slash_pos);
            $_path = substr($_path, 0, $_slash_pos);
            $_slash_pos = CUpdateClient::bxstrrpos($_path, '/');
        }
        for ($_i = count($_1364691373); $_i >= 0; $_i--)
        {
            $_path = $_path . '/' . $_1364691373[$_i];
            @mkdir($_path, BX_DIR_PERMISSIONS);
        }
    }
    public static function CopyDirFiles($_src_path, $_dst_path, &$strError, $_sub_directory_flag = True, $_957891511 = array())
    {
        $strError_tmp = "";
        while (strlen($_src_path) > 0 && $_src_path[strlen($_src_path)] == '/') 
            $_src_path = substr($_src_path, 0, strlen($_src_path));
        while (strlen($_dst_path) > 0 && $_dst_path[strlen($_dst_path)] == '/') 
            $_dst_path = substr($_dst_path, 0, strlen($_dst_path));
        if (strpos($_dst_path . '/', $_src_path . '/') === 0) 
            $strError_tmp .= '[UCDF01]' . GetMessage(SUPP_CDF_SELF_COPY) . '.';
        if (strlen($strError_tmp) <= 0)
        {
            if (!file_exists($_src_path)) 
                $strError_tmp .= '[UCDF02]' . str_replace('#FILE#', $_src_path, GetMessage(SUPP_CDF_NO_PATH)) . '.';
        }
        if (strlen($strError_tmp) <= 0)
        {
            $strongUpdateCheck = COption::GetOptionString('main', 'strong_update_check', 'Y');
            if (is_dir($_src_path))
            {
                CUpdateClient::CheckDirPath($_dst_path . '/');
                if (!file_exists($_dst_path) || !is_dir($_dst_path)) 
                    $strError_tmp .= '[UCDF03]' . str_replace('#FILE#', $_dst_path, GetMessage(SUPP_CDF_CANT_CREATE)) . '.';
                elseif (!is_writable($_dst_path)) 
                    $strError_tmp .= '[UCDF04]' . str_replace('#FILE#', $_dst_path, GetMessage(SUPP_CDF_CANT_WRITE)) . '.';
                if (strlen($strError_tmp) <= 0)
                {
                    if ($_dir_entry = @opendir($_src_path))
                    {
                        while (($_dir = readdir($_dir_entry)) !== false)
                        {
                            if ($_dir == '.' || $_dir == '..') continue;
                            if ($_sub_directory_flag && substr($_dir, 0, strlen('updater')) == 'updater') continue;
                            if (count($_957891511) > 0)
                            {
                                $_309628774 = false;
                                foreach ($_957891511 as $_30098938)
                                {
                                    if (strpos($_src_path . '/' . $_dir . '/', '/lang/' . $_30098938 . '/') !== false)
                                    {
                                        $_309628774 = true;
                                        break;
                                    }
                                }
                                if ($_309628774) continue;
                            }
                            if (is_dir($_src_path . '/' . $_dir))
                            {
                                CUpdateClient::CopyDirFiles($_src_path . '/' . $_dir, $_dst_path . '/' . $_dir, $strError_tmp, false, $_957891511);
                            }
                            elseif (is_file($_src_path . '/' . $_dir))
                            {
                                if (file_exists($_dst_path . '/' . $_dir) && !is_writable($_dst_path . '/' . $_dir))
                                {
                                    $strError_tmp .= '[UCDF05]' . str_replace('#FILE#', $_dst_path . '/' . $_dir, GetMessage(SUPP_CDF_CANT_FILE)) . '.';
                                }
                                else
                                {
                                    if ($strongUpdateCheck == 'Y') 
                                        $_1327440025 = dechex(crc32(file_get_contents($_src_path . '/' . $_dir)));
                                    @copy($_src_path . '/' . $_dir, $_dst_path . '/' . $_dir);
                                    @chmod($_dst_path . '/' . $_dir, BX_FILE_PERMISSIONS);
                                    if ($strongUpdateCheck == 'Y')
                                    {
                                        $_114995783 = dechex(crc32(file_get_contents($_dst_path . '/' . $_dir)));
                                        if ($_114995783 !== $_1327440025)
                                        {
                                            $strError_tmp .= '[UCDF061]' . str_replace('#FILE#', $_dst_path . '/' . $_dir, GetMessage(SUPP_UGA_FILE_CRUSH)) . '.';
                                        }
                                    }
                                }
                            }
                        }
                        @closedir($_dir_entry);
                    }
                }
            }
            else
            {
                $_slash_pos = CUpdateClient::bxstrrpos($_dst_path, '/');
                $_470594441 = substr($_dst_path, 0, $_slash_pos);
                CUpdateClient::CheckDirPath($_470594441 . '/');
                if (!file_exists($_470594441) || !is_dir($_470594441)) 
                    $strError_tmp .= '[UCDF06]' . str_replace('#FILE#', $_470594441, GetMessage(SUPP_CDF_CANT_FOLDER)) . '.';
                elseif (!is_writable($_470594441)) 
                    $strError_tmp .= '[UCDF07]' . str_replace('#FILE#', $_470594441, GetMessage(SUPP_CDF_CANT_FOLDER_WR)) . '.';
                if (strlen($strError_tmp) <= 0)
                {
                    if ($strongUpdateCheck == 'Y') 
                        $_1327440025 = dechex(crc32(file_get_contents($_src_path)));
                    @copy($_src_path, $_dst_path);
                    @chmod($_dst_path, BX_FILE_PERMISSIONS);
                    if ($strongUpdateCheck == 'Y')
                    {
                        $_114995783 = dechex(crc32(file_get_contents($_dst_path)));
                        if ($_114995783 !== $_1327440025)
                        {
                            $strError_tmp .= '[UCDF0611]' . str_replace('#FILE#', $_dst_path, GetMessage(SUPP_UGA_FILE_CRUSH)) . '.';
                        }
                    }
                }
            }
        }
        if (strlen($strError_tmp) > 0)
        {
            CUpdateClient::AddMessage2Log($strError_tmp, 'CUCDF');
            $strError .= $strError_tmp;
            return False;
        }
        else return True;
    }
    public static function DeleteDirFilesEx($_path)
    {
        if (!file_exists($_path)) return False;
        if (is_file($_path))
        {
            @unlink($_path);
            return True;
        }
        if ($_dir_entry = @opendir($_path))
        {
            while (($_dir = readdir($_dir_entry)) !== false)
            {
                if ($_dir == '.' || $_dir == '..') continue;
                if (is_dir($_path . '/' . $_dir))
                {
                    CUpdateClient::DeleteDirFilesEx($_path . '/' . $_dir);
                }
                else
                {
                    @unlink($_path . '/' . $_dir);
                }
            }
        }
        @closedir($_dir_entry);
        @rmdir($_path);
        return True;
    }
    public static function bxstrrpos($_haystack, $_needle)
    {
        $_467671718 = strpos(strrev($_haystack) , strrev($_needle));
        if ($_467671718 === false) return false;
        $_467671718 = strlen($_haystack) - strlen($_needle) - $_467671718;
        return $_467671718;
    }
    public static function GetModuleInfo($_path)
    {
        $arModuleVersion = array();
        //$_version_content = file_get_contents($_path . '/install/version.php');
        //var_dump($_version_content);
        include_once($_path . '/install/version.php');
        //if ($_version_content !== false)
        //{
            
        //    @eval(str_replace(array('') , '', $_version_content));
            if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) return $arModuleVersion;
//
        touch($_path . '/install/version.php');
        include ($_path . '/install/version.php');
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) return $arModuleVersion;
        include_once ($_path . '/install/index.php');
        $_arr_directory = explode('/', $_path);
        $_i = array_search('modules', $_arr_directory);
        $_module_dir = $_arr_directory[$_i];
        $_module_dir = str_replace( '.' , '_', $_module_dir);
        $_new_module = new $_module_dir;
        return array(
            'VERSION' => $_new_module->MODULE_VERSION,
            'VERSION_DATE' => $_new_module->MODULE_VERSION_DATE,
        );
    }
    public static function GetLicenseKey()
    {
        if (defined("US_LICENSE_KEY")) return US_LICENSE_KEY;
        if (defined('LICENSE_KEY')) return LICENSE_KEY;
        if (!isset($GLOBALS['CACHE4UPDATESYS_LICENSE_KEY']) || $GLOBALS['CACHE4UPDATESYS_LICENSE_KEY'] =='')
        {
            $LICENSE_KEY = 'demo';
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/thurly/license_key.php')) 
                include ($_SERVER['DOCUMENT_ROOT'] . '/thurly/license_key.php');
            $GLOBALS['CACHE4UPDATESYS_LICENSE_KEY'] = $LICENSE_KEY;
        }
        return $GLOBALS['CACHE4UPDATESYS_LICENSE_KEY'];
    }
    public static function getmicrotime()
    {
        list($_usec, $_sec) = explode(" ", microtime());
        return ((float)$_usec + (float)$_sec);
    }
    public static function ConvertToLangCharset($_error_str, $_error_no, $_sockInfo)
    {
        if (class_exists('CUtil') && method_exists('CUtil', 'ConvertToLangCharset')) 
            $_error_str = CUtil::ConvertToLangCharset($_error_str);
        $strError_tmp = GetMessage(SUPP_GHTTP_ER) .': [' . $_error_no . ']' . $_error_str . '.';
        if (intval($_error_no) <= 0) 
            $strError_tmp .= GetMessage(SUPP_GHTTP_ER_DEF) . '';
        CUpdateClient::AddMessage2Log('Error connecting2'.$_sockInfo[SOCKET_IP] . ': [' . $_error_no . ']' . $_error_str . '', 'ERRCONN1');
        return $strError_tmp;
    }
    public static function _getSockInfo($_update_site = null, $_default_port = null)
    {
        if (!$_update_site) $_update_site = COption::GetOptionString("main", "update_site", DEFAULT_UPDATE_SERVER);
        if (!$_default_port) $_default_port = 0;
        $_update_site_proxy_addr = COption::GetOptionString('main', 'update_site_proxy_addr','');
        $_update_site_proxy_port = COption::GetOptionString('main', 'update_site_proxy_port','');
        $_update_site_proxy_user = COption::GetOptionString('main', 'update_site_proxy_user','');
        $_update_site_proxy_pass = COption::GetOptionString('main', 'update_site_proxy_pass','');
        $_proxy_flag = (strlen($_update_site_proxy_addr) > 0 && strlen($_update_site_proxy_port) > 0);
        $_str_result = array(
            'USE_PROXY' => $_proxy_flag,
            'IP' => $_update_site,
            'PORT' => $_default_port,
            'SOCKET_IP' => $_update_site,
            'SOCKET_PORT' => $_default_port,
        );
        if ($_proxy_flag)
        {
            $_update_site_proxy_port = intval($_update_site_proxy_port);
            if ($_update_site_proxy_port <= 0) $_update_site_proxy_port = 0;
            $_str_result['SOCKET_IP'] = $_update_site_proxy_addr;
            $_str_result['SOCKET_PORT'] = $_update_site_proxy_port;
            $_str_result['PROXY_USERNAME'] = $_update_site_proxy_user;
            $_str_result['PROXY_PASSWORD'] = $_update_site_proxy_pass;
        }
        return $_str_result;
    }
}
class CUpdateControllerSupport
{
    public static function CheckUpdates()
    {
        $errorMessage = "";
        $_stable_versions_only = COption::GetOptionString('main', 'stable_versions_only', 'Y');
        if (!($_update_list = CUpdateClient::GetUpdatesList($errorMessage, 'LANG', $_stable_versions_only))) 
            $errorMessage .= GetMessage(SUPZC_NO_CONNECT) . '.';
        if ($_update_list)
        {
            if (isset($_update_list['ERROR']))
            {
                $_error_count = count($_update_list['ERROR']);
                for ($_i = 0; $_i < $_error_count; $_i++) 
                    $errorMessage .= '[' . $_update_list['ERROR'][$_i]['@']['TYPE'] . ']' . $_update_list['ERROR'][$_i]['#'];
            }
        }
        if (StrLen($errorMessage) > 0) 
            return array( 'ERROR', $errorMessage );
        if (isset($_update_list['UPDATE_SYSTEM'])) 
            return array( 'UPDSYS', '');
        $_module_count = 0;
        if (isset($_update_list['MODULES']) && 
            is_array($_update_list['MODULES']) && 
            is_array($_update_list['MODULES'][0]['#']['MODULE']))
            $_module_count = count($_update_list['MODULES'][0]['#']['MODULE']);
        $_lang_count = 0;
        if (isset($_update_list['LANGS']) && 
            is_array($_update_list['LANGS']) && 
            is_array($_update_list['LANGS'][0]['#']['INST']) && 
            is_array($_update_list['LANGS'][0]['#']['INST'][0]['#']['LANG'])) 
            $_lang_count = count($_update_list['LANGS'][0]['#']['INST'][0]['#']['LANG']);
        if ($_lang_count > 0 && $_module_count > 0) 
            return array( 'UPDATE', 'ML' );
        elseif ($_lang_count <= 0 && $_module_count > 0) 
            return array( 'UPDATE', 'M' );
        elseif ($_lang_count > 0 && $_module_count <= 0) 
            return array( 'UPDATE', 'L' );
        else 
            return array('FINISH', '');
    }
    public static function UpdateModules()
    {
        return CUpdateControllerSupport::__UpdateKernel("M");
    }
    public static function UpdateLangs()
    {
        return CUpdateControllerSupport::__UpdateKernel("L");
    }
    public static function __UpdateKernel($_1144105389)
    {
        define("UPD_INTERNAL_CALL", "Y");
        $_REQUEST['query_type'] = $_1144105389;
        ob_start();
        include ($_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/main/admin/update_system_call.php');
        $_str_result = ob_get_contents();
        ob_end_clean();
        return $_str_result;
    }
    public static function UpdateUpdate()
    {
        define("UPD_INTERNAL_CALL", "Y");
        $_REQUEST['query_type'] = 'updateupdate';
        ob_start();
        include ($_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/main/admin/update_system_act.php');
        $_str_result = ob_get_contents();
        ob_end_clean();
        return $_str_result;
    }
    public static function Finish()
    {
        @unlink($_SERVER["DOCUMENT_ROOT"] . US_SHARED_KERNEL_PATH . "/modules/versions.php");
    }
    public static function Update($_file_tmp_1_content = "")
    {
        @set_time_limit(0);
        ini_set('track_errors', 1);
        ignore_user_abort(true);
        $_param ='';
        $_file_tmp_1_content = Trim($_file_tmp_1_content);
        if (StrLen($_file_tmp_1_content) <= 0 || $_file_tmp_1_content == 'CHK')
        {
            $_row = CUpdateControllerSupport::CheckUpdates();
            if ($_row[0] == 'ERROR')
            {
                $_param = 'ERR|' . $_row[0];
            }
            elseif ($_row[0] == 'FINISH')
            {
                $_param = 'FIN';
            }
            elseif ($_row[0] == 'UPDSYS')
            {
                $_param = 'UPS';
            }
            elseif ($_row[0] == 'UPDATE')
            {
                $_param = 'STP' . $_row[0];
            }
            else
            {
                $_param = 'ERR|' . 'UNK1';
            }
        }
        else
        {
            if ($_file_tmp_1_content == 'UPS')
            {
                $_1018608480 = CUpdateControllerSupport::UpdateUpdate();
                if ($_1018608480 == 'Y') $_param = 'CHK';
                else $_param = 'ERR|' . $_1018608480;
            }
            elseif (SubStr($_file_tmp_1_content, 0 , 0) == 'STP')
            {
                $_1103457495 = SubStr($_file_tmp_1_content, 0);
                if ($_1103457495 == 'ML')
                {
                    $_1018608480 = CUpdateControllerSupport::UpdateModules();
                    if ($_1018608480 == 'FIN') $_param = 'STP' . 'L';
                    elseif (SubStr($_1018608480, 0 , 0) == 'ERR') $_param = 'ERR|' . SubStr($_1018608480, 0);
                    elseif (SubStr($_1018608480, 0 , 0) == 'STP') $_param = 'STP' . 'ML' . '|' . SubStr($_1018608480, 0);
                    else $_param = 'ERR|' . 'UNK01';
                }
                elseif ($_1103457495 == 'M')
                {
                    $_1018608480 = CUpdateControllerSupport::UpdateModules();
                    if ($_1018608480 == 'FIN') $_param = 'FIN';
                    elseif (SubStr($_1018608480, 0 , 0) == 'ERR') $_param = 'ERR|' . SubStr($_1018608480, 0);
                    elseif (SubStr($_1018608480, 0 , 0) == 'STP') $_param = 'STP' . 'M' . '|' . SubStr($_1018608480, 0);
                    else $_param = 'ERR|' . 'UNK02';
                }
                elseif ($_1103457495 == 'L')
                {
                    $_1018608480 = CUpdateControllerSupport::UpdateLangs();
                    if ($_1018608480 == 'FIN') $_param = 'FIN';
                    elseif (SubStr($_1018608480, 0, 0) == 'ERR') $_param = 'ERR|' . SubStr($_1018608480, 0);
                    elseif (SubStr($_1018608480, 0, 0) == 'STP') $_param = 'STP' . 'L' . '|' . SubStr($_1018608480, 0);
                    else $_param = 'ERR|' . 'UNK03';
                }
                else
                {
                    $_param = 'ERR|' . 'UNK2';
                }
            }
            else
            {
                $_param = 'ERR|' . 'UNK3';
            }
        }
        if ($_param == 'FIN') CUpdateControllerSupport::Finish();
        return $_param;
    }
    public static function CollectVersionsFile()
    {
        $_version_path = $_SERVER["DOCUMENT_ROOT"] . US_SHARED_KERNEL_PATH . "/modules/versions.php";
        @unlink($_version_path);
        $errorMessage ='';
        $_current_module = CUpdateClient::GetCurrentModules($errorMessage, false);
        if (StrLen($errorMessage) <= 0)
        {
            $_file_version_p = fopen($_version_path, 'w');
            fwrite($_file_version_p, '<' . '?');
            fwrite($_file_version_p, '$arVersions = array(');
            foreach ($_current_module as $_953617183 => $_1159597351) 
                fwrite($_file_version_p, ''.htmlspecialcharsbx($_953617183). '=>'.htmlspecialcharsbx($_1159597351));
            fwrite($_file_version_p,');');
            fwrite($_file_version_p, '?' . '>');
            fclose($_file_version_p);
        }
    }
} ?>
