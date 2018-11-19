<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/locations/index.php");
global $APPLICATION;

$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
$APPLICATION->IncludeComponent(
	"thurly:crm.config.locations",
	".default",
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/crm/configs/locations/"
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>
