<?php
error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
 require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen('/start.php')).'/bx_root.php');
 require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/lib/loader.php');
 \Thurly\Main\Loader::registerAutoLoadClasses( 'main', 
 	array( 
	 	'thurly\main\application' => 'lib/application.php',
	 	'thurly\main\httpapplication' => 'lib/httpapplication.php',
	 	'thurly\main\argumentexception' => 'lib/exception.php',
	 	'thurly\main\argumentnullexception' => 'lib/exception.php',
	 	'thurly\main\argumentoutofrangeexception' => 'lib/exception.php',
	 	'thurly\main\argumenttypeexception' => 'lib/exception.php',
		'thurly\main\notimplementedexception' => 'lib/exception.php',
		'thurly\main\notsupportedexception' => 'lib/exception.php',
		'thurly\main\invalidoperationexception' => 'lib/exception.php',
		'thurly\main\objectpropertyexception' => 'lib/exception.php',
		'thurly\main\objectnotfoundexception' => 'lib/exception.php',
		'thurly\main\objectexception' => 'lib/exception.php',
		'thurly\main\systemexception' => 'lib/exception.php',
		'thurly\main\accessdeniedexception' => 'lib/exception.php',
		'thurly\main\io\invalidpathexception' => 'lib/io/ioexception.php',
		'thurly\main\io\filenotfoundexception' => 'lib/io/ioexception.php',
		'thurly\main\io\filedeleteexception' => 'lib/io/ioexception.php',
		'thurly\main\io\fileopenexception' => 'lib/io/ioexception.php',
		'thurly\main\io\filenotopenedexception' => 'lib/io/ioexception.php',
		'thurly\main\context' => 'lib/context.php',
		'thurly\main\httpcontext' => 'lib/httpcontext.php',
		'thurly\main\dispatcher' => 'lib/dispatcher.php',
		'thurly\main\environment' => 'lib/environment.php',
		'thurly\main\event' => 'lib/event.php',
		'thurly\main\eventmanager' => 'lib/eventmanager.php',
		'thurly\main\eventresult' => 'lib/eventresult.php',
		'thurly\main\request' => 'lib/request.php',
		'thurly\main\httprequest' => 'lib/httprequest.php',
		'thurly\main\response' => 'lib/response.php',
		'thurly\main\httpresponse' => 'lib/httpresponse.php',
		'thurly\main\modulemanager' => 'lib/modulemanager.php',
		'thurly\main\server' => 'lib/server.php',
		'thurly\main\config\configuration' => 'lib/config/configuration.php',
		'thurly\main\config\option' => 'lib/config/option.php',
		'thurly\main\context\culture' => 'lib/context/culture.php',
		'thurly\main\context\site' => 'lib/context/site.php',
		'thurly\main\data\cache' => 'lib/data/cache.php',
		'thurly\main\data\cacheengineapc' => 'lib/data/cacheengineapc.php',
		'thurly\main\data\cacheenginememcache' => 'lib/data/cacheenginememcache.php',
		'thurly\main\data\cacheenginefiles' => 'lib/data/cacheenginefiles.php',
		'thurly\main\data\cacheenginenone' => 'lib/data/cacheenginenone.php',
		'thurly\main\data\connection' => 'lib/data/connection.php',
		'thurly\main\data\connectionpool' => 'lib/data/connectionpool.php',
		'thurly\main\data\icacheengine' => 'lib/data/cache.php',
		'thurly\main\data\hsphpreadconnection' => 'lib/data/hsphpreadconnection.php',
		'thurly\main\data\managedcache' => 'lib/data/managedcache.php',
		'thurly\main\data\taggedcache' => 'lib/data/taggedcache.php',
		'thurly\main\data\memcacheconnection' => 'lib/data/memcacheconnection.php',
		'thurly\main\data\memcachedconnection' => 'lib/data/memcachedconnection.php',
		'thurly\main\data\nosqlconnection' => 'lib/data/nosqlconnection.php',
		'thurly\main\db\arrayresult' => 'lib/db/arrayresult.php',
		'thurly\main\db\result' => 'lib/db/result.php',
		'thurly\main\db\connection' => 'lib/db/connection.php',
		'thurly\main\db\sqlexception' => 'lib/db/sqlexception.php',
		'thurly\main\db\sqlqueryexception' => 'lib/db/sqlexception.php',
		'thurly\main\db\sqlexpression' => 'lib/db/sqlexpression.php',
		'thurly\main\db\sqlhelper' => 'lib/db/sqlhelper.php',
		'thurly\main\db\mysqlconnection' => 'lib/db/mysqlconnection.php',
		'thurly\main\db\mysqlresult' => 'lib/db/mysqlresult.php',
		'thurly\main\db\mysqlsqlhelper' => 'lib/db/mysqlsqlhelper.php',
		'thurly\main\db\mysqliconnection' => 'lib/db/mysqliconnection.php',
		'thurly\main\db\mysqliresult' => 'lib/db/mysqliresult.php',
		'thurly\main\db\mysqlisqlhelper' => 'lib/db/mysqlisqlhelper.php',
		'thurly\main\db\mssqlconnection' => 'lib/db/mssqlconnection.php',
		'thurly\main\db\mssqlresult' => 'lib/db/mssqlresult.php',
		'thurly\main\db\mssqlsqlhelper' => 'lib/db/mssqlsqlhelper.php',
		'thurly\main\db\oracleconnection' => 'lib/db/oracleconnection.php',
		'thurly\main\db\oracleresult' => 'lib/db/oracleresult.php',
		'thurly\main\db\oraclesqlhelper' => 'lib/db/oraclesqlhelper.php',
		'thurly\main\diag\httpexceptionhandleroutput' => 'lib/diag/httpexceptionhandleroutput.php',
		'thurly\main\diag\fileexceptionhandlerlog' => 'lib/diag/fileexceptionhandlerlog.php',
		'thurly\main\diag\exceptionhandler' => 'lib/diag/exceptionhandler.php',
		'thurly\main\diag\iexceptionhandleroutput' => 'lib/diag/iexceptionhandleroutput.php',
		'thurly\main\diag\exceptionhandlerlog' => 'lib/diag/exceptionhandlerlog.php',
		'thurly\main\io\file' => 'lib/io/file.php',
		'thurly\main\io\fileentry' => 'lib/io/fileentry.php',
		'thurly\main\io\path' => 'lib/io/path.php',
		'thurly\main\io\filesystementry' => 'lib/io/filesystementry.php',
		'thurly\main\io\ifilestream' => 'lib/io/ifilestream.php',
		'thurly\main\localization\loc' => 'lib/localization/loc.php',
		'thurly\main\mail\mail' => 'lib/mail/mail.php',
		'thurly\main\mail\tracking' => 'lib/mail/tracking.php',
		'thurly\main\mail\eventmanager' => 'lib/mail/eventmanager.php',
		'thurly\main\mail\eventmessagecompiler' => 'lib/mail/eventmessagecompiler.php',
		'thurly\main\mail\eventmessagethemecompiler' => 'lib/mail/eventmessagethemecompiler.php',
		'thurly\main\mail\internal\event' => 'lib/mail/internal/event.php',
		'thurly\main\mail\internal\eventattachment' => 'lib/mail/internal/eventattachment.php',
		'thurly\main\mail\internal\eventmessage' => 'lib/mail/internal/eventmessage.php',
		'thurly\main\mail\internal\eventmessagesite' => 'lib/mail/internal/eventmessagesite.php',
		'thurly\main\mail\internal\eventmessageattachment' => 'lib/mail/internal/eventmessageattachment.php',
		'thurly\main\mail\internal\eventtype' => 'lib/mail/internal/eventtype.php',
		'thurly\main\text\converter' => 'lib/text/converter.php',
		'thurly\main\text\emptyconverter' => 'lib/text/emptyconverter.php',
		'thurly\main\text\encoding' => 'lib/text/encoding.php',
		'thurly\main\text\htmlconverter' => 'lib/text/htmlconverter.php',
		'thurly\main\text\binarystring' => 'lib/text/binarystring.php',
		'thurly\main\text\xmlconverter' => 'lib/text/xmlconverter.php',
		'thurly\main\type\collection' => 'lib/type/collection.php',
		'thurly\main\type\date' => 'lib/type/date.php',
		'thurly\main\type\datetime' => 'lib/type/datetime.php',
		'thurly\main\type\dictionary' => 'lib/type/dictionary.php',
		'thurly\main\type\filterabledictionary' => 'lib/type/filterabledictionary.php',
		'thurly\main\type\parameterdictionary' => 'lib/type/parameterdictionary.php',
		'thurly\main\web\cookie' => 'lib/web/cookie.php',
		'thurly\main\web\uri' => 'lib/web/uri.php',
		'thurly\main\sendereventhandler' => 'lib/senderconnector.php',
		'thurly\main\senderconnectoruser' => 'lib/senderconnector.php',
		'thurly\main\urlrewriterrulemaker' => 'lib/urlrewriter.php',
		'thurly\main\update\stepper' => 'lib/update/stepper.php',
		'CTimeZone' => 'classes/general/time.php',
		'thurly\main\composite\abstractresponse' => 'lib/composite/responder.php',
		'thurly\main\composite\fileresponse' => 'lib/composite/responder.php',
		'thurly\main\composite\memcachedresponse' => 'lib/composite/responder.php',
	)
);

function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
 	return((float)$usec+ (float)$sec);
}
define('START_EXEC_TIME', getmicrotime());
define('B_PROLOG_INCLUDED', true);
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/general/version.php');
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/tools.php');
if(version_compare(PHP_VERSION, '5.0.0') >= 0 && @ini_get_bool('register_long_arrays') != true) {
	$HTTP_POST_FILES = $_FILES;
	$HTTP_SERVER_VARS = $_SERVER;
	$HTTP_GET_VARS = $_GET;
	$HTTP_POST_VARS = $_POST;
	$HTTP_COOKIE_VARS = $_COOKIE;
	$HTTP_ENV_VARS = $_ENV;
}
if(version_compare(PHP_VERSION, '5.4.0') < 0) {
	UnQuoteAll();
}
FormDecode();
$application = \Thurly\Main\HttpApplication::getInstance();
$application->initializeBasicKernel();
global $DBType, $DBDebug, $DBDebugToFile, $DBHost, $DBName, $DBLogin, $DBPassword;
require_once($_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT.'/php_interface/dbconn.php');
if(defined('BX_UTF')) {
	define('BX_UTF_PCRE_MODIFIER', 'u');
} else{
	define('BX_UTF_PCRE_MODIFIER', '');
}
if(!defined('CACHED_b_lang')) define('CACHED_b_lang', 3600);
if(!defined('CACHED_b_option')) define('CACHED_b_option', 3600);
if(!defined('CACHED_b_lang_domain')) define('CACHED_b_lang_domain', 3600);
if(!defined('CACHED_b_site_template')) define('CACHED_b_site_template', 3600);
if(!defined('CACHED_b_event')) define('CACHED_b_event', 3600);
if(!defined('CACHED_b_agent')) define('CACHED_b_agent', 3660);
if(!defined('CACHED_menu')) define('CACHED_menu', 3600);
if(!defined('CACHED_b_file')) define('CACHED_b_file', false);
if(!defined('CACHED_b_file_bucket_size')) define('CACHED_b_file_bucket_size', 100);
if(!defined('CACHED_b_user_field')) define('CACHED_b_user_field', 3600);
if(!defined('CACHED_b_user_field_enum')) define('CACHED_b_user_field_enum', 3600);
if(!defined('CACHED_b_task')) define('CACHED_b_task', 3600);
if(!defined('CACHED_b_task_operation')) define('CACHED_b_task_operation', 3600);
if(!defined('CACHED_b_rating')) define('CACHED_b_rating', 3600);
if(!defined('CACHED_b_rating_vote')) define('CACHED_b_rating_vote', 86400);
if(!defined('CACHED_b_rating_bucket_size')) define('CACHED_b_rating_bucket_size', 100);
if(!defined('CACHED_b_user_access_check')) define('CACHED_b_user_access_check', 3600);
if(!defined('CACHED_b_user_counter')) define('CACHED_b_user_counter', 3600);
if(!defined('CACHED_b_group_subordinate')) define('CACHED_b_group_subordinate', 31536000);
if(!defined('CACHED_b_smile')) define('CACHED_b_smile', 31536000);
if(!defined('TAGGED_user_card_size')) define('TAGGED_user_card_size', 100);
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/'.$DBType.'/database.php');
$DB = new CDatabase;
$DB->debug = $DBDebug;
if($DBDebugToFile) {
	$DB->DebugToFile = true;
	$application->getConnection()->startTracker()->startFileLog($_SERVER['DOCUMENT_ROOT'].'/'.$DBType.'_debug.sql');
}
$show_sql_stat = '';
if(array_key_exists('show_sql_stat', $_GET)) {
	$show_sql_stat = strtoupper($_GET['show_sql_stat']) == 'Y' ? 'Y':'';
 	setcookie('show_sql_stat', $show_sql_stat, false, '/');
} elseif(array_key_exists('show_sql_stat', $_COOKIE)) {
	$show_sql_stat = $_COOKIE['show_sql_stat'];
}
if($show_sql_stat == 'Y') {
	$DB->ShowSqlStat = true;
 	$application->getConnection()->startTracker();
}
if(!($DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword))) {
	if(file_exists(($db_updater_file = $_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT.'/php_interface/dbconn_error.php')))
		include($db_updater_file);
	else
		include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/dbconn_error.php');
	die();
}
$LICENSE_KEY = '';
if(file_exists(($license_file = $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/license_key.php')))
	include($license_file);
if($LICENSE_KEY == '' || strtoupper($LICENSE_KEY) == 'DEMO') define('LICENSE_KEY', 'DEMO');
else define('LICENSE_KEY', $LICENSE_KEY);
require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/classes/general/punycode.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/classes/general/charset_converter.php');
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/'.$DBType.'/main.php');
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/'.$DBType.'/option.php');
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/general/cache.php');
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/general/module.php');
error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR|E_PARSE);
if(file_exists(($db_updater_file = $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/classes/general/update_db_updater.php'))) {
	$US_HOST_PROCESS_MAIN = True;
	include($db_updater_file);
}
?>