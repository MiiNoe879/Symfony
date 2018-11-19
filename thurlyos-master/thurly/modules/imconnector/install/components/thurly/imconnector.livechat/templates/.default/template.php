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
			<div class="imconnector-livechat-block-title imconnector-livechat-block-title-no-connect">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<?if($arResult["ERROR_STATUS"]):?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_CONNECTOR_ERROR_STATUS')?></td>
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
			<div class="imconnector-livechat-block-title">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<table class="imconnector-connect-table">
					<?if(!empty($arResult["INFO_CONNECTION"])):?>
						<?if(!empty($arResult["INFO_CONNECTION"]['URL_PUBLIC'])):?>
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_LINK')?>:</td>
							<td>
								<?if ($arResult["INFO_CONNECTION"]['URL_CODE_PUBLIC_ID'] > 0):?>
								<a href="<?=$arResult["INFO_CONNECTION"]['URL_PUBLIC']?>"
									class="imconnector-connect-link"
									target="_blank">
									<?=$arResult["INFO_CONNECTION"]['URL_PUBLIC']?>
								</a>
										<span class="imconnector-connect-link-copy"
												for="imconnector-livechat-link-copy"
												onclick="copyImconnector(this)"></span>
								<input type="text"
										class="imconnector-connect-link-input-hidden"
										id="imconnector-livechat-link-copy"
										value="<?=$arResult["INFO_CONNECTION"]['URL_PUBLIC']?>">
								<?else:?>
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_WO_PUBLUC')?>
								<?endif?>
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
		<label class="imconnector-livechat-button" for="imconnector-livechat" onclick="showHideImconnectors(this)">
	<?=$arResult["NAME"]?>
			<span class="imconnector-button-show"
					id="imconnector-livechat-button-show"><?
				if(!empty($arResult["PAGE"])):?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_COLLAPSE')?><?
				else:?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DEPLOY')?><?
				endif;?></span>
		</label>

		<input type="checkbox"
				id="imconnector-livechat"
				class="imconnector-checkbox" hidden<?
		if(!empty($arResult["PAGE"])):?> checked<?endif;
		?>>
	<div class="imconnector-wrapper">
	<?if(empty($arResult["ACTIVE_STATUS"])):?>
		<div class="imconnector-intro">
			<div class="imconnector-intro-text">
				<?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_INDEX_DESCRIPTION_1')?>
			</div>
			<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
				<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
				<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
						class="webform-small-button webform-small-button-accept webform-small-button-accept-nomargin"
						value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_CONNECT_ACTIVE')?>">
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
				<div class="imconnector-step-text imconnector-livechat-inner-container" style="padding-top: 0;">
					<div class="imconnector-livechat-public-link">
						<div class="imconnector-livechat-public-link-header">
							<div class="imconnector-livechat-public-link-header-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_WIDGET')?></div>
						</div>

						<div class="imconnector-livechat-public-link-inner">
							<?if ($arResult['INFO_CONNECTION']['BUTTON_INTERFACE'] == 'Y'):?>

								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_BUTTON_TEXT', Array(
									'#LINK#' => '<a href=" ' . $arResult["PUBLIC_TO_BUTTON_OL"] . '" target="_blank">'.Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_BUTTON_LINK').'</a>'
								));?>

							<?else:?>

								<span class="imconnector-livechat-public-link-settings-inner-param"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_WIDGET')?>:</span>
								<div class="imconnector-livechat-public-link-settings-inner-content">
									<div class="imconnector-public-link-settings-inner-option">
										<div class="imconnector-livechat-public-link-settings-inner-add-textarea-field">
											<textarea id="imconnector-livechat-public-widget-src" id="" cols="30" rows="10" class="imconnector-livechat-public-link-settings-inner-add-textarea-item"><?=htmlspecialcharsbx($arResult['INFO_CONNECTION']['WIDGET_CODE'])?></textarea>
										</div>
									</div><!--imconnector-public-link-settings-inner-option-->
								</div>

							<?endif;?>
						</div>
						<div class="imconnector-livechat-public-link-header">
							<div class="imconnector-livechat-public-link-header-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_PL_HEADER')?></div>
						</div><!--imconnector-livechat-public-link-header-->
						<div class="imconnector-livechat-public-link-inner">
							<div class="imconnector-livechat-public-link-inner-copy">
								<div class="imconnector-livechat-public-link-inner-copy-inner">
									<div class="imconnector-livechat-public-link-inner-copy-field">
										<span><?=htmlspecialcharsbx($arResult['INFO_CONNECTION']['URL_SERVER'])?></span>
										<input id="imconnector-livechat-public-link-url-code" class="imconnector-livechat-public-link-inner-copy-field-item imconnector-livechat-public-link-inner-copy-field-item-livechat" type="text" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_NAME')?>" name="URL_CODE_PUBLIC" value="<?=htmlspecialcharsbx($arResult['INFO_CONNECTION']['URL_CODE_PUBLIC'])?>">
										<span class="tel-context-help" data-text="<?=htmlspecialcharsbx(Loc::getMessage("IMCONNECTOR_COMPONENT_LIVECHAT_SF_LINK_TIP"))?>">?</span>
									</div>
									<div class="imconnector-livechat-public-link-inner-copy-button">
										<span class="webform-small-button imconnector-public-link-inner-copy-button-item" id="imconnector-copy-public-link"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_COPY')?></span>
									</div>
								</div><!--imconnector-livechat-public-link-inner-copy-inner-->
								<div class="imconnector-livechat-public-link-inner-copy-description">
									<div id="imconnector-copy-public-link-content-box" class="imconnector-copy-public-link-content-box" style="height: <?=(empty($arResult['INFO_CONNECTION']['URL_CODE_PUBLIC'])? '0px': '20px')?>;">
										<span class="imconnector-livechat-public-link-inner-copy-description-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_FINAL_LINK')?>:</span>
										<span class="imconnector-livechat-public-link-inner-copy-description-link" id="imconnector-copy-public-link-content" data-pattern="<?=htmlspecialcharsbx($arResult['INFO_CONNECTION']['URL_SERVER'])?>"><?=htmlspecialcharsbx($arResult['INFO_CONNECTION']['URL_PUBLIC'])?></span>
									</div>
									<div class="imconnector-livechat-public-link-inner-copy-description-item">(<?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_LINK_NOTICE')?>)</div>
								</div>
								<div class="imconnector-livechat-public-link-inner-copy-description">
								</div>
								<script type="text/javascript">
									BX.clipboard.bindCopyClick(BX('imconnector-copy-public-link'), {text: BX('imconnector-copy-public-link-content'), offsetLeft: 130});
									BX.OpenLinesConfigEdit.addEventForTooltip();
									BX.lastCheck = {};
									BX.bind(BX('imconnector-livechat-public-link-url-code'), 'bxchange', function(){
										clearTimeout(BX.tempTimeout1);
										BX.tempTimeout1 = setTimeout(function(){
											imconnectorCheckUrlCode(BX('imconnector-livechat-public-link-url-code').value, 'formatName');
											BX.lastCheck['checkName'] = '';
										}, 400);

										clearTimeout(BX.tempTimeout2);
										BX.tempTimeout2 = setTimeout(function(){
											imconnectorCheckUrlCode(BX('imconnector-livechat-public-link-url-code').value, 'checkName');
										}, 2000);

									});
									function imconnectorCheckUrlCode(text, command)
									{
										if (BX.lastCheck[command] == text)
											return true;

										BX.lastCheck[command] = text;

										BX.ajax({
											url: '<?=$this->getComponent()->getPath().'/ajax.php'?>',
											method: 'POST',
											data: {
												'ACTION': command,
												'CONFIG_ID': <?=intval($arResult['INFO_CONNECTION']['CONFIG_ID'])?>,
												'ALIAS': text,
												'sessid': BX.thurly_sessid()
											},
											timeout: 30,
											dataType: 'json',
											processData: true,
											onsuccess: function(data){
												data = data || {};
												if (command == 'formatName')
												{
													BX('imconnector-copy-public-link-content').href = BX('imconnector-copy-public-link-content').getAttribute('data-pattern')+data.ALIAS;
													BX('imconnector-copy-public-link-content').innerHTML = BX('imconnector-copy-public-link-content').href;
													BX.removeClass(BX('imconnector-livechat-public-link-url-code'), 'imconnector-livechat-public-link-inner-copy-field-item-livechat-error');
													BX('imconnector-copy-public-link-content-box').style = data.ALIAS? 'height: 20px': 'height: 0px';
												}
												else if (command == 'checkName')
												{
													if (data.AVAILABLE == 'N')
													{
														BX.addClass(BX('imconnector-livechat-public-link-url-code'), 'imconnector-livechat-public-link-inner-copy-field-item-livechat-error');
													}
												}
											}
										});
									}
								</script>
							</div><!--imconnector-livechat-public-link-inner-copy-->
							<div class="imconnector-livechat-border"></div><!--imconnector-livechat-border-->
						</div><!--imconnector-livechat-public-link-inner-->
					</div><!--imconnector-livechat-public-link-->
					<?/**/?>
					<div id="imconnector-livechat-public-link-settings-toggle" class="imconnector-livechat-public-link-settings" style="margin-top: 2px;">
						<span class="imconnector-livechat-public-link-settings-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_CONFIG')?></span>
						<span class="imconnector-livechat-public-link-settings-triangle-down"></span>
					</div><!--imconnector-livechat-public-link-settings-->
					<script type="text/javascript">
						BX.bind(BX('imconnector-livechat-public-link-settings-toggle'), 'click', function(e){
							BX.toggleClass(BX('imconnector-livechat-open'), 'imconnector-livechat-public-open');
							if(BX('imconnector-livechat-open-block').value == '')
								BX('imconnector-livechat-open-block').value='Y';
							else
								BX('imconnector-livechat-open-block').value='';
						});
					</script>
					<input type="hidden" name="open_block" id="imconnector-livechat-open-block" value="<?=$arResult['OPEN_BLOCK']?>">
					<div id="imconnector-livechat-open" class="imconnector-livechat-public-link-settings-inner<?=empty($arResult['OPEN_BLOCK'])?'':' imconnector-livechat-public-open';?>">
						<div class="imconnector-livechat-public-link-settings-inner-container">
							<span class="imconnector-livechat-public-link-settings-inner-param"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_TYPE')?>:</span>
							<div class="imconnector-livechat-public-link-settings-inner-content">
								<div class="imconnector-livechat-public-link-settings-inner-type">
								<?/*
								<span class="imconnector-livechat-public-link-settings-inner-chat">
									<label for="colorless" class="imconnector-livechat-public-link-settings-inner-chat-container">
										<div class="imconnector-livechat-public-link-settings-inner-chat-image imconnector-livechat-colorless"></div>
										<div class="imconnector-livechat-public-link-settings-inner-field-container">
											<input id="colorless" class="imconnector-public-link-settings-inner-chat-field" type="radio" value="colorless" name="TEMPLATE_ID" <?=($arResult['INFO_CONNECTION']['TEMPLATE_ID'] == "colorless"? "checked": "")?>>
											<span class="imconnector-public-link-settings-inner-chat-text"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_TYPE_1')?></span>
										</div>
									</label><!--imconnector-livechat-public-link-settings-inner-chat-container-->
								</span><!--imconnector-livechat-public-link-settings-inner-chat-->
									<span class="imconnector-livechat-public-link-settings-inner-chat">
									<label for="color" class="imconnector-livechat-public-link-settings-inner-chat-container">
										<div class="imconnector-livechat-public-link-settings-inner-chat-image imconnector-livechat-color"></div>
										<div class="imconnector-livechat-public-link-settings-inner-field-container">
											<input id="color" class="imconnector-public-link-settings-inner-chat-field" type="radio" value="color" name="TEMPLATE_ID" <?=($arResult['INFO_CONNECTION']['TEMPLATE_ID'] == "color"? "checked": "")?>>
											<span class="imconnector-public-link-settings-inner-chat-text"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_TYPE_2')?></span>
										</div>
									</label><!--imconnector-livechat-public-link-settings-inner-chat-container-->
								</span><!--imconnector-livechat-public-link-settings-inner-chat-->
								*/?>
									<div class="imconnector-livechat-public-link-settings-inner-upload">
										<div class="imconnector-public-public-link-settings-inner-upload-description">
										<span class="imconnector-public-link-settings-inner-upload-description-item">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_IMAGE_LOAD')?>
										</span>
										</div>
										<div class="imconnector-livechat-public-link-settings-inner-upload-field">
											<button class="imconnector-livechat-public-link-settings-inner-upload-button"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_IMAGE_LOAD_BUTTON')?></button>
											<input class="imconnector-livechat-public-link-settings-inner-upload-item" type="file" name="BACKGROUND_IMAGE">
										</div>
										<span id="BACKGROUND_IMAGE_TEXT" class="imconnector-livechat-public-link-settings-inner-upload-info"></span>
										<script type="text/javascript">
											var backgroundImages = document.getElementsByName('BACKGROUND_IMAGE');
											BX.bind(backgroundImages[0], 'bxchange', function(){
												var parts = [];
												parts = this.value.replace(/\\/g, '/').split( '/' );
												BX('BACKGROUND_IMAGE_TEXT').innerText = parts[parts.length-1];
											});
										</script>
										<?if($arResult['INFO_CONNECTION']['BACKGROUND_IMAGE'] > 0):?>
										<label class="imconnector-livechat-public-link-upload-checkbox-container" for="BACKGROUND_IMAGE_del">
											<input type="checkbox" class="imconnector-livechat-public-link-upload-checkbox" value="Y" name="BACKGROUND_IMAGE_del" id="BACKGROUND_IMAGE_del">
											<span class="imconnector-livechat-public-link-upload-checkbox-element"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_IMAGE_DELETE')?></span>
										</label>
										<div class="imconnector-livechat-public-link-upload-image-container">
											<img class="imconnector-livechat-public-link-upload-image" alt="" src="<?=$arResult['INFO_CONNECTION']['BACKGROUND_IMAGE_LINK']?>">
										</div>
										<?endif;?>
									</div><!--imconnector-livechat-public-link-settings-inner-upload-->
								</div><!--imconnector-livechat-public-link-settings-inner-type-->
							</div><!--imconnector-livechat-public-link-settings-inner-content-->
						</div><!--imconnector-livechat-public-link-settings-inner-container-->
						<div class="imconnector-border"></div><!--imconnector-border-->
						<div class="imconnector-livechat-public-link-settings-inner-settings-container">
							<span class="imconnector-livechat-public-link-settings-inner-param"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_CSS')?>:</span>
							<div class="imconnector-livechat-public-link-settings-inner-content">
								<div class="imconnector-public-link-settings-inner-option" onchange="BX.toggleClass(BX('imconnector-add-open'), 'imconnector-livechat-public-add-open');">
									<label for="css" class="imconnector-livechat-public-link-settings-inner-option-container">
										<input id="css" class="imconnector-public-link-settings-inner-option-field" type="checkbox" name="CSS_ACTIVE" <?=($arResult['INFO_CONNECTION']['CSS_ACTIVE'] == "Y"? "checked": "")?>>
										<span class="imconnector-public-link-settings-inner-option-text"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_CSS_2')?></span>
									</label><!--imconnector-public-link-settings-inner-option-->
								</div><!--imconnector-public-link-settings-inner-option-->

								<div id="imconnector-add-open" class="imconnector-livechat-public-link-settings-inner-add-wrapper <?=($arResult['INFO_CONNECTION']['CSS_ACTIVE'] == "Y"? "imconnector-livechat-public-add-open": "")?>">
									<div class="imconnector-livechat-public-link-settings-inner-add-container">
										<div class="imconnector-livechat-public-link-settings-inner-add-checkbox-container">
											<div class="imconnector-livechat-public-link-settings-inner-add-item-container">
												<span class="imconnector-livechat-public-link-settings-inner-add-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_CSS_PATH')?>:</span>
											</div>
										</div>
										<div class="imconnector-livechat-public-link-settings-inner-add-input-container">
											<input type="text" placeholder="http://" class="imconnector-livechat-public-link-settings-inner-add-input" name="CSS_PATH" value="<?=htmlspecialcharsbx($arResult['INFO_CONNECTION']['CSS_PATH'])?>">
										</div>
									</div><!--imconnector-livechat-public-link-settings-inner-add-container-->
									<div class="imconnector-livechat-public-link-settings-inner-add-textarea">
										<div class="imconnector-livechat-public-link-settings-inner-add-textarea-header">
											<span class="imconnector-livechat-public-link-settings-inner-add-textarea-header-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_CSS_TEXT')?>:</span>
										</div>
										<div class="imconnector-livechat-public-link-settings-inner-add-textarea-field">
											<textarea name="CSS_TEXT" id="" cols="30" rows="10" class="imconnector-livechat-public-link-settings-inner-add-textarea-item"><?=htmlspecialcharsbx($arResult['INFO_CONNECTION']['CSS_TEXT'])?></textarea>
										</div>
									</div><!--imconnector-livechat-public-link-settings-inner-add-textarea-->
								</div><!--imconnector-livechat-public-link-settings-inner-add-wrapper-->

							</div><!--imconnector-livechat-public-link-settings-inner-content-->
						</div><!--imconnector-livechat-public-link-settings-inner-container-->
						<div class="imconnector-border"></div><!--imconnector-border-->
						<div class="imconnector-livechat-public-link-settings-inner-settings-container">
							<span class="imconnector-livechat-public-link-settings-inner-param"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_SIGN')?>:</span>
							<div class="imconnector-livechat-public-link-settings-inner-content">
								<div class="imconnector-public-link-settings-inner-option">
									<label for="logo" class="imconnector-livechat-public-link-settings-inner-option-container">
										<input id="logo" name="COPYRIGHT_REMOVED" class="imconnector-public-link-settings-inner-option-field" type="checkbox" <?=($arResult['INFO_CONNECTION']['COPYRIGHT_REMOVED'] == "Y"? "checked": "")?>>
										<span class="imconnector-public-link-settings-inner-option-text <?=($arResult['INFO_CONNECTION']['CAN_REMOVE_COPYRIGHT'] == "Y"? "": "imconnector-lock-icon")?>">
											<span class="imconnector-livechat-public-link-settings-normal"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_SIGN_2')?></span>
											<span class="imconnector-livechat-public-link-settings-bold"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_SIGN_3')?></span>
										</span>
									</label><!--imconnector-livechat-public-link-settings-inner-option-container-->
									<?if($arResult['INFO_CONNECTION']['CAN_REMOVE_COPYRIGHT'] == "N"):?>
									<script type="text/javascript">
										BX.bind(BX('logo'), 'change', function(e){
											this.checked = false;

											if(!B24 || !B24['licenseInfoPopup'])
											{
												return;
											}

											B24.licenseInfoPopup.show(
												'imopenlines_livechat_copyright',
												'<?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_SIGN_HINT_1')?>',
												'<span><?=Loc::getMessage('IMCONNECTOR_COMPONENT_LIVECHAT_SF_PAGE_SIGN_HINT_2')?></span>'
											);
										});
									</script>
									<?endif?>
								</div><!--imconnector-public-link-settings-inner-option-->
							</div>
						</div><!--imconnector-livechat-public-link-settings-inner-container-->
					</div><!--imconnector-livechat-public-link-settings-inner-->
				</div>
					<div class="imconnector-border imconnctor-outer-border" style="display: none"></div><!--imconnector-border-->
					<table class="imconnector-step-table">
					<tr>
						<td>
							<input type="submit"
									class="webform-small-button webform-small-button-accept"
									id="webform-small-button-have-bot"
									name="<?=$arResult["CONNECTOR"]?>_save"
									value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SAVE')?>">
						</td>
					</tr>

				</table>
					<?=thurly_sessid_post();?>
				</form>
			</div>
		<?endif;?>
	<?endif;?>
		</div>
	</div>
<?endif;?>