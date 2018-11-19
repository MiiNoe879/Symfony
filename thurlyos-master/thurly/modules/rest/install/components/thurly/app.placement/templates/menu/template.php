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
 */

$c = \Thurly\Main\Text\Converter::getHtmlConverter();

$applicationIdList = array();
foreach($arResult['APPLICATION_LIST'] as $app):
	$applicationIdList[] = $app['ID'];
endforeach;
?>

<script>
	BX.rest.AppLayout.setPlacement('<?=$arResult['PLACEMENT']?>', new BX.rest.PlacementMenu({
		placement: '<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>',
		layout: 'rest_menu_<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>',
		node: 'rest_menu_<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>_#ID#',
		list: <?=\CUtil::PhpToJSObject($applicationIdList)?>,
		current: <?=intval($arResult['APPLICATION_CURRENT'])?>,
		unload: false
	}));

<?php
if($arParams['INTERFACE_EVENT']):
?>
	BX.rest.AppLayout.initializePlacementByEvent('<?=\CUtil::JSEscape($arResult['PLACEMENT'])?>', '<?=\CUtil::JSEscape($arParams['INTERFACE_EVENT'])?>');
<?php
endif;
?>
</script>
