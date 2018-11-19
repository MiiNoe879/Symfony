<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var CThurlyComponentTemplate $this
 */
$arParams = array_merge($arParams, array(
	'SHOW_SECTIONS_BAR' => 'Y',
	'SHOW_SECTION_COUNTERS' => 'Y'
));

$this->__component->arResult = $APPLICATION->IncludeComponent(
	'thurly:mobile.tasks.filter',
	'.default',
	$arParams,
	$this->__component
);
