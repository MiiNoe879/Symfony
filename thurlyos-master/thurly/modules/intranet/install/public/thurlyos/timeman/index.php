<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/timeman/index.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));

if (COption::GetOptionString("thurlyos", "absence_limits_enabled", "") != "Y" || \Thurly\ThurlyOS\Feature::isFeatureEnabled("absence"))
{
	$workTimeStart = 9;
	$workTimeEnd = 18;
	if (Thurly\Main\Loader::includeModule("calendar"))
	{
		$arCalendarSet = CCalendar::GetSettings(array('getDefaultForEmpty' => false));
		if (intval($arCalendarSet['work_time_start']))
			$workTimeStart = $arCalendarSet['work_time_start'];
		if (intval($arCalendarSet['work_time_end']))
			$workTimeEnd = $arCalendarSet['work_time_end'];
	}
	$APPLICATION->IncludeComponent("thurly:intranet.absence.calendar", ".default", Array(
		 "FILTER_NAME"	=>	"absence",
		 "FILTER_SECTION_CURONLY"	=>	"N",
		 "DAY_START" => $workTimeStart,
		 "DAY_FINISH" => $workTimeEnd
	));
}
else
{
	if (LANGUAGE_ID == "de" || LANGUAGE_ID == "la")
		$lang = LANGUAGE_ID;
	else
		$lang = LangSubst(LANGUAGE_ID);
	?>
	<p><?=GetMessage("TARIFF_RESTRICTION_TEXT")?></p>
	<div style="text-align: center;"><img src="images/<?=$lang?>/absence.png"/></div>
	<p><?=GetMessage("TARIFF_RESTRICTION_TEXT2")?></p>
	<br/>
	<div style="text-align: center;"><?CThurlyOS::showTariffRestrictionButtons("absence")?></div>
	<?
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>