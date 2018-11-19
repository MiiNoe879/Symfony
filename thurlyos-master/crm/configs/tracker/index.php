<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/thurlyos/public/crm/configs/tracker/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));

$APPLICATION->IncludeComponent(
		"thurly:crm.config.tracker",
		".default"
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>