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

CJSCore::Init(array('clipboard'));
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
			<div class="imconnector-network-block-title imconnector-network-block-title-no-connect">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<?if($arResult["ERROR_STATUS"]):?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_CONNECTOR_ERROR_STATUS')?></td>
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
			<div class="imconnector-network-block-title">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<table class="imconnector-connect-table">
					<?if(!empty($arResult["FORM"])):?>
						<?if(!empty($arResult["FORM"]["NAME"])):?>
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_NAME')?></td>
							<td>
								<?=$arResult["FORM"]["NAME"]?>
							</td>
						</tr>
						<?endif;?>
						<?if(!empty($arResult["FORM"]["CODE"])):?>
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_CODE')?></td>
							<td>
								<span class="imconnector-connect-link"
									  for="imconnector-network-link-copy"
									  onclick="copyImconnector(this)">
									<?=$arResult["FORM"]["CODE"]?>
								</span>
										<span class="imconnector-connect-link-copy"
											  for="imconnector-network-link-copy"
											  onclick="copyImconnector(this)"></span>
								<input type="text"
									   class="imconnector-connect-link-input-hidden"
									   id="imconnector-network-link-copy"
									   value="<?=$arResult["FORM"]["CODE"]?>">
							</td>
						</tr>
						<?endif;?>
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
		<label class="imconnector-network-button" for="imconnector-network" onclick="showHideImconnectors(this)">
	<?=$arResult["NAME"]?>
			<span class="imconnector-button-show"
				  id="imconnector-network-button-show"><?
				if(!empty($arResult["PAGE"])):?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_COLLAPSE')?><?
				else:?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DEPLOY')?><?
				endif;?></span>
		</label>

		<input type="checkbox"
			   id="imconnector-network"
			   class="imconnector-checkbox" hidden<?
		if(!empty($arResult["PAGE"])):?> checked<?endif;
		?>>
	<div class="imconnector-wrapper">
	<?if(empty($arResult["ACTIVE_STATUS"])):?>
		<div class="imconnector-intro">
			<div class="imconnector-intro-text">
				<?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_INDEX_DESCRIPTION')?>
			</div>
			<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
				<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
				<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active" class="webform-small-button webform-small-button-accept webform-small-button-accept-nomargin" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_CONNECT')?>">
				<?=thurly_sessid_post();?>
			</form>
		</div>
	<?else:?>
		<?if($arResult["PAGE"] == 'simple_form'):?>
			<div class="imconnector-wrapper-step">
				<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post" enctype="multipart/form-data">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
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

					<div class="imconnector-intro" style="margin-bottom: 16px;">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_SIMPLE_FORM_DESCRIPTION_1')?>
					</div>
					<div class="imconnector-public-link-inner-copy-inner">
						<div class="imconnector-public-link-inner-copy-field">
							<span class="imconnector-public-link-title"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_1')?>:</span>
							<input class="imconnector-step-input" type="text" name="name" value="<?=$arResult["FORM"]["NAME"]?>">
						</div>
					</div>
					<div class="imconnector-public-link-inner-copy-inner">
						<div class="imconnector-public-link-inner-copy-field">
							<span class="imconnector-public-link-title"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_2')?>:</span>
							<input class="imconnector-step-input" type="text" name="description" value="<?=$arResult["FORM"]["DESCRIPTION"]?>">
						</div>
					</div>
					<div class="imconnector-public-link-inner-copy-inner">
						<div class="imconnector-public-link-inner-copy-field">
							<span class="imconnector-public-link-title"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_3')?>:</span>
							<textarea class="imconnector-step-textarea" name="welcome_message"><?=$arResult["FORM"]["WELCOME_MESSAGE"]?></textarea>
						</div>
					</div>

					<div class="imconnector-public-link-settings-inner-container">
						<span class="imconnector-public-link-settings-inner-param"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_4')?>:</span>
						<div class="imconnector-lpublic-link-settings-inner-content">
							<div class="imconnector-public-link-settings-inner-type">
								<div class="imconnector-public-link-settings-inner-upload">
									<div class="imconnector-public-public-link-settings-inner-upload-description">
										<span class="imconnector-public-link-settings-inner-upload-description-item" style="font-weight: normal">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_4_DESCRIPTION_1')?>
										</span>
									</div>
									<div class="imconnector-public-link-settings-inner-upload-field imconnector-public-link-settings-inner-upload-description">
										<button class="imconnector-public-link-settings-inner-upload-button"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_4_DESCRIPTION_2')?></button>
										<input type="file" name="avatar" class="imconnector-public-link-settings-inner-upload-item">
									</div>
									<span id="avatar_text" class="imconnector-public-link-settings-inner-upload-info"></span>
									<script type="text/javascript">
										var avatarImages = document.getElementsByName('avatar');
										BX.bind(avatarImages[0], 'bxchange', function(){
											var parts = [];
											parts = this.value.replace(/\\/g, '/').split( '/' );
											BX('avatar_text').innerText = parts[parts.length-1];
										});
									</script>
									<?if(!empty($arResult["FORM"]["AVATAR"])):?>
										<div class="imconnector-img-del">
											<label class="imconnector-public-link-upload-checkbox-container" for="id-2">
												<input clas="imconnector-public-link-settings-inner-upload-description-item" value="Y" name="avatar_del" type="checkbox" id="id-2">
												<span class="imconnector-public-link-settings-inner-option-text"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_4_DESCRIPTION_3')?></span>
											</label>
											<div class="imconnector-public-link-upload-image-container">
												<img class="imconnector-public-link-upload-image" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_4')?>" src="<?=$arResult['FORM']['AVATAR_LINK']?>">
											</div>
										</div>
									<?endif;?>
								</div>
							</div>
						</div>
					</div>

					<div class="imconnector-public-link-inner-copy-inner">
						<div class="imconnector-public-link-inner-copy-field">
							<span class="imconnector-public-link-title"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_5')?>:</span>
							<input class="imconnector-public-link-settings-inner-option-field" style="margin-top: 16px;" type="checkbox" name="searchable" value="Y"<?=($arResult["FORM"]["SEARCHABLE"]? 'checked': '')?>>
						</div>
					</div>

					<div class="imconnector-step-text">
						<?if(!empty($arResult["FORM"]["CODE"])):?>
						<table class="imconnector-step-table">
							<tr>
								<td colspan="2">
									<div class="imconnector-public-link-settings-inner-upload-description"><span class="imconnector-public-link-settings-inner-upload-description-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_6')?></span> <span data-text="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_FIELD_6_TIP')?>" class="tel-context-help">?</span></div></td>
							</tr>
							<tr>
								<td><input class="imconnector-step-input" id="network-link" type="text" value="<?=$arResult["FORM"]["CODE"]?>" readonly></td>
								<td><span class="webform-small-button imconnector-public-link-inner-copy-button-item" id="imconnector-network-link"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_COPY')?></span></td>

							</tr>
						</table>
						<script type="text/javascript">
							BX.clipboard.bindCopyClick(BX('imconnector-network-link'), {text: BX('network-link'), offsetLeft: 130});
							BX.OpenLinesConfigEdit.addEventForTooltip();
						</script>
						<?endif;?>
						<div class="imconnector-step-text imconnector-step-text-14">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_REST_HELP', Array(
								'#LINK_START#' => '<a href="'.Loc::getMessage('IMCONNECTOR_COMPONENT_NETWORK_REST_LINK').'" target="_blank">',
								'#LINK_END#' => '</a>'
							))?>
						</div>
					</div>
					<input type="submit"
						   class="webform-small-button webform-small-button-accept"
						   name="<?=$arResult["CONNECTOR"]?>_save"
						   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SAVE')?>">
					<?=thurly_sessid_post();?>
				</form>
			</div>
		<?endif;?>
	<?endif;?>
		</div>
	</div>
<?endif;?>