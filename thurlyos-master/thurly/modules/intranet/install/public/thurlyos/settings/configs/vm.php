<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/settings/configs/vm.php");
$APPLICATION->SetTitle(GetMessage("VM_TITLE"));

$APPLICATION->IncludeComponent("thurly:intranet.configs.vm", "", array());

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>