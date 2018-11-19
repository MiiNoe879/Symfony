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
/** $arResult["CONNECTION_STATUS"]; */
/** $arResult["REGISTER_STATUS"]; */
/** $arResult["ERROR_STATUS"]; */
/** $arResult["SAVE_STATUS"]; */

Loc::loadMessages(__FILE__);

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/thurly/components/thurly/imconnector.settings/templates/.default/template.php');

if(\Thurly\Main\Loader::includeModule("thurlyos"))
{
	CThurlyOS::initLicenseInfoPopupJS();
}

$this->addExternalCss('/thurly/components/thurly/imconnector.settings/templates/.default/style.css');
$this->addExternalCss('/thurly/js/imconnector/icon.css');
$this->addExternalJs('/thurly/components/thurly/imconnector.settings/templates/.default/script.js');
?>
<?if(empty($arResult['RELOAD'])):?>
<div class="im-connector-settings-wrapper">

	<?if(Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_TITLE_' . str_replace('.', '_', strtoupper($arResult['ID']))) != null):?>
		<div class="im-connector-settings-header-title">
			<span class="im-connector-settings-header-title-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_TITLE_' . str_replace('.', '_', strtoupper($arResult['ID'])))?></span>
		</div>
	<?endif;?>

	<?if(Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_DESCRIPTION_' . str_replace('.', '_', strtoupper($arResult['ID']))) != null):?>
	<div class="im-connector-settings-header-container">
		<div class="im-connector-settings-anything">
			<div class="im-connector-settings-social">
				<div class="connector-icon connector-icon-<?=str_replace('.', '_', $arResult['ID'])?> connector-icon-xl" title="<?=$arResult['NAME']?>"></div>
				<div class="im-connector-settings-social-icon"></div>
				<div class="im-connector-settings-social-name"><?=$arResult['NAME_SMALL']?></div>
			</div>
		</div><!--im-connector-settings-video-->

		<div class="im-connector-settings-description">
			<div class="im-connector-settings-header">
				<?=Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_DESCRIPTION_' . str_replace('.', '_', strtoupper($arResult['ID'])))?>
			</div><!--im-connector-settings-header-->
		</div><!--im-connector-settings-description-->
	</div><!--im-connector-settings-header-->
	<?endif;?>

	<div class="im-connector-settings-content">

		<div class="im-connector-settings-title">
			<div class="im-connector-settings-title-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_CONFIGURE_CHANNEL')?></div>
			<div class="im-connector-settings-title-border"></div>
		</div><!--im-connector-settings-title-->

		<div class="im-connector-settings-channel-container">
			<?if((!empty($arResult['LIST_LINE']) && (count($arResult['LIST_LINE'])>1 || !empty($arResult['PATH_TO_ADD_LINE']))) || (!empty($arResult['LIST_LINE']) && count($arResult['LIST_LINE'])==1) || (!empty($arResult['PATH_TO_ADD_LINE'])) || (!empty($arResult['ACTIVE_LINE']['URL_EDIT']))):?>
			<div class="im-connector-settings-channel-options">
				<span class="im-connector-settings-channel-options-name"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_OPEN_LINE')?></span>
				<?if(!empty($arResult['LIST_LINE']) && (count($arResult['LIST_LINE'])>1 || !empty($arResult['PATH_TO_ADD_LINE']))):?>
					<span class="im-connector-settings-channel-options-line" data-role="select-link"><?=$arResult['ACTIVE_LINE']['NAME']?></span>
				<?elseif(!empty($arResult['LIST_LINE']) && count($arResult['LIST_LINE'])==1):?>
					<span class="im-connector-settings-channel-options-tune"><?=$arResult['ACTIVE_LINE']['NAME']?></span>
				<?elseif(!empty($arResult['PATH_TO_ADD_LINE'])):?>
					<a href="<?=$arResult['PATH_TO_ADD_LINE']?>" class="im-connector-settings-channel-options-tune"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_CREATE_OPEN_LINE')?></a>
				<?endif;?>
				<?if(!empty($arResult['ACTIVE_LINE']['URL_EDIT'])):?>
				<a href="<?=$arResult['ACTIVE_LINE']['URL_EDIT']?>" class="im-connector-settings-channel-options-tune"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_CONFIGURE')?></a>
				<?endif;?>
			</div><!--im-connector-settings-channel-options-->
			<?endif;?>

			<div class="im-connector-settings-channel-inner">
			<?if(!empty($arResult['ACTIVE_LINE'])):?>
				<div class="imconnector-new" id="imconnector-new">
				<?$APPLICATION->IncludeComponent(
					$arResult['COMPONENT'],
					"",
					Array(
						"LINE" => $arResult['ACTIVE_LINE']['ID'],
						"AJAX_MODE" => "Y",
						"AJAX_OPTION_ADDITIONAL" => "",
						"AJAX_OPTION_HISTORY" => "N",
						"AJAX_OPTION_JUMP" => "Y",
						"AJAX_OPTION_STYLE" => "Y",
						"INDIVIDUAL_USE" => "Y"
					)
				);?>
				</div>
				<?=$arResult['LANG_JS_SETTING'];?>
			<?elseif(empty($arResult['ACTIVE_LINE']) && !empty($arResult['PATH_TO_ADD_LINE'])):?>
				<?=Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_NO_OPEN_LINE')?>
			<?else:?>
				<?=Loc::getMessage('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_NO_OPEN_LINE_AND_NOT_ADD_OPEN_LINE')?>
			<?endif;?>
			</div><!--im-connector-settings-channel-inner-->

		</div><!--im-connector-settings-channel-container-->



	</div><!--im-connector-settings-content-->

</div><!--im-connector-settings-wrapper-->

<?if(!empty($arResult['LIST_LINE']) && (count($arResult['LIST_LINE'])>1 || !empty($arResult['PATH_TO_ADD_LINE']))):?>
<script>
	BX.ready(function ()
	{
		var selectLink = document.querySelector('[data-role="select-link"]');

		selectLink.addEventListener('click', function ()
		{
			var menuItems = [
				<?if(!empty($arResult['LIST_LINE'])):?>
					<?foreach ($arResult['LIST_LINE'] as $line):?>
				{
					text: "<?=$line['NAME']?>",
					className : ("lenta-sort-item<?if(!empty($line['ACTIVE'])):?> lenta-sort-item-selected<?endif;?>"),
					href : "<?=CUtil::JSEscape($line['URL'])?>",
				},
					<?endforeach;?>
				<?endif;?>
				<?if(!empty($arResult['LIST_LINE']) && !empty($arResult['PATH_TO_ADD_LINE'])):?>
				{ delimiter : true },
				<?endif;?>
				<?if(!empty($arResult['PATH_TO_ADD_LINE'])):?>
				{
					text: "<?=GetMessageJS('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_CREATE_OPEN_LINE')?>",
					className: "lenta-sort-item",
					//href : "<?=CUtil::JSEscape($arResult['PATH_TO_ADD_LINE'])?>",
					onclick: function() {
						var newLine = new BX.ImConnectorConnectorSettings();
						newLine.createLine("<?=str_replace('#ID#', $arResult['ID'], $arResult["PATH_TO_CONNECTOR_LINE"])?>");
						this.popupWindow.close();
					}
				}
				<?endif;?>
			];
			BX.PopupMenu.show("crm-automation-select-popup", this, menuItems, {
				autoHide: true,
				zIndex: 1200,
				offsetLeft: 20,
				angle: true,
				overlay: { backgroundColor: 'transparent' }
			})
		})
	});

	BX.message({
		IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_POPUP_LIMITED_TITLE: '<? echo GetMessageJS('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_POPUP_LIMITED_TITLE') ?>',
		IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_POPUP_LIMITED_TEXT: '<? echo GetMessageJS('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_POPUP_LIMITED_TEXT') ?>',
		IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_ERROR_ACTION: '<? echo GetMessageJS('IIMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_ERROR_ACTION') ?>',
		IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_CLOSE: '<? echo GetMessageJS('IMCONNECTOR_COMPONENT_CONNECTOR_SETTINGS_CLOSE') ?>',
	});
</script>
<?endif;?>
<?else:?>
	<html>
	<body>
	<script>
		window.reloadAjaxImconnector = function(urlReload, idReload)
		{
			parent.window.opener.BX.ajax.insertToNode(urlReload, idReload);
			window.close();
		};
		reloadAjaxImconnector(<?=CUtil::PhpToJSObject($arResult['URL_RELOAD'])?>, <?=CUtil::PhpToJSObject('comp_' . $arResult['RELOAD'])?>);
	</script>
	</body>
	</html>
<?endif;?>