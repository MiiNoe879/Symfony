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
?>
<?if(empty($arParams['INDIVIDUAL_USE'])):?>
<script>
	BX.ready(function(){
		<?if(empty($arResult["STATUS"])):?>
		BX.addClass(BX('status-<?=$arResult["CONNECTOR"]?>'), 'connector-icon-disabled');
		<?else:?>
		BX.removeClass(BX('status-<?=$arResult["CONNECTOR"]?>'), 'connector-icon-disabled');
		<?endif;?>
	});
</script>
<?endif;?>

<form action="<?=$arResult["URL"]["DELETE"]?>" method="post" id="form_delete_<?=$arResult["CONNECTOR"]?>">
	<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
	<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_del" value="Y">
	<?=thurly_sessid_post();?>
</form>
<a name="configure<?=$arResult["CONNECTOR"]?>"></a>
<?if(!empty($arResult["ACTIVE_STATUS"]) && empty($arResult["PAGE"])):?>
	<?if(empty($arResult["STATUS"])):?>
		<div class="imconnector-block">
			<div class="imconnector-baseconnector-block-title imconnector-baseconnector-block-title-no-connect">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<?if($arResult["ERROR_STATUS"]):?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BASECONNECTOR_CONNECTOR_ERROR_STATUS')?></td>
							<td></td>
						</tr>
						<tr>
							<td colspan="2">
								<a href="<?=$arResult["URL"]["SIMPLE_FORM"]?>"
								   class="imconnector-connect-link-gray"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_ERROR_LINK_FIX')?></a>
							<span class="imconnector-connect-link-gray"
								  onclick="popupShow(<?=CUtil::PhpToJSObject($arResult["CONNECTOR"])?>)"
								  title="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DISABLE_TITLE')?>"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DISABLE')?></span>
							</td>
						</tr>
					</table>
				<?else:?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SETTING_IS_NOT_COMPLETED')?></td>
							<td></td>
						</tr>
						<tr>
							<td colspan="2">
								<a href="<?=$arResult["URL"]["SIMPLE_FORM"]?>"
								   class="imconnector-connect-link-gray"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_CONTINUE_WITH_THE_SETUP')?></a>
							<span class="imconnector-connect-link-gray"
								  onclick="popupShow(<?=CUtil::PhpToJSObject($arResult["CONNECTOR"])?>)"
								  title="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DISABLE_TITLE')?>"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DISABLE')?></span>
							</td>
						</tr>
					</table>
				<?endif;?>
			</div>
		</div>
	<?else:?>
		<div class="imconnector-block">
			<div class="imconnector-baseconnector-block-title">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<table class="imconnector-connect-table">
					<?if(!empty($arResult["FORM"]["USER"]["INFO"])):?>
							<tr>
								<td>Header connection information</td>
								<td>
									Connection information
								</td>
							</tr>
					<?endif;?>

					<tr>
						<td colspan="2">
							<a href="<?=$arResult["URL"]["SIMPLE_FORM"]?>"
							   class="imconnector-connect-link-gray"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_CHANGE_SETTING')?></a>
							<span class="imconnector-connect-link-gray"
								  onclick="popupShow(<?=CUtil::PhpToJSObject($arResult["CONNECTOR"])?>)"
								  title="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DISABLE_TITLE')?>"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DISABLE')?></span>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?endif;?>
<?else:?>
	<div class="imconnector-item<?
	if(!empty($arResult["PAGE"]) || !empty($arParams['INDIVIDUAL_USE'])):?> imconnector-item-show<?endif;
	?>">
		<?if(!empty($arResult["ACTIVE_STATUS"])):?>
			<span class="imconnector-back" onclick="popupShow(<?=CUtil::PhpToJSObject($arResult["CONNECTOR"])?>)"
				  title="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DISABLE_TITLE')?>"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DISABLE')?></span>
		<?endif;?>
	<label class="imconnector-baseconnector-button" for="imconnector-baseconnector" onclick="showHideImconnectors(this)">
		<?=$arResult["NAME"]?>
		<span class="imconnector-button-show"
			  id="imconnector-baseconnector-button-show"><?
			if(!empty($arResult["PAGE"])):?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_COLLAPSE')?><?
			else:?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DEPLOY')?><?
			endif;?></span>
	</label>
	<input type="checkbox"
		   id="imconnector-baseconnector"
		   class="imconnector-checkbox" hidden>
	<div class="imconnector-wrapper">
			<?if(!empty($arResult["ACTIVE_STATUS"])):
				//The unit can be removed?>
				<div class="imconnector-wrapper-title-in">
					A connection header
					<span class="imconnector-wrapper-title-nav">Information on the steps</span>
				</div>
			<?endif;?>
			<?
			if($arResult['messages'])
			{
				echo '<div class="imconnector-settings-message imconnector-settings-message-success">';
				foreach ($arResult['messages'] as $value)
				{
					echo '<div>' . $value . '</div>';
				}
				echo '</div>';
			}
			if($arResult['error'])
			{
				echo '<div class="imconnector-settings-message imconnector-settings-message-error">';
				foreach ($arResult['error'] as $value)
				{
					echo '<div>' . $value . '</div>';
				}
				echo '</div>';
			}
			?>
		<div class="imconnector-intro">
			<?if(empty($arResult["ACTIVE_STATUS"])):?>
			<div class="imconnector-intro-text">
				<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BASECONNECTOR_INDEX_DESCRIPTION_NEW')?>
			</div>
				<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
					<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active" class="webform-small-button webform-small-button-accept webform-small-button-accept-nomargin" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BASECONNECTOR_CONNECT')?>">
					<?=thurly_sessid_post();?>
				</form>
			<?else:?>
				<?if(!empty($arResult["PAGE"])):?>
					Connection information and the executable code
				<?endif;?>
			<?endif;?>
			</div>
		</div>
	</div>
<?endif;?>