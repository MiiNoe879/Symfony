<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CThurlyComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Thurly\Main\ModuleManager;
use Thurly\Main\Loader;
use Thurly\Socialnetwork\UserToGroupTable;
use Thurly\Socialnetwork\Integration;

CSocNetLogComponent::processDateTimeFormatParams($arParams);

if (is_array($arResult["Owner"]))
{
	if (intval($arResult["Owner"]["USER_PERSONAL_PHOTO"]) > 0)
	{
		$arImage = CFile::ResizeImageGet(
			$arResult["Owner"]["USER_PERSONAL_PHOTO"], 
			array("width" => 100, "height" => 100),
			BX_RESIZE_IMAGE_EXACT
		);
	}
	else
	{
		$arImage = array("src" => "");
	}

	$arResult["Owner"]["USER_PERSONAL_PHOTO_FILE"]["SRC"] = $arImage["src"];
}

if (is_array($arResult["Moderators"]["List"]))
{
	foreach($arResult["Moderators"]["List"] as $key => $arModerator)
	{
		if (is_array($arModerator))
		{
			if (intval($arModerator["USER_PERSONAL_PHOTO"]) > 0)
			{
				$arImage = CFile::ResizeImageGet(
					$arModerator["USER_PERSONAL_PHOTO"], 
					array("width" => 100, "height" => 100),
					BX_RESIZE_IMAGE_EXACT
				);
			}
			else
			{
				$arImage = array("src" => "");
			}

			$arResult["Moderators"]["List"][$key]["USER_PERSONAL_PHOTO_FILE"]["SRC"] = $arImage["src"];
		}
	}
}

if (is_array($arResult["Members"]["List"]))
{
	foreach($arResult["Members"]["List"] as $key => $arMember)
	{
		if (is_array($arMember))
		{
			if (intval($arMember["USER_PERSONAL_PHOTO"]) > 0)
			{
				$arImage = CFile::ResizeImageGet(
					$arMember["USER_PERSONAL_PHOTO"], 
					array("width" => 100, "height" => 100),
					BX_RESIZE_IMAGE_EXACT
				);
			}
			else
			{
				$arImage = array("src" => "");
			}
			
			$arResult["Members"]["List"][$key]["USER_PERSONAL_PHOTO_FILE"]["SRC"] = $arImage["src"];
		}
	}
}

$arResult["Urls"]["Delete"] = CComponentEngine::MakePathFromTemplate(
	$arParams["PATH_TO_GROUP_DELETE"],
	array("group_id" => $arResult["Group"]["ID"])
);

$arResult["Urls"]["content_search"] = CComponentEngine::MakePathFromTemplate(
	$arParams["PATH_TO_GROUP_CONTENT_SEARCH"],
	array("group_id" => $arResult["Group"]["ID"])
);

if (!isset($arResult["CanView"]))
{
	$arResult["CanView"] = array();
}

$arResult["CanView"]["content_search"] = (
	is_array($arResult["ActiveFeatures"]) && array_key_exists("search", $arResult["ActiveFeatures"]) &&
	CSocNetFeaturesPerms::CanPerformOperation(
		$GLOBALS["USER"]->GetID(),
		SONET_ENTITY_GROUP,
		$arResult["Group"]["ID"],
		"search",
		"view",
		CSocNetUser::IsCurrentUserModuleAdmin()
	)
);

$arResult["FAVORITES"] = false;
if ($USER->IsAuthorized())
{
	$res = \Thurly\Socialnetwork\WorkgroupFavoritesTable::getList(array(
		'filter' => array(
			'GROUP_ID' => $arResult["Group"]["ID"],
			'USER_ID' => $USER->getId()
		)
	));
	$arResult["FAVORITES"] = ($res->fetch());
}

$arResult["bChatActive"] = (
	ModuleManager::isModuleInstalled('im')
	&& Integration\Im\Chat\Workgroup::getGroupChatAvailable($arResult["Group"]["ID"])
	&& in_array($arResult["CurrentUserPerms"]["UserRole"], UserToGroupTable::getRolesMember())
);

$arResult["Types"] = \Thurly\Socialnetwork\Item\Workgroup::getTypes(array(
	'currentExtranetSite' => $arResult["bExtranet"]
));

$arResult["Group"]["IS_EXTRANET_GROUP"] = (
	Loader::includeModule("extranet")
	&& CExtranet::isExtranetSocNetGroup($arResult["Group"]["ID"])
	? "Y"
	: "N"
);
