<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
$APPLICATION->SetPageProperty("BodyClass", "page-one-column");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/timeman/work_report.php");

$APPLICATION->SetTitle(GetMessage("TITLE"));
$licenseType = "";
if (\Thurly\Main\Loader::includeModule("thurlyos"))
{
	$licenseType = CThurlyOS::getLicenseType();
}
?>
<?
if (IsModuleInstalled("timeman"))
{
	$APPLICATION->IncludeComponent(
		"thurly:timeman.report.weekly",
		"",
		Array(
		),
		false
	);
}
elseif (!(!IsModuleInstalled("timeman") && in_array($licenseType, array("company", "edu", "nfr"))))
{
	if (LANGUAGE_ID == "de" || LANGUAGE_ID == "la")
		$lang = LANGUAGE_ID;
	else
		$lang = LangSubst(LANGUAGE_ID);
	?>
	<p><?=GetMessage("TARIFF_RESTRICTION_TEXT")?></p>
	<div style="text-align: center;"><img src="images/<?=$lang?>/workreport.png"/></div>
	<p><?=GetMessage("TARIFF_RESTRICTION_TEXT2")?></p>
	<br/>
	<div style="text-align: center;"><?CThurlyOS::showTariffRestrictionButtons("workreport")?></div>
	<?
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>