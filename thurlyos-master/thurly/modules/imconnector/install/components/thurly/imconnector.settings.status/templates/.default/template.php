<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Thurly\Main\Localization\Loc;
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

Loc::loadMessages(__FILE__);
$this->addExternalCss('/thurly/js/imconnector/icon.css');
$this->addExternalCss('/thurly/js/imconnector/icon-disabled.css');
?>
<?if(!empty($arResult)):?>
<div class="imconnector-social-connected">
	<?foreach ($arResult as $value):?>
		<<?
		if($arParams['LINK_ON']):?>a<?else:?>span<?endif;
		?> href="#configure<?=$value['ID']?>" id="status-<?=$value['ID']?>" class="connector-icon connector-icon-<?=$value['ID']?> connector-icon-square connector-icon-30<?if(empty($value['STATUS'])):?> connector-icon-disabled<?endif;?>" title="<?=$value['NAME']?>"></<?
		if($arParams['LINK_ON']):?>a<?else:?>span<?endif;
		?>>
	<?endforeach;?>
</div>
<?endif;?>