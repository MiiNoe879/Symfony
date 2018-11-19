<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->SetAdditionalCSS("/thurly/components/thurly/voximplant.main/templates/.default/telephony.css");

$APPLICATION->IncludeComponent("thurly:voximplant.documents", "", array());

$APPLICATION->IncludeComponent("thurly:voximplant.lines.default", "", array());

$APPLICATION->IncludeComponent("thurly:voximplant.interface.config", "", array());

$APPLICATION->IncludeComponent("thurly:voximplant.settings.crm", "", array());

$APPLICATION->IncludeComponent("thurly:voximplant.settings.combinations", "", array());

if($arResult['SHOW_AUTOPAY'])
{
	$APPLICATION->IncludeComponent("thurly:voximplant.autopayment", "", array());
}

$APPLICATION->IncludeComponent("thurly:voximplant.blacklist", "", array());
?>

