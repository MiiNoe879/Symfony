<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetPageProperty("BodyClass", "page-one-column");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/timeman/timeman.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));
$licenseType = "";
if (\Thurly\Main\Loader::includeModule("thurlyos"))
{
	$licenseType = CThurlyOS::getLicenseType();
}
?> <?

if (IsModuleInstalled("timeman"))
{
	$APPLICATION->IncludeComponent("thurly:timeman.report", ".default", array());
}
elseif (!(!IsModuleInstalled("timeman") && in_array($licenseType, array("company", "edu", "nfr"))))
{
	if (LANGUAGE_ID == "de" || LANGUAGE_ID == "la")
		$lang = LANGUAGE_ID;
	else
		$lang = LangSubst(LANGUAGE_ID);
?>
	<p><?=GetMessage("TARIFF_RESTRICTION_TEXT")?></p>
	<div style="text-align: center;"><img src="images/<?=$lang?>/timeman.png"/></div>
	<p><?=GetMessage("TARIFF_RESTRICTION_TEXT2")?></p>
	<br/>
	<div style="text-align: center;"><?CThurlyOS::showTariffRestrictionButtons("timeman")?></div>
<?
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>