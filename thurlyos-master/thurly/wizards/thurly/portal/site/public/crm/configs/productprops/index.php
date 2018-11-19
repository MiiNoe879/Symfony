<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/productprops/index.php");
global $APPLICATION;

$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
$APPLICATION->IncludeComponent(
	"thurly:crm.product.props",
	".default",
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#crm/configs/productprops/"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>
