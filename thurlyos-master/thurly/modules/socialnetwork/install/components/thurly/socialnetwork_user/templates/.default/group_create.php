<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CThurlyComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$pageId = "";
include("util_menu.php");


$APPLICATION->IncludeComponent(
	"thurly:socialnetwork.group_create.ex",
	"", 
	Array(
		"PATH_TO_USER" => $arResult["PATH_TO_USER"],
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
		"PATH_TO_GROUP_GENERAL" => $arParams["PATH_TO_GROUP_GENERAL"],
		"PATH_TO_GROUP_CREATE" => $arResult["PATH_TO_GROUP_CREATE"],
		"PAGE_VAR" => $arResult["ALIASES"]["page"],
		"USER_VAR" => $arResult["ALIASES"]["user_id"],
		"GROUP_VAR" => $arResult["ALIASES"]["group_id"],
		"SET_NAV_CHAIN" => $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" => $arResult["SET_TITLE"],
		"USER_ID" => $arResult["VARIABLES"]["user_id"],
		"GROUP_ID" => $arResult["VARIABLES"]["group_id"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"USE_KEYWORDS" => $arParams["GROUP_USE_KEYWORDS"],
		"USE_AUTOSUBSCRIBE" => "N",
		"FIRST_ROW" => ($_GET["firstRow"] == "project" ? "project" : ""),
	),
	$this->getComponent()
);
?>