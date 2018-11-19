<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle(GetMessage("CRM_PAGE_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.event.view",
	"",
	Array(
		"ENTITY_ID" => "",
		"EVENT_COUNT" => "20",
		"EVENT_ENTITY_LINK" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>