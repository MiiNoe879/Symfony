<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/reports/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE")/*"Воронка продаж"*/);
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.deal.funnel",
	"",
	Array(
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>