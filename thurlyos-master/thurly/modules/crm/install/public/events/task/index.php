<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetTitle(GetMessage("CRM_PAGE_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.activity.task.list",
	"",
	Array(
		"ACTIVITY_TASK_COUNT" => "20",
		"ACTIVITY_ENTITY_LINK" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>