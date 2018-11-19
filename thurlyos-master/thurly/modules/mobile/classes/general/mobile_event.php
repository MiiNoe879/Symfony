<?
IncludeModuleLangFile(__FILE__);

class CMobileEvent
{
	public static function PullOnGetDependentModule()
	{
		return Array(
			'MODULE_ID' => "mobile",
			'USE' => Array("PUBLIC_SECTION")
		);
	}
}

class MobileApplication extends Thurly\Main\Authentication\Application
{
	protected $validUrls = array(
		"/mobile/",
		"/thurly/tools/check_appcache.php",
		"/thurly/tools/disk/uf.php",
		"/thurly/services/disk/index.php",
		"/thurly/groupdav.php",
		"/thurly/tools/composite_data.php",
		"/thurly/tools/crm_show_file.php",
		"/thurly/tools/dav_profile.php",
		"/thurly/components/thurly/disk.folder.list/ajax.php",
		"/thurly/services/mobile/jscomponent.php",
		"/thurly/services/rest/index.php",
		"/rest/"

	);

	public function __construct()
	{
		$diskEnabled = \Thurly\Main\Config\Option::get('disk', 'successfully_converted', false) && CModule::includeModule('disk');

		if(!$diskEnabled)
		{
			$this->validUrls = array_merge(
				$this->validUrls,
				array(
					"/company/personal.php",
					"/docs/index.php",
					"/docs/shared/index.php",
					"/workgroups/index.php"
				));
		}

		if (\Thurly\Main\ModuleManager::isModuleInstalled('extranet'))
		{
			$extranetSiteId = \Thurly\Main\Config\Option::get('extranet', 'extranet_site', false);
			if ($extranetSiteId)
			{
				$res = \Thurly\Main\SiteTable::getList(array(
					'filter' => array('=LID' => $extranetSiteId),
					'select' => array('DIR')
				));
				if ($site = $res->fetch())
				{
					$this->validUrls = array_merge(
						$this->validUrls,
						array(
							$site['DIR']."mobile/",
							$site['DIR']."contacts/personal.php"
						));
				}
			}
		}
	}

	public static function OnApplicationsBuildList()
	{
		return array(
			"ID" => "mobile",
			"NAME" => GetMessage("MOBILE_APPLICATION_NAME"),
			"DESCRIPTION" => GetMessage("MOBILE_APPLICATION_DESC"),
			"SORT" => 90,
			"CLASS" => "MobileApplication",
		);
	}
}
