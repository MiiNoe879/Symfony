<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->IncludeComponent(
	"thurly:forum.pm.search",
	"",
	array(
		"PM_USER_PAGE" => $arResult["PM_USER_PAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		"URL_TEMPLATES_PM_LIST" => $arResult["URL_TEMPLATES_PM_LIST"],
		"URL_TEMPLATES_PM_READ" => $arResult["URL_TEMPLATES_PM_READ"],
		"URL_TEMPLATES_PM_EDIT" => $arResult["URL_TEMPLATES_PM_EDIT"],
		"URL_TEMPLATES_PM_SEARCH" => $arResult["URL_TEMPLATES_PM_SEARCH"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"],
		"NAME_TEMPLATE"	=> $arParams["NAME_TEMPLATE"],
		"SEO_USER" => $arParams["SEO_USER"]
	),
	$component
);
?>