<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/external_sale/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.config.external_sale",
	"",
	Array(
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>