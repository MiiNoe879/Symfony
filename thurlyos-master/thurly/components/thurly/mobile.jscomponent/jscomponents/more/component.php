<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $USER;

CModule::IncludeModule("mobile");
CModule::IncludeModule("mobileapp");

function sortMenu($item, $anotherItem)
{
	$itemSort = (array_key_exists("sort", $item) ? $item["sort"] : 100);
	$anotherSort = (array_key_exists("sort", $anotherItem) ? $anotherItem["sort"] : 100);
	if ($itemSort > $anotherSort)
	{
		return 1;
	}

	if ($itemSort == $anotherSort)
	{
		return 0;
	}

	return -1;
}

$USER_ID = $USER->GetID();
$arResult = array();
$ttl = (defined("BX_COMP_MANAGED_CACHE") ? 2592000 : 600);
$extEnabled = IsModuleInstalled('extranet');

$cache_id = 'user_mobile_menu__' . $USER_ID . '_' . $extEnabled . '_' . LANGUAGE_ID . '_' . CSite::GetNameFormat(false);
$cache_dir = '/bx/mobile_menu_js/user_' . $USER_ID;
$obCache = new CPHPCache;

if ($obCache->InitCache($ttl, $cache_id, $cache_dir))
{
	$arResult = $obCache->GetVars();
}
else
{
	global $CACHE_MANAGER;
	$CACHE_MANAGER->StartTagCache($cache_dir);
	$arResult["menu"] = include(".mobile_menu.php");
	$host = Thurly\Main\Context::getCurrent()->getServer()->getHttpHost();
	$host = preg_replace("/:(80|443)$/", "", $host);
	$arResult["host"] = htmlspecialcharsbx($host);
	$user = $USER->GetByID($USER_ID)->GetNext();
	$arResult["user"] = array(
		"fullName" => CUser::FormatName(CSite::GetNameFormat(false), array(
			"NAME" => $USER->GetFirstName(),
			"LAST_NAME" => $USER->GetLastName(),
			"SECOND_NAME" => $USER->GetSecondName(),
			"LOGIN" => $USER->GetLogin()
		))
	);

	$arResult["user"]["avatar"] = "";

	if ($user["PERSONAL_PHOTO"])
	{
		$imageFile = CFile::GetFileArray($user["PERSONAL_PHOTO"]);
		if ($imageFile !== false)
		{
			$avatar = CFile::ResizeImageGet($imageFile, array("width" => 1200, "height" => 1020), BX_RESIZE_IMAGE_EXACT, false, false, false, 50);
			$arResult["user"]["avatar"] = $avatar["src"];
		}
	}

	$CACHE_MANAGER->RegisterTag('sonet_group');
	$CACHE_MANAGER->RegisterTag('USER_CARD_' . intval($USER_ID / TAGGED_user_card_size));
	$CACHE_MANAGER->RegisterTag('sonet_user2group_U' . $USER_ID);
	$CACHE_MANAGER->RegisterTag('mobile_custom_menu');
	$CACHE_MANAGER->EndTagCache();

	if ($obCache->StartDataCache())
	{
		$obCache->EndDataCache($arResult);
	}
}

$events = \Thurly\Main\EventManager::getInstance()->findEventHandlers("mobile", "onMobileMenuStructureBuilt");
if (count($events) > 0)
{
	$menu = ExecuteModuleEventEx($events[0], array($arResult["menu"]));
	$arResult["menu"] = $menu;
}

$arResult["menu"][] = array(
	"title" => "",
	"sort" => 0,
	"items" => array(
		array(
			"title" => $arResult["user"]["fullName"],
			"imageUrl" => $arResult["user"]["avatar"],
			"subtitle" => GetMessage("MENU_VIEW_PROFILE"),
			"params" => array(
				"url" => SITE_DIR . "mobile/users/?ID=" . $user["ID"]
			),
		)
	)
);



usort($arResult["menu"], 'sortMenu');


unset($obCache);

return $arResult;