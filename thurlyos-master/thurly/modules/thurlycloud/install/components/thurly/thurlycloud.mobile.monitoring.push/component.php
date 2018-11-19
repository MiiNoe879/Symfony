<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!$USER->CanDoOperation("thurlycloud_monitoring"))
{
	ShowError(GetMessage("BCLMME_ACCESS_DENIED"));
	return;
}

if (!CModule::IncludeModule('thurlycloud'))
{
	ShowError(GetMessage("BCLMME_BC_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule('mobileapp'))
{
	ShowError(GetMessage("BCLMME_MA_NOT_INSTALLED"));
	return;
}

$arResult = array(
	"CURRENT_PAGE" => $APPLICATION->GetCurPage(),
	"AJAX_URL" => $componentPath."/ajax.php",
	"DOMAIN" => isset($_REQUEST["domain"]) ? $_REQUEST["domain"] : "",
	"DOMAINS_NAMES" => array(),
	"OPTIONS" => array()
);

$monitoring = CThurlyCloudMonitoring::getInstance();
$monitoringResults = $monitoring->getMonitoringResults();

if($arResult["DOMAIN"] != "")
{
	$arUserDevices = CThurlyCloudMobile::getUserDevices($USER->GetID());
	$arMonDevices = $monitoring->getDevices($arResult["DOMAIN"]);

	foreach ($arUserDevices as $deviceId)
	{
		if(in_array($deviceId, $arMonDevices))
			$arResult["OPTIONS"]["SUBSCRIBE"] = 'Y';
		else
			$arResult["OPTIONS"]["SUBSCRIBE"] = 'N';
	}
}
else
{
	foreach ($monitoringResults as $domainName => $tmp)
		$arResult["DOMAINS_NAMES"][] = $domainName;
}

CJSCore::Init('ajax');

$this->IncludeComponentTemplate();
?>