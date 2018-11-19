<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle(GetMessage("CRM_PAGE_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.report",
	"",
	Array(
		"SEF_MODE" => "Y",
		"REPORT_ID" => $_REQUEST["report_id"],
		"SEF_FOLDER" => "#SITE_DIR#crm/reports/report/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"report" => "report/",
			"construct" => "construct/#report_id#/#action#/",
			"show" => "view/#report_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"report" => Array(),
			"construct" => Array(),
			"show" => Array()
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>