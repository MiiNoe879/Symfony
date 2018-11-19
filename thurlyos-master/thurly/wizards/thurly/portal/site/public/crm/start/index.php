<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/start/index.php");
global $APPLICATION;
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
$APPLICATION->IncludeComponent(
	"thurly:crm.channel_tracker",
	"",
	array()
);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>