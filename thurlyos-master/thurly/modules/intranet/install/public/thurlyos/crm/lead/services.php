<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/lead/services.php");
$APPLICATION->SetTitle(GetMessage("TITLE")/*"Веб сервис"*/);
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.lead.webservice",
	"",
	Array(
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>