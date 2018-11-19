<? 
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php"); 
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/configs/currency/index.php");
global $APPLICATION;

$APPLICATION->SetTitle(GetMessage("TITLE"));
$APPLICATION->IncludeComponent(
	"thurly:crm.currency", 
	".default", 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/crm/configs/currency/",
	),
	false
);
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php"); 
?>
