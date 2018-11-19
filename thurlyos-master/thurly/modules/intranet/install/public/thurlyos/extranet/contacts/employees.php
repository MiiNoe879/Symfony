<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/extranet/contacts/employees.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));
?>

<?$APPLICATION->IncludeComponent("thurly:intranet.search", ".default", array(
	"STRUCTURE_PAGE" => "",
	"PM_URL" => "/extranet/contacts/personal/messages/chat/#USER_ID#/",
	"PATH_TO_VIDEO_CALL" => "/extranet/contacts/personal/video/#USER_ID#/",
	"STRUCTURE_FILTER" => "contacts",
	"FILTER_1C_USERS" => "N",
	"USERS_PER_PAGE" => "50",
	"FILTER_SECTION_CURONLY" => "N",
	"NAME_TEMPLATE" => "",
	"SHOW_ERROR_ON_NULL" => "Y",
	"SHOW_NAV_TOP" => "N",
	"SHOW_NAV_BOTTOM" => "Y",
	"SHOW_UNFILTERED_LIST" => "Y",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "Y",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"FILTER_NAME" => "contacts_search",
	"FILTER_DEPARTMENT_SINGLE" => "Y",
	"FILTER_SESSION" => "N",
	"DEFAULT_VIEW" => "list",
	"LIST_VIEW" => "list",
	"USER_PROPERTY_TABLE" => array(
		0 => "PERSONAL_PHOTO",
		1 => "FULL_NAME",
		2 => "PERSONAL_PHONE",
		3 => "WORK_POSITION",
		4 => "UF_DEPARTMENT",
	),
	"USER_PROPERTY_EXCEL" => array(
		0 => "FULL_NAME",
		1 => "EMAIL",
		2 => "PERSONAL_PHONE",
		3 => "PERSONAL_FAX",
		4 => "PERSONAL_MOBILE",
		5 => "WORK_POSITION",
		6 => "UF_DEPARTMENT",
	),
	"USER_PROPERTY_LIST" => array(
		0 => "EMAIL",
		1 => "PERSONAL_ICQ",
		2 => "PERSONAL_PHONE",
		3 => "PERSONAL_FAX",
		4 => "PERSONAL_MOBILE",
		5 => "UF_DEPARTMENT",
		6 => "PERSONAL_PHOTO",
	),
	"EXTRANET_TYPE" => "employees",
	"AJAX_OPTION_ADDITIONAL" => "",
	"PATH_TO_USER" => "/extranet/contacts/personal/user/#user_id#/",
	"PATH_TO_USER_EDIT" => "/extranet/contacts/personal/user/#user_id#/edit/",
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>