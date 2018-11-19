<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["PATH_TO_SONET_PROFILE"] = (isset($arParams["PATH_TO_SONET_PROFILE"]) ? $arParams["PATH_TO_SONET_PROFILE"] : SITE_DIR."company/personal/user/#user_id#/");
$arParams["PATH_TO_SONET_PROFILE_EDIT"] = (isset($arParams["PATH_TO_SONET_PROFILE_EDIT"]) ? $arParams["PATH_TO_SONET_PROFILE_EDIT"] : SITE_DIR."company/personal/user/#user_id#/edit/");
$arParams["THUMBNAIL_SIZE"] = (isset($arParams["THUMBNAIL_SIZE"]) ? intval($arParams["THUMBNAIL_SIZE"]) : 100);

$arResult["USER_FULL_NAME"] = CUser::FormatName("#NAME# #LAST_NAME#", array(
	"NAME" => $USER->GetFirstName(),
	"LAST_NAME" => $USER->GetLastName(),
	"SECOND_NAME" => $USER->GetSecondName(),
	"LOGIN" => $USER->GetLogin()
));

$user_id = intval($GLOBALS["USER"]->GetID());

if(defined("BX_COMP_MANAGED_CACHE"))
	$ttl = 2592000;
else
	$ttl = 600;
$cache_id = 'user_avatar_'.$user_id;
$cache_dir = '/bx/user_avatar';
$obCache = new CPHPCache;

if($obCache->InitCache($ttl, $cache_id, $cache_dir))
{
	$arResult["USER_PERSONAL_PHOTO_SRC"] = $obCache->GetVars();
}
else
{
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache($cache_dir);
		}

		$dbUser = CUser::GetByID($GLOBALS["USER"]->GetID());
		$arUser = $dbUser->Fetch();

		$arResult["USER_DATE_REGISTER"] = MakeTimeStamp($arUser["DATE_REGISTER"]);
		$imageFile = false;

		if (intval($arUser["PERSONAL_PHOTO"]) > 0)
		{
			$imageFile = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
			if ($imageFile !== false)
			{
				$arFileTmp = CFile::ResizeImageGet(
					$imageFile,
					array("width" => $arParams["THUMBNAIL_SIZE"], "height" => $arParams["THUMBNAIL_SIZE"]),
					BX_RESIZE_IMAGE_EXACT,
					false
				);
				$arResult["USER_PERSONAL_PHOTO_SRC"] = $arFileTmp["src"];
			}
		}
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->RegisterTag("USER_CARD_".intval($user_id / TAGGED_user_card_size));
			$CACHE_MANAGER->EndTagCache();
		}
	}

	if($obCache->StartDataCache())
	{
		$obCache->EndDataCache($arResult["USER_PERSONAL_PHOTO_SRC"]);
	}
}

// add chache here!!!

if(
	IsModuleInstalled('thurlyos')
	&& COption::GetOptionString('thurlyos', 'network', 'N') == 'Y'
	&& CModule::IncludeModule('socialservices')
)
{
	// also check for B24Net turned on in module settings

	$dbSocservUser = CSocServAuthDB::GetList(
		array(),
		array(
			'USER_ID' => $user_id,
			"EXTERNAL_AUTH_ID" => CSocServThurlyOSNet::ID
		), false, false, array("PERSONAL_WWW")
	);
	$arSocservUser = $dbSocservUser->Fetch();
	if($arSocservUser)
	{
		$arResult['B24NET_WWW'] = $arSocservUser['PERSONAL_WWW'];
	}
}

//B24 helper
if (!function_exists("__getStepByUrl"))
{
	function __getStepByUrl($videoSteps, $url)
	{
		$currentStepId = ($url == SITE_DIR ? $videoSteps[0]["id"] : "");
		foreach ($videoSteps as $step)
		{
			foreach ($step["patterns"] as $pattern)
			{
				if (preg_match($pattern, $url))
				{
					$currentStepId = $step["id"];
					break 2;
				}
			}
		}

		return $currentStepId;
	}
}

if (CModule::IncludeModule('thurlyos') && in_array(CThurlyOS::getLicenseType(), array("project", "demo")))
{
	$arResult["SHOW_LICENSE_BUTTON"] = false;
	if (
		!in_array(LANGUAGE_ID, array("ru", "ua"))
		|| (
			in_array(LANGUAGE_ID, array("ru", "ua")) &&
			(COption::GetOptionString("main", "~controller_date_create", "") + 86400 < time() || !intval(COption::GetOptionString("main", "~controller_date_create", "")))
		)
	)
	{
		$arResult["SHOW_LICENSE_BUTTON"] = true;
		$arResult["B24_LICENSE_PATH"] = CThurlyOS::PATH_LICENSE_ALL;
		if (!isset($_SESSION["B24_LICENSE_BUTTON"]))
			$_SESSION["B24_LICENSE_BUTTON"] = "Y";

		$arResult["LICENSE_BUTTON_COUNTER_URL"] = CThurlyOS::PATH_COUNTER;
		$arResult["HOST_NAME"] = BX24_HOST_NAME;
	}
}

$arResult["CURRENT_STEP_ID"] = "";

$arHelperSteps = array(
	array(
		"id" => "start",
		"patterns" => array(),
	),
	array(
		"id" => "tasks",
		"patterns" => array(
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/tasks/~",
			"~^".SITE_DIR."workgroups/group/\\d+/tasks/~"
		),
	),
	array(
		"id" => "calendar",
		"patterns" => array(
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/calendar/~",
			"~^".SITE_DIR."workgroups/group/\\d+/calendar/~"
		),
	),
	array(
		"id" => "disk",
		"patterns" => array(
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/disk/~",
			"~^".SITE_DIR."docs/~",
			"~^".SITE_DIR."workgroups/group/\\d+/disk/~"
		),
	),
	array(
		"id" => "profile",
		"patterns" => array(
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/edit/$~",
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/passwords/~",
			"~^".SITE_DIR."(company|contacts)/personal/user/\\d+/security/~",
		),
	),
	array(
		"id" => "crm",
		"patterns" => array("~^".SITE_DIR."crm/~"),
	),
	array(
		"id" => "workgroups",
		"patterns" => array("~^".SITE_DIR."workgroups/~"),
	),
	/*array(
		"id" => "company",
		"patterns" => array(
			"~^".SITE_DIR."company/meeting/~",
			"~^".SITE_DIR."company/$~",
			"~^".SITE_DIR."company/vis_structure.php~",
			"~^".SITE_DIR."company/absence.php~",
			"~^".SITE_DIR."company/lists/~",
		),
	),*/
	array(
		"id" => "marketplace",
		"patterns" => array("~^".SITE_DIR."marketplace/~"),
	),
	array(
		"id" => "telephony",
		"patterns" => array("~^".SITE_DIR."telephony/([^/]*\\.php)?$~"),
	),
	/*array(
		"id" => "configs",
		"patterns" => array(
			"~^".SITE_DIR."settings/([^/]*\\.php)?$~",
		)
	),*/
);

if (IsModuleInstalled("extranet") && SITE_ID == COption::GetOptionString("extranet", "extranet_site", ""))
{
	$arHelperSteps[] = array(
		"id" => "extranet",
		"patterns" => array("~^".SITE_DIR."$~"),
	);
}

$currentStepId = __getStepByUrl($arHelperSteps, $APPLICATION->GetCurPage());
$arViewedSteps = CUserOptions::GetOption("thurlyos", "new_helper_views", array());
$arResult["CURRENT_STEP_ID"] = $currentStepId;

$lastHeroView = CUserOptions::GetOption("thurlyos", "new_helper_last", "");

$arResult["SHOW_ANIMATED_FINGER_HERO"] = "Y";
if ($lastHeroView && $lastHeroView+60*60 > time() || isset($_SESSION["HELPER"][$currentStepId]))
{
	$arResult["SHOW_ANIMATED_FINGER_HERO"] = "N";
}

if (IsModuleInstalled('imbot') && COption::GetOptionInt('imbot', 'marta_bot_id', 0) > 0)
{
	$timeWelcomeMessage = CUserOptions::GetOption('imbot', 'marta_welcome_message', 0);
	if (!$timeWelcomeMessage || time()-$timeWelcomeMessage <= 600)
	{
		$arResult["SHOW_ANIMATED_FINGER_HERO"] = "N";
	}
}

if ($arResult["SHOW_ANIMATED_FINGER_HERO"] == "Y")
{
	$arNumViews = CUserOptions::GetOption("thurlyos", "new_helper_num_views", array());

	if ($currentStepId && !in_array($currentStepId, $arViewedSteps))
	{
		if (!isset($arNumViews[$currentStepId]) || $arNumViews[$currentStepId]<10)
		{
			$arNumViews[$currentStepId]++;
			CUserOptions::SetOption("thurlyos", "new_helper_num_views", $arNumViews);
			$_SESSION["HELPER"][$currentStepId] = "Y";
		}
		else
		{
			$arViewedSteps[] = $currentStepId;
			CUserOptions::SetOption("thurlyos", "new_helper_views", $arViewedSteps);
		}

		CUserOptions::SetOption("thurlyos", "new_helper_last", time());
	}

	$arResult["SHOW_ANIMATED_FINGER_HERO"] = ($currentStepId && !in_array($currentStepId, $arViewedSteps)) ? "Y" : "N";
}

$arResult["NEED_CHECK_HELP_NOTIFICATION"] = "N";
$arResult["HELP_NOTIFY_NUM"] = "";

if (CModule::IncludeModule('thurlyos') && !(CModule::IncludeModule("extranet") && SITE_ID == CExtranet::GetExtranetSiteID()))
{
	$helpNotify = CUserOptions::GetOption("thurlyos", "new_helper_notify");
	if (!isset($helpNotify["counter_update_date"]))
	{
		$helpNotify["counter_update_date"] = time();
		CUserOptions::SetOption("thurlyos", "new_helper_notify", $helpNotify);
	}
	$arResult["COUNTER_UPDATE_DATE"] = $helpNotify["counter_update_date"]; //time when user read notifications last time

	if ($arResult["SHOW_ANIMATED_FINGER_HERO"] != "Y" && (!isset($helpNotify["time"]) || $helpNotify["time"] < time()))
	{
		$arResult["NEED_CHECK_HELP_NOTIFICATION"] = "Y";
	}
	if (isset($helpNotify["num"]))
	{
		$arResult["HELP_NOTIFY_NUM"] = intval($helpNotify["num"]);
	}
}

$arResult["SHOW_USER_INFO_SPOTLIGHT"] = false;
$showUserInfoSpotLight = CUserOptions::GetOption("thurlyos", "show_userinfo_spotlight");
if (!empty($showUserInfoSpotLight) && is_array($showUserInfoSpotLight))
{
	if (
		$showUserInfoSpotLight["needShow"] == "Y"
		&& (
			!isset($showUserInfoSpotLight["time"]) ||
			isset($showUserInfoSpotLight["time"])
			&& time() > $showUserInfoSpotLight["time"]
		)
	)
	{
		$firstName = $USER->GetFirstName();
		$lastName = $USER->GetLastName();
		if (
			empty($firstName)
			|| empty($lastName)
			|| empty($arResult["USER_PERSONAL_PHOTO_SRC"])
		)
		{
			$arResult["SHOW_USER_INFO_SPOTLIGHT"] = true;

			if (isset($showUserInfoSpotLight["time"]))
			{
				CUserOptions::DeleteOption("thurlyos", "show_userinfo_spotlight");
			}
		}
		else
		{
			CUserOptions::DeleteOption("thurlyos", "show_userinfo_spotlight");
		}
	}
}
?>
