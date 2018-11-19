<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/product/index.php");
global $APPLICATION;
$APPLICATION->SetTitle(GetMessage("TITLE"));
$APPLICATION->IncludeComponent("thurly:crm.product", ".default", array(
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/crm/product/"
	),
	false
);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>