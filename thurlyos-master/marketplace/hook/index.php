<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle("");
?><?$APPLICATION->IncludeComponent(
	"thurly:rest.hook",
	".default", 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => SITE_DIR."marketplace/hook/",
		"COMPONENT_TEMPLATE" => ".default",
		"SEF_URL_TEMPLATES" => array(
			"list" => "",
			"event_list" => "event/",
			"event_edit" => "event/#id#/",
			"ap_list" => "ap/",
			"ap_edit" => "ap/#id#/",
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>