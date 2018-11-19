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

$placeholder = ' placeholder="' . Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_PLACEHOLDER') . '"';
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
			<div class="imconnector-viber-block-title imconnector-viber-block-title-no-connect">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<?if($arResult["ERROR_STATUS"]):?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_CONNECTOR_ERROR_STATUS')?></td>
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
			<div class="imconnector-viber-block-title">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<table class="imconnector-connect-table">
					<?if(!empty($arResult["INFO_CONNECTION"])):?>
						<?if(!empty($arResult["INFO_CONNECTION"]['NAME'])):?>
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_NAME_BOT')?></td>
							<td>
								<?=$arResult["INFO_CONNECTION"]['NAME']?>
							</td>
						</tr>
						<?endif;?>
						<?if(!empty($arResult["INFO_CONNECTION"]['URL'])):?>
							<tr>
								<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_PUBLIC_ACCOUNT')?></td>
								<td>
									<a href="<?=$arResult["INFO_CONNECTION"]['URL']?>"
									   class="imconnector-connect-link"
									   target="_blank">
										<?=$arResult["INFO_CONNECTION"]['URL']?>
									</a>
									<span class="imconnector-connect-link-copy"
										  for="imconnector-viber-link-copy"
										  onclick="copyImconnector(this)"></span>
									<input type="text"
										   class="imconnector-connect-link-input-hidden"
										   id="imconnector-viber-link-copy"
										   value="<?=$arResult["INFO_CONNECTION"]['URL']?>">
								</td>
							</tr>
						<?endif;?>
						<?if(!empty($arResult["INFO_CONNECTION"]['URL_OTO'])):?>
							<tr>
								<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_CHAT_ONE_TO_ONE')?></td>
								<td>
									<a href="<?=$arResult["INFO_CONNECTION"]['URL_OTO']?>"
									   class="imconnector-connect-link"
									   target="_blank">
										<?=$arResult["INFO_CONNECTION"]['URL_OTO']?>
									</a>
									<span class="imconnector-connect-link-copy"
										  for="imconnector-viber-link-copy"
										  onclick="copyImconnector(this)"></span>
									<input type="text"
										   class="imconnector-connect-link-input-hidden"
										   id="imconnector-viber-link-copy"
										   value="<?=$arResult["INFO_CONNECTION"]['URL_OTO']?>">
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
		<label class="imconnector-viber-button" for="imconnector-viber" onclick="showHideImconnectors(this)">
	<?=$arResult["NAME"]?>
			<span class="imconnector-button-show"
				  id="imconnector-viber-button-show"><?
				if(!empty($arResult["PAGE"])):?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_COLLAPSE')?><?
				else:?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DEPLOY')?><?
				endif;?></span>
		</label>

		<input type="checkbox"
			   id="imconnector-viber"
			   class="imconnector-checkbox" hidden<?
		if(!empty($arResult["PAGE"])):?> checked<?endif;
		?>>
	<div class="imconnector-wrapper">
	<?if(empty($arResult["ACTIVE_STATUS"]) || $arResult["PAGE"] == 'index'):?>
		<div class="imconnector-wrapper-step">
			<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_INDEX_DESCRIPTION')?></div>
			<div class="imconnector-create">

				<form action="<?=$arResult["URL"]["MASTER_NEW"]?>" method="post" class="webform-small-button webform-small-button-accept">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
					<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
						   class="webform-small-button webform-small-button-text"
						   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_CREATE_A_PUBLIC_ACCOUNT')?>">
					<?=thurly_sessid_post();?>
				</form>
				<form action="<?=$arResult["URL"]["MASTER"]?>" method="post" class="webform-small-button webform-small-button-accept">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
					<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
						   class="webform-small-button webform-small-button-text"
						   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_AVAILABLE_PUBLIC_ACCOUNT')?>">
					<?=thurly_sessid_post();?>
				</form>
				<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post" class="webform-small-button">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
					<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
						   class="webform-small-button-text"
						   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_I_KNOW_KEY')?>">
					<?=thurly_sessid_post();?>
				</form>
			</div>
		</div>
	<?else:?>
		<?if($arResult["PAGE"] == 'master_new'):?>
			<div class="imconnector-wrapper-step">
				<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEPS_TITLE')?></div>
				<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEPS_TITLE_2', array('#URL#' => Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEPS_TITLE_2_URL')))?></div>
				<div class="imconnector-step">
					<div class="imconnector-step-item<?if(empty($arResult['error']) && empty($arResult["STATUS"])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-viber-step-item-1-5">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_5_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_5')?></span>
						</label>
						<input type="radio"
							   id="imconnector-viber-step-item-1-5"
							   class="imconnector-toggle"
							   name="imconnector-viber-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-viber-step-1-5">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_5_DESCRIPTION_1', array("#URL#" => Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_5_DESCRIPTION_1_URL')))?>
							</div>
							<img  class="imconnector-step-img"
								  src="<?=$templateFolder?>/images/imconnector-viber-1.jpg" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_5_TITLE')?>">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-viber-step-item-2-5"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

					<div class="imconnector-step-item" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-viber-step-item-2-5">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5')?></span>
						</label>
						<input type="radio"
							   id="imconnector-viber-step-item-2-5"
							   class="imconnector-toggle"
							   name="imconnector-viber-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-viber-step-2-5">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_DESCRIPTION_1')?>
							</div>
							<table>
								<tr style="vertical-align: top;">
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_ANDROID')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_DESCRIPTION_2_PIC_ANDROID')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_TITLE')?>"></td>
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_IOS')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_DESCRIPTION_2_PIC_IOS')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_TITLE')?>"></td>
								</tr>
							</table>
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_DESCRIPTION_2')?>
							</div>
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_DESCRIPTION_3')?>
							</div>
							<img  class="imconnector-step-img"
								  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_DESCRIPTION_3_PIC')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_5_TITLE')?>">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-viber-step-item-3-5"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

					<div class="imconnector-step-item" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-viber-step-item-3-5">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_3_OF_5')?></span>
						</label>
						<input type="radio"
							   id="imconnector-viber-step-item-3-5"
							   class="imconnector-toggle"
							   name="imconnector-viber-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-viber-step-2-5">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_1')?>
							</div>
							<table>
								<tr style="vertical-align: top;">
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_ANDROID')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_4_PIC_ANDROID')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>"></td>
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_IOS')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_4_PIC_IOS')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>"></td>
								</tr>
							</table>

							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_2')?>
							</div>
							<table>
								<tr style="vertical-align: top;">
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_ANDROID')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_5_PIC_ANDROID')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>"></td>
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_IOS')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_5_PIC_IOS')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>"></td>
								</tr>
							</table>

							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_3')?>
							</div>
							<img  class="imconnector-step-img"
								  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_6_PIC')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-viber-step-item-4-5"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

					<div class="imconnector-step-item<?if(!empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-viber-step-item-4-5">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_4_OF_5')?></span>
						</label>
						<input type="radio"
							   id="imconnector-viber-step-item-4-5"
							   class="imconnector-toggle"
							   name="imconnector-viber-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-viber-step-4-5">
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
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_DESCRIPTION_1')?>
							</div>
							<img  class="imconnector-step-img"
								  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_DESCRIPTION_7_PIC')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_TITLE')?>">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_DESCRIPTION_2')?>
							</div>
							<form action="<?=$arResult["URL"]["MASTER_NEW"]?>" method="post">
								<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
								<table class="imconnector-step-table">
									<tr>
										<td>
											<input type="text"
												   name="api_token"
												   class="imconnector-step-input"
												   id="imconnector-viber-step-input-conn-b"
												   value="<?=$arResult["FORM"]["api_token"]?>"
												<?=$arResult["placeholder"]["api_token"]?$placeholder:'';?>
												   onkeyup="checkViberSecond()"
												   onmouseout="checkViberSecond()">
										</td>
										<td>
											<input type="submit"
												   name="<?=$arResult["CONNECTOR"]?>_save"
												   class="webform-small-button webform-small-button-accept"
												   id="webform-small-button-conn-b"
												   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SAVE')?>"
												   disabled>
										</td>
									</tr>
								</table>
								<?=thurly_sessid_post();?>
							</form>
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_DESCRIPTION_3')?>
							</div>
							<script>
								var inputlinkCheckBotSecond = document.getElementById('imconnector-viber-step-input-conn-b');
								document.getElementById('webform-small-button-conn-b').disabled = inputlinkCheckBotSecond.value ? false : "disabled";
								function checkViberSecond() {
									document.getElementById('webform-small-button-conn-b').disabled = inputlinkCheckBotSecond.value ? false : "disabled";
								}
							</script>
						</div>
					</div>

					<?if(!empty($arResult["STATUS"])):?>
						<div class="imconnector-step-item<?if(empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
							<label class="imconnector-step-item-title"
								   for="imconnector-viber-step-item-5-5">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_3_OF_GENERAL_TITLE')?>
								<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_5_OF_5')?></span>
							</label>
							<input type="radio"
								   id="imconnector-viber-step-item-5-5"
								   class="imconnector-toggle"
								   name="imconnector-viber-accordeon" hidden>
							<div class="imconnector-step-wrapper"
								 id="imconnector-viber-step-5-5">

								<?include 'final.php';?>

							</div>
						</div>
					<?endif;?>
				</div>
			</div>
		<?elseif($arResult["PAGE"] == 'master'):?>
			<div class="imconnector-wrapper-step">
				<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEPS_TITLE')?></div>
				<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEPS_TITLE_2', array('#URL#' => Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEPS_TITLE_2_URL')))?></div>
				<div class="imconnector-step">
					<div class="imconnector-step-item<?if(empty($arResult['error']) && empty($arResult["STATUS"])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-viber-step-item-1-3">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_3')?></span>
						</label>
						<input type="radio"
							   id="imconnector-viber-step-item-1-3"
							   class="imconnector-toggle"
							   name="imconnector-viber-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-viber-step-1-3">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_1')?>
							</div>
							<table>
								<tr style="vertical-align: top;">
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_ANDROID')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_4_PIC_ANDROID')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>"></td>
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_IOS')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_4_PIC_IOS')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>"></td>
								</tr>
							</table>

							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_2')?>
							</div>
							<table>
								<tr style="vertical-align: top;">
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_ANDROID')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_5_PIC_ANDROID')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>"></td>
									<td>
										<div class="imconnector-step-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_DESCRIPTION_IOS')?>
										</div>
										<img  class="imconnector-step-img"
											  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_5_PIC_IOS')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>"></td>
								</tr>
							</table>

							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_3')?>
							</div>
							<img  class="imconnector-step-img"
								  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_DESCRIPTION_6_PIC')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_1_OF_GENERAL_TITLE')?>">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-viber-step-item-2-3"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

					<div class="imconnector-step-item<?if(!empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-viber-step-item-2-3">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_3')?></span>
						</label>
						<input type="radio"
							   id="imconnector-viber-step-item-2-3"
							   class="imconnector-toggle"
							   name="imconnector-viber-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-viber-step-2-3">
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
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_DESCRIPTION_1')?>
							</div>
							<img  class="imconnector-step-img"
								  src="<?=$templateFolder?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_DESCRIPTION_7_PIC')?>" alt="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_TITLE')?>">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_DESCRIPTION_2')?>
							</div>
							<form action="<?=$arResult["URL"]["MASTER_NEW"]?>" method="post">
								<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
								<table class="imconnector-step-table">
									<tr>
										<td>
											<input type="text"
												   name="api_token"
												   class="imconnector-step-input"
												   id="imconnector-viber-step-input-conn-b"
												   value="<?=$arResult["FORM"]["api_token"]?>"
												<?=$arResult["placeholder"]["api_token"]?$placeholder:'';?>
												   onkeyup="checkViberSecond()"
												   onmouseout="checkViberSecond()">
										</td>
										<td>
											<input type="submit"
												   name="<?=$arResult["CONNECTOR"]?>_save"
												   class="webform-small-button webform-small-button-accept"
												   id="webform-small-button-conn-b"
												   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SAVE')?>"
												   disabled>
										</td>
									</tr>
								</table>
								<?=thurly_sessid_post();?>
							</form>
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_2_OF_GENERAL_DESCRIPTION_3')?>
							</div>
							<script>
								var inputlinkCheckBotSecond = document.getElementById('imconnector-viber-step-input-conn-b');
								document.getElementById('webform-small-button-conn-b').disabled = inputlinkCheckBotSecond.value ? false : "disabled";
								function checkViberSecond() {
									document.getElementById('webform-small-button-conn-b').disabled = inputlinkCheckBotSecond.value ? false : "disabled";
								}
							</script>
						</div>
					</div>

					<?if(!empty($arResult["STATUS"])):?>
						<div class="imconnector-step-item<?if(empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
							<label class="imconnector-step-item-title"
								   for="imconnector-viber-step-item-3-3">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_3_OF_GENERAL_TITLE')?>
								<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEP_3_OF_3')?></span>
							</label>
							<input type="radio"
								   id="imconnector-viber-step-item-3-3"
								   class="imconnector-toggle"
								   name="imconnector-viber-accordeon" hidden>
							<div class="imconnector-step-wrapper"
								 id="imconnector-viber-step-3-3">

								<?include 'final.php';?>

							</div>
						</div>
					<?endif;?>
				</div>
			</div>
		<?elseif($arResult["PAGE"] == 'simple_form'):?>
			<div class="imconnector-wrapper-step">
				<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
				<div class="imconnector-intro">
					<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_SIMPLE_FORM_DESCRIPTION_1')?>
				</div>
					<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEPS_TITLE_2', array('#URL#' => Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_STEPS_TITLE_2_URL')))?></div>
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
				<div class="imconnector-step-text"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_API_KEY')?></div>
					<table class="imconnector-step-table">
					<tr>
						<td>
							<input type="text"
								   class="imconnector-step-input"
								   id="imconnector-viber-have-bot"
								   name="api_token"
								   value="<?=$arResult["FORM"]["api_token"]?>"
									<?=$arResult["placeholder"]["api_token"]?$placeholder:'';?>
								   onkeyup="checkViberFirst()"
								   onmouseout="checkViberFirst()">
						</td>
						<td>
							<input type="submit"
								   class="webform-small-button webform-small-button-accept"
								   id="webform-small-button-have-bot"
								   name="<?=$arResult["CONNECTOR"]?>_save"
								   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SAVE')?>"
								   disabled>
						</td>
					</tr>
					</table>
					<?if($arResult["SAVE_STATUS"]):?>
					<div class="imconnector-step-text">
								<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_SIMPLE_FORM_DESCRIPTION_TESTED')?></div>
								<input type="submit"
									   class="webform-small-button webform-small-button-accept"
									   name="<?=$arResult["CONNECTOR"]?>_tested"
									   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_TESTED')?>">
					</div>
					<?endif;?>
					<?if($arResult["SAVE_STATUS"] && $arResult["CONNECTION_STATUS"]):?>
					<div class="imconnector-step-text">
								<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_SIMPLE_FORM_DESCRIPTION_REGISTER')?></div>
								<input type="submit"
									   class="webform-small-button webform-small-button-accept"
									   name="<?=$arResult["CONNECTOR"]?>_register"
									   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VIBER_REGISTER')?>">
					</div>
					<?endif;?>
					<?=thurly_sessid_post();?>
				</form>

				<?if(!empty($arResult["STATUS"])):?>
					<?include 'final.php';?>
				<?endif;?>

				<script>
					var inputlinkCheckBot = document.getElementById('imconnector-viber-have-bot');
					document.getElementById('webform-small-button-have-bot').disabled = inputlinkCheckBot.value ? false : "disabled";
					function checkViberFirst() {
						var inputlink = document.getElementById('imconnector-viber-have-bot');
						document.getElementById('webform-small-button-have-bot').disabled = inputlink.value ? false : "disabled";
					}
				</script>
					<?=thurly_sessid_post();?>
				</form>
			</div>
		<?endif;?>
	<?endif;?>
		</div>
	</div>
<?endif;?>