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
 * @var CThurlyComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

?>

<div class="mp_wrap">
<?php
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
?>
	<div class="mp_section">
<?php
$APPLICATION->IncludeComponent("thurly:rest.marketplace.updates", "", array(
	"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
	"CATEGORY_URL_TPL" => $arParams["CATEGORY_URL_TPL"],
), $component);
?>
	</div>
</div>