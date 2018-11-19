<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/.superleft.menu_ext.php");
CModule::IncludeModule("intranet");

if (!function_exists("getLeftMenuItemLink"))
{
	function getLeftMenuItemLink($sectionId, $defaultLink = "")
	{
		$settings = CUserOptions::GetOption("UI", $sectionId);
		return
			is_array($settings) && isset($settings["firstPageLink"]) && strlen($settings["firstPageLink"]) ?
				$settings["firstPageLink"] :
				$defaultLink;
	}
}

$userId = $GLOBALS["USER"]->GetID();

if (defined("BX_COMP_MANAGED_CACHE"))
{
	global $CACHE_MANAGER;
	$CACHE_MANAGER->registerTag("thurlyos_left_menu");
	$CACHE_MANAGER->registerTag("crm_change_role");
	$CACHE_MANAGER->registerTag("USER_CARD_".intval($userId / TAGGED_user_card_size));
}

$arMenu = array(
	array(
		GetMessage("MENU_LIVE_FEED"),
		"/stream/",
		array(),
		array(
			"name" => "live_feed",
			"counter_id" => "live-feed",
			"menu_item_id" => "menu_live_feed",
			"my_tools_section" => true,
		),
		""
	),
	array(
		GetMessage("MENU_TASKS"),
		"/company/personal/user/".$userId."/tasks/",
		array(),
		array(
			"real_link" => getLeftMenuItemLink(
				"tasks_panel_menu",
				"/company/personal/user/".$userId."/tasks/"
			),
			"name" => "tasks",
			"counter_id" => "tasks_total",
			"menu_item_id" => "menu_tasks",
			"sub_link" => SITE_DIR."company/personal/user/".$userId."/tasks/task/edit/0/",
			"top_menu_id" => "tasks_panel_menu",
			"my_tools_section" => true,
		),
		""
	)
);

if (\Thurly\Main\ModuleManager::isModuleInstalled("landing"))
{
	if (
		\Thurly\Main\Loader::includeModule("thurlyos")
		&&
		(
			in_array(\CThurlyOS::getPortalZone(), array("ru", "ua", "kz", "by"))
			|| Thurly\ThurlyOS\Release::isAvailable("landing")
		)
	)
	{
		$arMenu[] = array(
			GetMessage("MENU_SITES"),
			"/sites/",
			array(),
			array(
				"menu_item_id" => "menu_sites",
				"my_tools_section" => true,
				"is_beta" => true

			),
			""
		);
	}
}

$arMenu[] = array(
	GetMessage("MENU_CALENDAR"),
	"/calendar/",
	array(
		"/company/personal/user/".$userId."/calendar/",
	),
	array(
		"real_link" => getLeftMenuItemLink(
			"top_menu_id_calendar",
			"/company/personal/user/".$userId."/calendar/"
		),
		"menu_item_id" => "menu_calendar",
		"counter_id" => "calendar",
		"top_menu_id" => "top_menu_id_calendar",
		"my_tools_section" => true,
	),
	""
);

$diskEnabled = \Thurly\Main\Config\Option::get('disk', 'successfully_converted', false);
$diskPath =
	$diskEnabled === "Y" ?
		"/company/personal/user/".$userId."/disk/path/" :
		"/company/personal/user/".$userId."/files/lib/"
;

$arMenu[] = array(
	GetMessage("MENU_DISK_SECTION"),
	"/docs/",
	array(
		$diskPath,
		"/company/personal/user/".$userId."/disk/volume/", 
	),
	array(
		"real_link" => getLeftMenuItemLink(
			"top_menu_id_docs",
			$diskPath
		),
		"menu_item_id" => "menu_files",
		"top_menu_id" => "top_menu_id_docs",
		"my_tools_section" => true,
	),
	""
);

$arMenu[] = array(
	GetMessage("MENU_PHOTO"),
	"/company/personal/user/".$userId."/photo/",
	array(),
	array(
		"menu_item_id" => "menu_photo",
		"my_tools_section" => true,
		"hidden" => true
	),
	""
);

$arMenu[] = array(
	GetMessage("MENU_BLOG"),
	"/company/personal/user/".$userId."/blog/",
	array(),
	array(
		"menu_item_id" => "menu_blog",
		"my_tools_section" => true,
		"hidden" => true
	),
	""
);

if (CModule::IncludeModule("crm") && CCrmPerms::IsAccessEnabled())
{
	$arMenu[] = array(
		GetMessage("MENU_CRM"),
		"/crm/menu/",
		array("/crm/"),
		array(
			"real_link" => getLeftMenuItemLink(
				"crm_control_panel_menu",
				"/crm/start/"
			),
			"counter_id" => "crm_all",
			"menu_item_id" => "menu_crm_favorite",
			"top_menu_id" => "crm_control_panel_menu"
		),
		""
	);
}

if (IsModuleInstalled("thurlyos") && CModule::IncludeModule("sender") && \Thurly\Sender\Security\User::current()->hasAccess())
{
	$arMenu[] = array(
		GetMessage("MENU_CRM_MARKETING"),
		"/marketing/",
		array(),
		array(
			"menu_item_id" => "menu_marketing",
			"is_beta" => true
		),
		""
	);
}

$arMenu[] = array(
	GetMessage("MENU_IM_MESSENGER"),
	"/online/",
	array(),
	array(
		"counter_id" => "im-message",
		"menu_item_id" => "menu_im_messenger",
		"my_tools_section" => true,
	),
	""
);

if (CModule::IncludeModule("intranet") && CIntranetUtils::IsExternalMailAvailable())
{
	$arMenu[] = array(
		GetMessage("MENU_MAIL"),
		"/company/personal/mail/",
		array(),
		array(
			"counter_id" => "mail_unseen",
			"warning_link" => '/company/personal/mail/?config',
			"warning_title" => GetMessage("MENU_MAIL_CHANGE_SETTINGS"),
			"menu_item_id" => "menu_external_mail",
			"my_tools_section" => true,
		),
		""
	);
}

//groups
$arMenu[] = array(
	GetMessage("MENU_GROUP_SECTION"),
	"/workgroups/menu/",
	array("/workgroups/"),
	array(
		"real_link" => getLeftMenuItemLink(
			"sonetgroups_panel_menu",
			"/workgroups/"
		),
		"sub_link" => "/company/personal/user/".$userId."/groups/create/",
		"menu_item_id"=>"menu_all_groups",
		"top_menu_id" => "sonetgroups_panel_menu"
	),
	""
);

if (CModule::IncludeModule("bizproc") && CBPRuntime::isFeatureEnabled())
{
	$arMenu[] = array(
		GetMessage("MENU_BIZPROC"),
		"/bizproc/",
		array(
			"/company/personal/bizproc/",
			"/company/personal/processes/",
		),
		array(
			"real_link" => getLeftMenuItemLink(
				"top_menu_id_bizproc",
				"/company/personal/bizproc/"
			),
			"counter_id" => "bp_tasks",
			"menu_item_id" => "menu_bizproc_sect",
			"top_menu_id" => "top_menu_id_bizproc",
			"my_tools_section" => true,
		),
		""
	);
}
$licensePrefix = "";
if (CModule::IncludeModule("thurlyos"))
{
	$licensePrefix = CThurlyOS::getLicensePrefix();
}

//marketplace
$arMenu[] = array(
	GetMessage("MENU_MARKETPLACE_APPS"),
	"/marketplace/",
	array(),
	array(
		"real_link" => getLeftMenuItemLink(
			"top_menu_id_marketplace",
			"/marketplace/"
		),
		"class" => "menu-apps",
		"menu_item_id" => "menu_marketplace_sect",
		"top_menu_id" => "top_menu_id_marketplace"
	),
	""
);

if (IsModuleInstalled("thurlyos") &&  in_array($licensePrefix, array('ru', 'kz', 'by', 'ua')) || !IsModuleInstalled("thurlyos"))
{
	if(IsModuleInstalled("crm"))
	{
		$arMenu[] = array(
			GetMessage("MENU_ONEC_SECTION"),
			"/onec/",
			array(),
			array(
				"real_link" => getLeftMenuItemLink(
					"top_menu_id_onec",
					"/onec/"
				),
				"menu_item_id"=>"menu_onec_sect",
				"top_menu_id" => "top_menu_id_onec"
			),
			""
		);
	}
}

$arMenu[] = array(
	GetMessage("MENU_COMPANY_SECTION"),
	"/company/",
	array(),
	array(
		"real_link" => getLeftMenuItemLink(
			"top_menu_id_company",
			"/company/vis_structure.php"
		),
		"class" => "menu-company",
		"menu_item_id" => "menu_company",
		"top_menu_id" => "top_menu_id_company"
	),
	""
);

$arMenu[] = array(
	GetMessage("MENU_TIMEMAN_SECTION"),
	"/timeman/",
	array(),
	array(
		"real_link" => getLeftMenuItemLink(
			"top_menu_id_timeman",
			"/timeman/"
		),
		"menu_item_id"=>"menu_timeman_sect",
		"top_menu_id" => "top_menu_id_timeman"
	),
	""
);

if (CModule::IncludeModule("imopenlines") && \Thurly\ImOpenlines\Security\Helper::isMainMenuEnabled())
{
	$arMenu[] = array(
		GetMessage("MENU_OPENLINES_LINES_SINGLE"),
		"/openlines/",
		array(),
		array(
			"real_link" => getLeftMenuItemLink(
				"top_menu_id_openlines",
				"/openlines/"
			),
			"menu_item_id" => "menu_openlines",
			"top_menu_id" => "top_menu_id_openlines"
		),
		""
	);
}

if (CModule::IncludeModule('voximplant') && \Thurly\Voximplant\Security\Helper::isMainMenuEnabled() && $licensePrefix !== "by")
{
	$arMenu[] = array(
		GetMessage("MENU_TELEPHONY_SECTION"),
		"/telephony/",
		array(),
		array(
			"real_link" => getLeftMenuItemLink(
				"top_menu_id_telephony",
				"/telephony/"
			),
			"class" => "menu-telephony",
			"menu_item_id" => "menu_telephony",
			"top_menu_id" => "top_menu_id_telephony"
		),
		""
	);
}

if (IsModuleInstalled("thurlyos"))
{
	$arMenu[] = array(
		GetMessage("MENU_TARIFF"),
		"/settings/",
		array(),
		array(
			"real_link" => getLeftMenuItemLink(
				"top_menu_id_settings",
				$GLOBALS['USER']->CanDoOperation('thurlyos_config') ? "/settings/license.php" : "/settings/license_all.php"
			),
			"class" => "menu-tariff",
			"menu_item_id" => "menu_tariff",
			"top_menu_id" => "top_menu_id_settings"
		),
		""
	);
}
else
{
	$arMenu[] = array(
		GetMessage("MENU_LICENSE"),
		"/updates/",
		array(),
		array(
			"real_link" => getLeftMenuItemLink(
				"top_menu_id_updates",
				"/updates/"
			),
			"menu_item_id" => "menu_updates",
			"top_menu_id" => "top_menu_id_updates"
		),
		""
	);
}

if (
	IsModuleInstalled("thurlyos") && $GLOBALS['USER']->CanDoOperation('thurlyos_config')
	|| !IsModuleInstalled("thurlyos") && $GLOBALS['USER']->IsAdmin()
)
{
	$arMenu[] = array(
		GetMessage("MENU_SETTINGS_SECTION"),
		"/settings/configs/",
		array("/company/personal/mail/manage/"),
		array(
			"real_link" => getLeftMenuItemLink(
				"top_menu_id_settings_configs",
				"/settings/configs/"
			),
			"class" => "menu-settings",
			"menu_item_id" => "menu_configs_sect",
			"top_menu_id" => "top_menu_id_settings_configs"
		),
		""
	);
}

$aMenuLinks = $arMenu;