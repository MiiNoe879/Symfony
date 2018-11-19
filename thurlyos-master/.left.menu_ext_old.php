<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (SITE_TEMPLATE_ID !== "thurlyos")
	return;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/.left.menu_ext.php");
	
if (!CModule::IncludeModule("socialnetwork"))
	return;

$arUserActiveFeatures = CSocNetFeatures::GetActiveFeatures(SONET_ENTITY_USER, $GLOBALS["USER"]->GetID());
GLOBAL $USER;
$USER_ID = $USER->GetID();

$aMenuB24 = array();
	
$aMenuB24[] = Array(
	GetMessage("LEFT_MENU_LIVE_FEED"),
	SITE_DIR."index.php",
	Array(),
	Array("name" => "live_feed", "counter_id" => "live-feed", "menu_item_id"=>"menu_live_feed"),
	""
);
	
if ($GLOBALS["USER"]->IsAuthorized())
{
	$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

	if (CModule::IncludeModule("im") && SITE_TEMPLATE_ID == "thurlyos")
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_IM_MESSENGER"),
			"/online/",
			Array(),
			Array("counter_id" => "im-message", "menu_item_id"=>"menu_im_messenger"),
			"CBXFeatures::IsFeatureEnabled('WebMessenger')"
		);

	if (
		array_key_exists("tasks", $arSocNetFeaturesSettings)
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["tasks"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["tasks"]["allowed"])
		&& in_array("tasks", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_TASKS"),
			SITE_DIR."company/personal/user/".$USER_ID."/tasks/",
			Array(),
			Array("name" => "tasks", "counter_id" => "tasks_total", "menu_item_id" => "menu_tasks"),
			"CBXFeatures::IsFeatureEnabled('Tasks')"
		);
	if (
		array_key_exists("calendar", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["calendar"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["calendar"]["allowed"])
		&& in_array("calendar", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_CALENDAR"),
			SITE_DIR."company/personal/user/".$USER_ID."/calendar/",
			Array(),
			Array("menu_item_id"=>"menu_calendar", "counter_id" => "calendar"),
			"CBXFeatures::IsFeatureEnabled('Calendar')"
		);
	if (
		CModule::IncludeModule("disk") && $GLOBALS["USER"]->IsAuthorized()
		&& array_key_exists("files", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["files"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["files"]["allowed"])
		&& in_array("files", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_DISC"),
			SITE_DIR."company/personal/user/".$USER_ID."/disk/path/",
			Array(),
			Array(),
			"CBXFeatures::IsFeatureEnabled('PersonalFiles')"
		);
	if (
		CModule::IncludeModule("photogallery") 
		&& array_key_exists("photo", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["photo"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["photo"]["allowed"])
		&& in_array("photo", $arUserActiveFeatures)	
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_PHOTO"),
			SITE_DIR."company/personal/user/".$USER_ID."/photo/",
			Array(),
			Array(),
			"CBXFeatures::IsFeatureEnabled('PersonalPhoto')"
		);
	if (
		CModule::IncludeModule("blog") 
		&& array_key_exists("blog", $arSocNetFeaturesSettings)
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["blog"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["blog"]["allowed"])
		&& in_array("blog", $arUserActiveFeatures)	
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_BLOG"),
			SITE_DIR."company/personal/user/".$USER_ID."/blog/",
			Array(),
			Array("counter_id" => "blog_post"),
			""
		);
	if (CModule::IncludeModule("intranet") && CIntranetUtils::IsExternalMailAvailable())
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_MAIL"),
			SITE_DIR."company/personal/mail/",
			Array(),
			Array("counter_id" => "mail_unseen", "warning_link" => SITE_DIR.'company/personal/mail/?config', "warning_title" => GetMessage("LEFT_MENU_MAIL_SETTING"), "menu_item_id"=>"menu_external_mail"),
			""
		);
	if (CModule::IncludeModule("bizproc"))
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_BP"),
			SITE_DIR."company/personal/bizproc/",
			Array(),
			Array("counter_id" => "bp_tasks"),
			"CBXFeatures::IsFeatureEnabled('BizProc')"
		);
	if (CModule::IncludeModule("lists") && CLists::isFeatureEnabled())
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_MY_PROCESS"),
			SITE_DIR."company/personal/processes/",
			Array(),
			Array("menu_item_id"=>"menu_my_processes"),
			""
		);
	if (CModule::IncludeModule("crm") && CCrmPerms::IsAccessEnabled())
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_CRM"),
			SITE_DIR."crm/stream/",
			Array(),
			Array("counter_id" => "crm_cur_act", "menu_item_id"=>"menu_crm_favorite"),
			""
		);
}

$arMenuB24[] = Array(
	GetMessage("TOP_MENU_MARKETPLACE"),
	SITE_DIR."marketplace/",
	Array('/marketplace/'),
	Array("class" => "menu-apps"),
	(!IsModuleInstalled("rest")) ? "false" : "true"
);

if (CModule::IncludeModule("socialnetwork"))
	$arMenuB24[] = Array(
		GetMessage("TOP_MENU_GROUPS"),
		SITE_DIR."workgroups/",
		Array(),
		Array("class" => "menu-groups"),
		"CBXFeatures::IsFeatureEnabled('Workgroups')"
	);
//extranet groups
if (CModule::IncludeModule("extranet") && CBXFeatures::IsFeatureEnabled('Workgroups') && CBXFeatures::IsFeatureEnabled('Extranet') && CModule::IncludeModule("socialnetwork"))
{
	$arGroupFilterMy = array(
		"USER_ID" => $USER_ID,
		"<=ROLE" => SONET_ROLES_USER,
		"GROUP_ACTIVE" => "Y",
		"!GROUP_CLOSED" => "Y",
		"GROUP_SITE_ID" => CExtranet::GetExtranetSiteID()
	);

	$dbGroups = CSocNetUserToGroup::GetList(
		array(),
		$arGroupFilterMy,
		false,
		array('nTopCount' => 1)
	//array('ID')
	);
	if ($arGroups = $dbGroups->GetNext())
	{
		$arMenuB24[] = Array(
			GetMessage("TOP_MENU_GROUPS_EXTRANET"),
			SITE_DIR."workgroups/extranet/",
			Array(),
			Array("class" => "menu-groups-extranet"),
			"CBXFeatures::IsFeatureEnabled('Workgroups')"
		);
	}
}

$arSkipPattern = array("\/workgroups\/$", "\/workgroups\/extranet\/$");
$arPathClassPattern = array(
	"\/company\/$" => "menu-employees",
	"\/docs\/$" => "menu-docs",
	"\/services\/$" => "menu-services",
	"\/crm\/$" => "menu-crm",
	"\/about\/$" => "menu-company",
	"\/marketplace\/$" => "menu-apps",
);
foreach ($aMenuLinks as $arItem)
{
	$bFound = false;
	foreach($arSkipPattern as $skip)
	{
		preg_match("/".$skip."/is", $arItem[1], $matches);
		if ($matches[0])
		{
			$bFound = true;
			break;
		}
	}
	if ($bFound)
		continue;

	foreach ($arPathClassPattern as $path => $class)
	{
		$matches = "";
		preg_match("/".$path."/is", $arItem[1], $matches);
		if ($matches[0])
		{
			$arItem[3] = array("class" => $class);
			break;
		}
	}
	$arMenuB24[] = $arItem;
}

$arMenuB24[] = Array(
	GetMessage('TOP_MENU_OPENLINES'),
	SITE_DIR."services/openlines/",
	Array('/services/openlines/'),
	Array("class" => "menu-openlines"),
	(!IsModuleInstalled("imopenlines"))?"false":'$GLOBALS["USER"]->IsAdmin()'
);

$arMenuB24[] = Array(
	GetMessage("TOP_MENU_TELEPHONY"),
	SITE_DIR."services/telephony/",
	Array('/services/telephony/'),
	Array("class" => "menu-telephony"),
	'CModule::IncludeModule("voximplant") && Thurly\Voximplant\Security\Helper::isMainMenuEnabled()'
);

$rsSite = CSite::GetList($by="sort", $order="asc", $arFilter=array("ACTIVE" => "Y"));
if (intval($rsSite->SelectedRowsCount())>1)
{
	$arMenuB24[] = Array(
		GetMessage("TOP_MENU_DEPARTMENTS"),
		SITE_DIR."departments/",
		Array(),
		Array("class" => "menu-departments"),
		""
	);
}

$aMenuLinks = array_merge($aMenuLinks, $aMenuB24);
?>