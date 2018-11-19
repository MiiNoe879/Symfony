<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE")/*"Задачи"*/);
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.activity.task.list",
	"",
	Array(
		"ACTIVITY_TASK_COUNT" => "20",
		"ACTIVITY_ENTITY_LINK" => "Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>