<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/configs/fields/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.config.fields",
	"",
	Array(
		"SEF_MODE" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CACHE_NOTES" => "",
		"SEF_FOLDER" => "/crm/configs/fields/",
		"SEF_URL_TEMPLATES" => Array(
			"ENTITY_LIST_URL" => "",
			"FIELDS_LIST_URL" => "#entity_id#/",
			"FIELD_EDIT_URL" => "#entity_id#/edit/#field_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"ENTITY_LIST_URL" => Array(),
			"FIELDS_LIST_URL" => Array(),
			"FIELD_EDIT_URL" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>