<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/timeman/meeting/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));?>
<?
$licenseType = "";
if (\Thurly\Main\Loader::includeModule("thurlyos"))
{
	$licenseType = CThurlyOS::getLicenseType();
}

if (IsModuleInstalled("meeting"))
{
	GetGlobalID();
	$APPLICATION->IncludeComponent("thurly:meetings", ".default", array(
			"SEF_MODE"          => "Y",
			"SEF_FOLDER"        => "/timeman/meeting/",
			"SEF_URL_TEMPLATES" => array(
				"list"         => "",
				"meeting"      => "meeting/#MEETING_ID#/",
				"meeting_edit" => "meeting/#MEETING_ID#/edit/",
				"meeting_copy" => "meeting/#MEETING_ID#/copy/",
				"item"         => "item/#ITEM_ID#/",
			)
		),
		false
	);
}
elseif (!(!IsModuleInstalled("meeting") && in_array($licenseType, array("company", "edu", "nfr"))))
{
	if (LANGUAGE_ID == "de" || LANGUAGE_ID == "la")
		$lang = LANGUAGE_ID;
	else
		$lang = LangSubst(LANGUAGE_ID);
	?>
	<p><?=GetMessage("TARIFF_RESTRICTION_TEXT")?></p>
	<div style="text-align: center;"><img src="images/<?=$lang?>/meeting.png"/></div>
	<p><?=GetMessage("TARIFF_RESTRICTION_TEXT2")?></p>
	<br/>
	<div style="text-align: center;"><?CThurlyOS::showTariffRestrictionButtons("meeting")?></div>
	<?
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>