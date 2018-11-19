<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
global $APPLICATION;
$APPLICATION->SetTitle(GetMessage("CRM_PAGE_TITLE"));
$APPLICATION->IncludeComponent(
	"thurly:crm.product", 
	".default", 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#crm/product/"
	),
	false
);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
