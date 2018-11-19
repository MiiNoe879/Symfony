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
 */

$this->SetViewTarget('pagetitle', 100);

foreach($arResult["ITEMS"] as $index => $arItem):
?>
	<a href="<?=\Thurly\Main\Text\Converter::getHtmlConverter()->encode($arItem["LINK"])?>" class="webform-small-button <?=$arItem["PARAMS"]["class"]?>"><?=$arItem["TEXT"]?></a>
<?php
endforeach;

$this->EndViewTarget();
?>
