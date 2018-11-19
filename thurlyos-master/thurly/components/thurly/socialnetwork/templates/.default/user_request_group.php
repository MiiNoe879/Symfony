<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->IncludeComponent(
	"thurly:socialnetwork.user_request_group", 
	"", 
	Array(
		"PATH_TO_USER" 					=> $arResult["PATH_TO_USER"],
		"PATH_TO_GROUP" 				=> $arResult["PATH_TO_GROUP"],
		"PATH_TO_GROUP_REQUESTS" 		=> $arResult["PATH_TO_GROUP_REQUESTS"],
		"PATH_TO_MESSAGES_CHAT" 		=> $arResult["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_VIDEO_CALL" 			=> $arResult["PATH_TO_VIDEO_CALL"],
		"PATH_TO_CONPANY_DEPARTMENT" 	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"PAGE_VAR" 						=> $arResult["ALIASES"]["page"],
		"USER_VAR" 						=> $arResult["ALIASES"]["user_id"],
		"GROUP_VAR" 					=> $arResult["ALIASES"]["group_id"],
		"SET_NAV_CHAIN" 				=> $arResult["SET_NAV_CHAIN"],
		"SET_TITLE" 					=> $arResult["SET_TITLE"],
		"PATH_TO_SMILE" 				=> $arResult["PATH_TO_SMILE"],
		"GROUP_ID" 						=> $arResult["VARIABLES"]["group_id"],
		"NAME_TEMPLATE" 				=> $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" 					=> $arParams["SHOW_LOGIN"],
		"DATE_TIME_FORMAT" 				=> $arResult["DATE_TIME_FORMAT"],		
		"SHOW_YEAR" 					=> $arParams["SHOW_YEAR"],		
		"CACHE_TYPE" 					=> $arParams["CACHE_TYPE"],
		"CACHE_TIME" 					=> $arParams["CACHE_TIME"],
		"USE_THUMBNAIL_LIST" 			=> "N",
		"INLINE" 						=> "Y",
		"USE_AUTOSUBSCRIBE" 			=> "N",
	),
	$component 
);
?>