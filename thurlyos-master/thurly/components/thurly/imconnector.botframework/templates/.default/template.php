<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Thurly\Main\Localization\Loc;
use \Thurly\ImConnector\Library;
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
Library::loadMessagesConnectorClass();

$placeholder = ' placeholder="' . Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_PLACEHOLDER') . '"';
$this->addExternalCss('/thurly/js/imconnector/icon.css');
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
			<div class="imconnector-ms-block-title imconnector-ms-block-title-no-connect" title="<?=$arResult["NAME_TITLE"]?>">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<?if($arResult["ERROR_STATUS"]):?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_CONNECTOR_ERROR_STATUS')?></td>
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
			<div class="imconnector-ms-block-title"  title="<?=$arResult["NAME_TITLE"]?>">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<table class="imconnector-connect-table">
					<?if(!empty($arResult["INFO_CONNECTION"])):?>
						<?if(!empty($arResult["INFO_CONNECTION"]['URL'])):?>
							<tr>
								<td><a href="<?=$arResult["INFO_CONNECTION"]['URL']?>"
									   class="imconnector-connect-link"
									   target="_blank">
										<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_GET_LINKS')?>
									</a>
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
			<?if(empty($arResult["PAGE"]) || $arResult["PAGE"] != 'index'):?>
			<a href="<?=$arResult["URL"]["INDEX"]?>" class="imconnector-back"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_GO_BACK_TO_BEGINNING')?></a>
			<?endif;?>
		<?endif;?>
		<label class="imconnector-ms-button" for="botframework" onclick="showHideImconnectors(this)" title="<?=$arResult["NAME_TITLE"]?>">
			<?=$arResult["NAME"]?>
			<span class="imconnector-button-show"
				  id="imconnector-ms-button-show"><?
				if(!empty($arResult["PAGE"])):?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_COLLAPSE')?><?
				else:?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DEPLOY')?><?
				endif;?></span>
		</label>

		<input type="checkbox"
			   id="botframework"
			   class="imconnector-checkbox" hidden<?
		if(!empty($arResult["PAGE"])):?> checked<?endif;
		?>>
		<div class="imconnector-wrapper">
<?if(empty($arResult["ACTIVE_STATUS"]) || $arResult["PAGE"] == 'index'):?>
		<div class="imconnector-wrapper-step">
			<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_INDEX_DESCRIPTION')?></div>
			<div class="imconnector-create">
				<form action="<?=$arResult["URL"]["MASTER"]?>" method="post" class="webform-small-button webform-small-button-accept">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
					<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
						   class="webform-small-button webform-small-button-text"
						   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_CREATE_NEW_BOT')?>">
					<?=thurly_sessid_post();?>
				</form>
				<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post" class="webform-small-button">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
					<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
						   class="webform-small-button-text"
						   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_FORM_SETTINGS')?>">
					<?=thurly_sessid_post();?>
				</form>
			</div>
		</div>
<?else:?>
	<?if($arResult["PAGE"] == 'master'):?>
		<div class="imconnector-wrapper-step">
			<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_10_STEPS_TITLE')?></div>

			<div class="imconnector-step">
				<form action="<?=$arResult["URL"]["MASTER"]?>" method="post">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">

				<div class="imconnector-step-item<?if(empty($arResult['error']) && empty($arResult["STATUS"])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-1">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_1_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_1_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-1"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-1">

						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_1_OF_10_DESCRIPTION_1')?><a href="https://dev.botframework.com/"
																												   class="imconnector-link-a"
																												   target="_blank">https://dev.botframework.com/</a><br>
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_1_OF_10_DESCRIPTION_2')?>
						</div>

						<div class="imconnector-step-next">
							<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
								   for="imconnector-ms-step-item-2"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
						</div>
					</div>
				</div>

				<div class="imconnector-step-item" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-2">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_2_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_2_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-2"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-2">

						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_2_OF_10_DESCRIPTION_1')?>
						</div>
						<img class="imconnector-step-img"
							 src="<?=$templateFolder?>/images/imconnector-step-2.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_2_OF_10_TITLE')?>">
						<div class="imconnector-step-text">
							<label for="bot_handle"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_2_OF_10_DESCRIPTION_2')?></label>
						</div>
						<input type="text" name="bot_handle" id="bot_handle" size="50" value="<?=$arResult["FORM"]["bot_handle"]?>"<?=$arResult["placeholder"]["bot_handle"]?$placeholder:'';?> class="imconnector-step-input">

						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_2_OF_10_DESCRIPTION_3')?>
						</div>
						<table class="imconnector-step-table">
							<tr>
								<td>
									<input type="text"
										   class="imconnector-step-input"
										   id="imconnector-ms-step-input-id"
										   value = "<?=$arResult["URL_WEBHOOK"]?>"
										   readonly>
								</td>
								<td>
									<span class="webform-small-button webform-small-button-accept"
										  for="imconnector-ms-step-input-id"
										  onclick="copyImconnector(this)"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_COPY')?></span>
								</td>
							</tr>
						</table>

						<div class="imconnector-step-next">
							<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
								   for="imconnector-ms-step-item-3"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
						</div>
					</div>
				</div>

				<div class="imconnector-step-item" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-3">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_3_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_3_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-3"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-3">
						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_3_OF_10_DESCRIPTION_1')?>
						</div>
						<img class="imconnector-step-img"
							 src="<?=$templateFolder?>/images/imconnector-step-3-1.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_3_OF_10_TITLE')?>">

						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_3_OF_10_DESCRIPTION_2')?>
						</div>

						<div class="imconnector-step-text"><label for="app_id"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_APP_ID_NAME')?></label></div>
						<input type="text" name="app_id" id="app_id" size="50" value="<?=$arResult["FORM"]["app_id"]?>"<?=$arResult["placeholder"]["app_id"]?$placeholder:'';?> class="imconnector-step-input">

						<div class="imconnector-step-next">
							<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
								   for="imconnector-ms-step-item-4"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
						</div>
					</div>
				</div>

					<div class="imconnector-step-item" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-ms-step-item-4">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10')?></span>
						</label>
						<input type="radio"
							   id="imconnector-ms-step-item-4"
							   class="imconnector-toggle"
							   name="imconnector-ms-accordeon" hidden>
						<div class="imconnector-step-wrapper" id="imconnector-ms-step-4">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10_DESCRIPTION_1')?>
							</div>
							<img class="imconnector-step-img"
								 src="<?=$templateFolder?>/images/imconnector-step-4-1.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10_TITLE')?>">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10_DESCRIPTION_2')?>
							</div>
							<img class="imconnector-step-img"
								 src="<?=$templateFolder?>/images/imconnector-step-4-2.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10_TITLE')?>">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10_DESCRIPTION_3')?>
							</div>
							<div class="imconnector-step-text"><label for="app_secret"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_APP_SECRET_NAME')?></label></div>
							<input type="text" name="app_secret" id="app_secret" size="50" value="<?=$arResult["FORM"]["app_secret"]?>"<?=$arResult["placeholder"]["app_secret"]?$placeholder:'';?> class="imconnector-step-input">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10_DESCRIPTION_4')?>
							</div>
							<img class="imconnector-step-img"
								 src="<?=$templateFolder?>/images/imconnector-step-4-3.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_4_OF_10_TITLE')?>">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-ms-step-item-5"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

				<div class="imconnector-step-item" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-5">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_5_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_5_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-5"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-5">

						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_5_OF_10_DESCRIPTION_1')?>
						</div>
						<img class="imconnector-step-img"
							 src="<?=$templateFolder?>/images/imconnector-step-5.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_5_OF_10_TITLE')?>">

						<div class="imconnector-step-next">
							<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
								   for="imconnector-ms-step-item-6"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
						</div>
					</div>
				</div>
				<div class="imconnector-step-item" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-6">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_6_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_6_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-6"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-6">

						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_6_OF_10_DESCRIPTION_1')?>
						</div>
						<img class="imconnector-step-img"
							 src="<?=$templateFolder?>/images/imconnector-step-6new.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_6_OF_10_TITLE')?>">

						<div class="imconnector-step-next">
							<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
								   for="imconnector-ms-step-item-7"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
						</div>
					</div>
				</div>
				<div class="imconnector-step-item" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-7">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_7_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_7_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-7"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-7">
						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_7_OF_10_DESCRIPTION_1')?>
						</div>
						<img class="imconnector-step-img"
							 src="<?=$templateFolder?>/images/imconnector-step-7new.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_7_OF_10_TITLE')?>">

						<div class="imconnector-step-next">
							<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
								   for="imconnector-ms-step-item-8"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
						</div>
					</div>
				</div>
				<div class="imconnector-step-item" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-8">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_8_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_8_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-8"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-8">

						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_8_OF_10_DESCRIPTION_1')?>
						</div>
						<img class="imconnector-step-img"
							 src="<?=$templateFolder?>/images/imconnector-step-8.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_8_OF_10_TITLE')?>">

						<div class="imconnector-step-next">
							<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
								   for="imconnector-ms-step-item-9"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
						</div>
					</div>
				</div>
				<div class="imconnector-step-item<?if(!empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-9">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_9_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_9_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-9"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-9">
						<?
						if(!empty($arResult['error']))
						{
							echo '<div class="imconnector-settings-message imconnector-settings-message-error">';
							foreach ($arResult['error'] as $value)
							{
								echo '<div>' . $value . '</div>';
							}
							echo '</div>';
						}
						?>
						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_9_OF_10_DESCRIPTION_1')?>
						</div>
						<img class="imconnector-step-img"
							 src="<?=$templateFolder?>/images/imconnector-step-9-1.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_9_OF_10_TITLE')?>">
						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_9_OF_10_DESCRIPTION_2')?>
						</div>
						<img class="imconnector-step-img"
							 src="<?=$templateFolder?>/images/imconnector-step-9-2.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_9_OF_10_TITLE')?>">
						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_9_OF_10_DESCRIPTION_3')?>
						</div>

						<div class="imconnector-step-next">
							<input type="submit" name="<?=$arResult["CONNECTOR"]?>_save" class="webform-small-button webform-small-button-accept" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SAVE')?>">
						</div>
					</div>
				</div>
			<?if(!empty($arResult["STATUS"])):?>
				<div class="imconnector-step-item<?if(empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-ms-step-item-10">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_10_OF_10_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_STEP_10_OF_10')?></span>
					</label>
					<input type="radio"
						   id="imconnector-ms-step-item-10"
						   class="imconnector-toggle"
						   name="imconnector-ms-accordeon" hidden>
					<div class="imconnector-step-wrapper" id="imconnector-ms-step-10">

					<?include 'final.php';?>

						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_LINKS_CHANNELS_COMMUNICATION_DESCRIPTION_MASTER', Array(
								'#LINK_BEGIN#' => '<a href="' . $arResult["URL"]["SIMPLE_FORM"] . '&open_block=Y#open_block">',
								'#LINK_END#' => '</a>',
							));?>
						</div>
					</div>
				</div>
			<?endif;?>
					<?=thurly_sessid_post();?>
				</form>
			</div>
		</div>
	<?elseif($arResult["PAGE"] == 'simple_form'):?>
		<div class="imconnector-wrapper-step">
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
			<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_SIMPLE_FORM_DESCRIPTION_1')?></div>

			<?if(!empty($arResult["URL_WEBHOOK"])):?>
			<table class="imconnector-step-table">
				<tr>
					<td>
						<input type="text"
							   class="imconnector-step-input"
							   id="imconnector-ms-step-input-adress"
							   onfocus="this.select()"
							   value="<?=$arResult["URL_WEBHOOK"]?>"
							   readonly>
					</td>
					<td>
						<span class="webform-small-button webform-small-button-accept"
							  for="imconnector-ms-step-input-adress"
							  onclick="copyImconnector(this)"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_COPY')?></span>
					</td>
				</tr>
			</table>
			<?endif;?>

			<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
				<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
			<div class="imconnector-step-text"><label for="bot_handle"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_BOT_HANDLE_NAME')?></label></div>
			<input type="text" name="bot_handle" id="bot_handle" size="50" value="<?=$arResult["FORM"]["bot_handle"]?>"<?=$arResult["placeholder"]["bot_handle"]?$placeholder:'';?> class="imconnector-step-input">

			<div class="imconnector-step-text"><label for="app_id"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_APP_ID_NAME')?></label></div>
			<input type="text" name="app_id" id="app_id" size="50" value="<?=$arResult["FORM"]["app_id"]?>"<?=$arResult["placeholder"]["app_id"]?$placeholder:'';?> class="imconnector-step-input">

			<div class="imconnector-step-text"><label for="app_secret"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_APP_SECRET_NAME')?></label></div>
			<input type="text" name="app_secret" id="app_secret" size="50" value="<?=$arResult["FORM"]["app_secret"]?>"<?=$arResult["placeholder"]["app_secret"]?$placeholder:'';?> class="imconnector-step-input">
				<a name="open_block"></a>
				<div class="imconnector-step-text">
					<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_LINKS_CHANNELS_COMMUNICATION_DESCRIPTION', Array(
						'#LINK_BEGIN#' => '<a href="' . (empty($arResult['INFO_CONNECTION']['URL']) ? 'https://dev.botframework.com/bots' : $arResult['INFO_CONNECTION']['URL']) . '" target="_blank">',
						'#LINK_END#' => '</a>',
					));?>
				</div>
				<div id="imconnector-botframework-public-link-settings-toggle" class="imconnector-botframework-public-link-settingss">
					<span class="imconnector-botframework-public-link-settings-item"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_LINKS_CHANNELS_COMMUNICATION_TITLE')?></span>
					<span class="imconnector-botframework-public-link-settings-triangle-down"></span>
				</div><!--imconnector-botframework-public-link-settingss-->
				<script type="text/javascript">
					BX.bind(BX('imconnector-botframework-public-link-settings-toggle'), 'click', function(e){
						BX.toggleClass(BX('imconnector-botframework-open'), 'imconnector-botframework-public-open');
						if(BX('imconnector-botframework-open-block').value == '')
							BX('imconnector-botframework-open-block').value='Y';
						else
							BX('imconnector-botframework-open-block').value='';
					});
				</script>
				<input type="hidden" name="open_block" id="imconnector-botframework-open-block" value="<?=$arResult['OPEN_BLOCK']?>">
				<div id="imconnector-botframework-open" class="imconnector-botframework-public-link-settings-inner<?=empty($arResult['OPEN_BLOCK'])?'':' imconnector-botframework-public-open';?>">
					<div class="imconnector-step-text">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_CHANNELS_DESCRIPTION', Array(
							'#LINK_BEGIN#' => '<a href="' . (empty($arResult['INFO_CONNECTION']['URL']) ? 'https://dev.botframework.com/bots' : $arResult['INFO_CONNECTION']['URL']) . '" target="_blank">',
							'#LINK_END#' => '</a>',
						))?>
					</div>
					<table class="imconnector-step-table">
						<tr>
							<td>
								<label for="url_skypebot"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-skype" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_SKYPE')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_skypebot" id="url_skypebot" value="<?=$arResult["FORM"]["url_skypebot"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_SKYPEBOT_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_SKYPEBOT_EXAMPLE')?>
								</div>
							</td>
						</tr>

						<tr>
							<td>
								<label for="url_slack"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-slack" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_SLACK')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_slack" id="url_slack" value="<?=$arResult["FORM"]["url_slack"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_SLACK_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_SLACK_EXAMPLE')?>
								</div>
							</td>
						</tr>

						<tr>
							<td>
								<label for="url_kik"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-kik" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_KIK')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_kik" id="url_kik" value="<?=$arResult["FORM"]["url_kik"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_KIK_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_KIK_EXAMPLE')?>
								</div>
							</td>
						</tr>

						<tr>
							<td>
								<label for="url_groupme"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-groupme" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_GROUPME')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_groupme" id="url_groupme" value="<?=$arResult["FORM"]["url_groupme"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_GROUPME_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_GROUPME_EXAMPLE')?>
								</div>
							</td>
						</tr>

						<tr>
							<td>
								<label for="url_twilio"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-twilio" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_TWILIO')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_twilio" id="url_twilio" value="<?=$arResult["FORM"]["url_twilio"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_TWILIO_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_TWILIO_EXAMPLE')?>
								</div>
							</td>
						</tr>
<?/*?>
						<tr>
							<td>
								<label for="url_webchat"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-webchat" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_WEBCHAT')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_webchat" id="url_webchat" value="<?=$arResult["FORM"]["url_webchat"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_WEBCHAT_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_WEBCHAT_EXAMPLE')?>
								</div>
							</td>
						</tr>
<?*/?>
						<tr>
							<td>
								<label for="url_email"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-emailoffice365" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_EMAILOFFICE365')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_email" id="url_email" value="<?=$arResult["FORM"]["url_email"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_EMAILOFFICE365_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_EMAILOFFICE365_EXAMPLE')?>
								</div>
							</td>
						</tr>

						<tr>
							<td>
								<label for="url_telegram"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-telegram" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_TELEGRAM')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_telegram" id="url_telegram" value="<?=$arResult["FORM"]["url_telegram"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_TELEGRAM_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_TELEGRAM_EXAMPLE')?>
								</div>
							</td>
						</tr>

						<tr>
							<td>
								<label for="url_facebook"><div class="connector-icon connector-icon-40 connector-icon-square connector-icon-botframework-facebookmessenger" title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_FACEBOOKMESSENGER')?>"></div></label>
							</td>
							<td>
								<input type="text" name="url_facebook" id="url_facebook" value="<?=$arResult["FORM"]["url_facebook"]?>" placeholder="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_FACEBOOKMESSENGER_PLACEHOLDER')?>" class="imconnector-step-input imconnector-botframework-url-input">
							</td>
						</tr>
						<tr>
							<td>
							</td>
							<td>
								<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-info">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_URL_FACEBOOKMESSENGER_EXAMPLE')?>
								</div>
							</td>
						</tr>
					</table>
				</div><!--imconnector-livechat-public-link-settings-inner-->

			<div class="imconnector-step-text">
				<input type="submit" name="<?=$arResult["CONNECTOR"]?>_save" class="webform-small-button webform-small-button-accept" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SAVE')?>">
			</div>

			<?if($arResult["SAVE_STATUS"]):?>
				<div class="imconnector-step-text">
					<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_SIMPLE_FORM_DESCRIPTION_TESTED')?></div>

					<input type="submit" name="<?=$arResult["CONNECTOR"]?>_tested" class="webform-small-button webform-small-button-accept" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_BOTFRAMEWORK_TESTED')?>">
				</div>
			<?endif;?>

			<?if(!empty($arResult["STATUS"])):?>
				<?include 'final.php';?>
			<?endif;?>

				<?=thurly_sessid_post();?>
			</form>
		</div>
	<?endif;?>
<?endif;?>
	</div>
</div>
<?endif;?>