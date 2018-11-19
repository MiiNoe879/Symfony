<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @global CMain $APPLICATION
 */

CUtil::InitJSCore(array('window', 'ajax'));
$APPLICATION->AddHeadScript('/thurly/js/main/utils.js');
$APPLICATION->AddHeadScript('/thurly/js/main/popup_menu.js');
$APPLICATION->AddHeadScript('/thurly/js/main/dd.js');

$APPLICATION->SetAdditionalCSS('/thurly/themes/.default/pubstyles.css');

$theme = '';
if(isset($arResult["OPTIONS"]))
{
	$theme = $arResult["OPTIONS"]["theme"];
}
elseif(CPageOption::GetOptionString("main.interface", "use_themes", "Y") !== "N")
{
	$theme = CGridOptions::GetTheme($arParams["GRID_ID"]);
}

if($theme <> '')
{
	$APPLICATION->SetAdditionalCSS($templateFolder.'/themes/'.$theme.'/style.css');
}

$currentBodyClass = $APPLICATION->GetPageProperty("BodyClass", false);
$APPLICATION->SetPageProperty("BodyClass", ($currentBodyClass ? $currentBodyClass." " : "")."flexible-layout");
