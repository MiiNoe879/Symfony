<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/deal_category/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
$APPLICATION->IncludeComponent(
	"thurly:crm.deal_category",
	".default",
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/crm/configs/deal_category/"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>
