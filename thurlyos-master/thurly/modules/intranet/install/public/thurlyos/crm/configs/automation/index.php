<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/configs/automation/index.php");

$APPLICATION->SetTitle(GetMessage("TITLE"));
?><?
$APPLICATION->IncludeComponent(
	"thurly:crm.config.automation",
	"",
	Array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/crm/configs/automation/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"edit" => "#entity#/#category#/",
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"edit" => Array(),
		)
	)
);

?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>