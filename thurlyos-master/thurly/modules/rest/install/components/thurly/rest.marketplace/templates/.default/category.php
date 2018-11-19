<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $component
 * @var CThurlyComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

?>

<div class="mp_wrap">
<?php

if(!$arResult['SLIDER'])
{
	$APPLICATION->IncludeComponent("thurly:rest.marketplace.toolbar", '', array(
		"COMPONENT_PAGE" => $arParams["COMPONENT_PAGE"],
		"TOP_URL" => $arParams["TOP_URL"],
		"CATEGORY_URL" => $arParams["CATEGORY_URL"],
		"DETAIL_URL" => $arParams["DETAIL_URL"],
		"SEARCH_URL" => $arParams["SEARCH_URL"],
		"BUY_URL" => $arParams["BUY_URL"],
		"UPDATES_URL" => $arParams["UPDATES_URL"],
		"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
		"CATEGORY_URL_TPL" => $arParams["CATEGORY_URL_TPL"],
	), $component);
}
?>
	<div class="mp_section">
<?php
if($arResult['SLIDER'])
{
	$APPLICATION->IncludeComponent(
		'thurly:rest.marketplace.iframe',
		'',
		array(
			'COMPONENT_NAME' => 'thurly:rest.marketplace.category',
			'COMPONENT_TEMPLATE_NAME' => '',
			'COMPONENT_PARAMS' => array(
				"CATEGORY" => $arResult["VARIABLES"]["category"],
				"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
				"CATEGORY_URL_TPL" => $arParams["CATEGORY_URL_TPL"],
			),
		),
		$component
	);
}
else
{
	$APPLICATION->IncludeComponent("thurly:rest.marketplace.category", "", array(
		"CATEGORY" => $arResult["VARIABLES"]["category"],
		"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
		"CATEGORY_URL_TPL" => $arParams["CATEGORY_URL_TPL"],
	), $component);
}
?>
	</div>
</div>

