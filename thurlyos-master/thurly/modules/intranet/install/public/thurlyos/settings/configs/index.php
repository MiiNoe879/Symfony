<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/settings/configs/index.php");
$APPLICATION->SetTitle(GetMessage("CONFIG_TITLE"));

$APPLICATION->IncludeComponent("thurly:intranet.configs", "", array());

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");
?>