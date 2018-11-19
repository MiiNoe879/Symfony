<?php
if (!defined("CACHED_b_thurlycloud_option"))
	define("CACHED_b_thurlycloud_option", 36000);

global $DB;
$db_type = strtolower($DB->type);
CModule::AddAutoloadClasses("thurlycloud", array(
	"CAllThurlyCloudOption" => "classes/general/option.php",
	"CThurlyCloudOption" => "classes/".$db_type."/option.php",
	"CThurlyCloudWebService" => "classes/general/webservice.php",
	"CThurlyCloudCDNWebService" => "classes/general/cdn_webservice.php",
	"CThurlyCloudCDNConfig" => "classes/general/cdn_config.php",
	"CThurlyCloudCDN" => "classes/general/cdn.php",
	"CThurlyCloudCDNQuota" => "classes/general/cdn_quota.php",
	"CThurlyCloudCDNClasses" => "classes/general/cdn_class.php",
	"CThurlyCloudCDNClass" => "classes/general/cdn_class.php",
	"CThurlyCloudCDNServerGroups" => "classes/general/cdn_server.php",
	"CThurlyCloudCDNServerGroup" => "classes/general/cdn_server.php",
	"CThurlyCloudCDNLocations" => "classes/general/cdn_location.php",
	"CThurlyCloudCDNLocation" => "classes/general/cdn_location.php",
	"CThurlyCloudBackupWebService" => "classes/general/backup_webservice.php",
	"CThurlyCloudBackup" => "classes/general/backup.php",
	"CThurlyCloudMonitoringWebService" => "classes/general/monitoring_webservice.php",
	"CThurlyCloudMonitoring" =>  "classes/general/monitoring.php",
	"CThurlyCloudMonitoringResult" => "classes/general/monitoring_result.php",
	"CThurlyCloudMobile" => "classes/general/mobile.php"
));

if(CModule::IncludeModule('clouds'))
{
	CModule::AddAutoloadClasses("thurlycloud", array(
		"CThurlyCloudBackupBucket" => "classes/general/backup_bucket.php",
	));
}

CJSCore::RegisterExt('mobile_monitoring', array(
	'js' => '/thurly/js/thurlycloud/mobile_monitoring.js',
	'lang' => '/thurly/modules/thurlycloud/lang/'.LANGUAGE_ID.'/js_mobile_monitoring.php'
));

class CThurlyCloudException extends Exception
{
	protected $error_code = "";
	protected $debug_info = "";
	public function __construct($message = "", $error_code = "", $debug_info = "")
	{
		parent::__construct($message);
		$this->error_code = $error_code;
		$this->debug_info = $debug_info;
	}
	final public function getErrorCode()
	{
		return $this->error_code;
	}
	final public function getDebugInfo()
	{
		return $this->debug_info;
	}
}
