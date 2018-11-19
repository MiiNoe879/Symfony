<?php
IncludeModuleLangFile(__FILE__);

class CThurlyCloudMobile
{
	/**
	 * Builds menu
	 *
	 * @return void
	 *
	 * RegisterModuleDependences(
	 * 	"mobileapp",
	 * 	"OnBeforeAdminMobileMenuBuild",
	 * 	"thurlycloud",
	 * 	"CThurlyCloudMobile",
	 * 	"OnBeforeAdminMobileMenuBuild"
	 * );
	 */
	public static function OnBeforeAdminMobileMenuBuild()
	{
		global $USER;

		if ($USER->CanDoOperation("thurlycloud_monitoring"))
		{
			CAdminMobileMenu::addItem(array(
				"text" => GetMessage("BCL_MON_MOB_INSPECTOR"),
				"type" => "section",
				"sort" => 300,
				"items" => array(
					array(
						"text" => GetMessage("BCL_MON_MOB_MENU_IPAGE"),
						"data-url" => "/thurly/admin/mobile/thurlycloud_monitoring_ipage.php",
						"data-pageid" => "thurly_cloud_monitoring_info",
						"push-param" => "bc"
					),
					array(
						"text" => GetMessage("BCL_MON_MOB_MENU_PUSH"),
						"data-url" => "/thurly/admin/mobile/thurlycloud_monitoring_push.php",
						"data-pageid" => "thurly_cloud_monitoring_push",
					),
				),
			));
		}
	}

	public static function getUserDevices($userId)
	{
		$arResult = array();

		if(CModule::IncludeModule("pull"))
		{
			$dbres = CPullPush::GetList(Array(), Array("USER_ID" => $userId));
			while($arDb = $dbres->Fetch())
			{
				if($arDb["DEVICE_TYPE"] == "APPLE")
				{
					CModule::IncludeModule("mobileapp");
					CMobile::Init();

/*					if(CMobile::$isDev)
						$protocol = 1;
					else */
						$protocol = 2;
				}
				else
					$protocol = 3;

				$arResult[] = $arDb["DEVICE_TOKEN"].":".$protocol.":ThurlyAdmin";
			}
		}

		return $arResult;
	}
}