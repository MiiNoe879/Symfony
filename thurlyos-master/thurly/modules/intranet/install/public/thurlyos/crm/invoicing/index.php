<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/invoicing/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.invoice.invoicing",
	".default",
	Array()
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>