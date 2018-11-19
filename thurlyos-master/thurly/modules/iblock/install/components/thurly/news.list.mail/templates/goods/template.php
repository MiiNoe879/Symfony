<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CThurlyComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CThurlyComponent $component */
//$this->setFrameMode(true);

$itemIdList = array();
foreach($arResult["ITEMS"] as $item)
{
	$itemIdList[] = $item['ID'];
}

\Thurly\Main\Mail\EventMessageThemeCompiler::includeComponent(
	"thurly:catalog.show.products.mail",
	"",
	Array(
		"LIST_ITEM_ID" => $itemIdList
	)
);