<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/configs/tax/index.php");
global $APPLICATION;

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

$bVatMode = CCrmTax::isVatMode();

if($bVatMode)
{
	$APPLICATION->SetTitle(GetMessage("TITLE"));
	$APPLICATION->IncludeComponent(
		"thurly:crm.config.vat",
		".default",
		array(
			"SEF_MODE" => "Y",
			"SEF_FOLDER" => "/crm/configs/tax/",
		),
		false
	);
}
else
{
	$APPLICATION->SetTitle(GetMessage("TITLE2"));
	$APPLICATION->IncludeComponent(
		"thurly:crm.config.tax",
		".default",
		array(
			"SEF_MODE" => "Y",
			"SEF_FOLDER" => "/crm/configs/tax/",
		),
		false
	);
}
require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>
