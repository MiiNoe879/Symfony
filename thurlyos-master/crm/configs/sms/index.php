<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/sms/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));

$APPLICATION->IncludeComponent(
	"thurly:crm.config.sms",
	".default"
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>