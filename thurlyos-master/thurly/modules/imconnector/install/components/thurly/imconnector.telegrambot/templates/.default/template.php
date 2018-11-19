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
			<div class="imconnector-telegrambot-block-title imconnector-telegrambot-block-title-no-connect">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<?if($arResult["ERROR_STATUS"]):?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_CONNECTOR_ERROR_STATUS')?></td>
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
			<div class="imconnector-telegrambot-block-title">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<table class="imconnector-connect-table">
					<?if(!empty($arResult["INFO_CONNECTION"])):?>
						<?if(!empty($arResult["INFO_CONNECTION"]['NAME'])):?>
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_NAME_BOT')?></td>
							<td>
								<?=$arResult["INFO_CONNECTION"]['NAME']?>
							</td>
						</tr>
						<?endif;?>
						<?if(!empty($arResult["INFO_CONNECTION"]['URL'])):?>
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_LINK')?></td>
							<td>
								<a href="<?=$arResult["INFO_CONNECTION"]['URL']?>"
								   class="imconnector-connect-link"
								   target="_blank">
									<?=$arResult["INFO_CONNECTION"]['URL']?>
								</a>
										<span class="imconnector-connect-link-copy"
											  for="imconnector-telegram-link-copy"
											  onclick="copyImconnector(this)"></span>
								<input type="text"
									   class="imconnector-connect-link-input-hidden"
									   id="imconnector-telegram-link-copy"
									   value="<?=$arResult["INFO_CONNECTION"]['URL']?>">
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
		<label class="imconnector-telegrambot-button" for="imconnector-telegrambot" onclick="showHideImconnectors(this)">
	<?=$arResult["NAME"]?>
			<span class="imconnector-button-show"
				  id="imconnector-telegrambot-button-show"><?
				if(!empty($arResult["PAGE"])):?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_COLLAPSE')?><?
				else:?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DEPLOY')?><?
				endif;?></span>
		</label>

		<input type="checkbox"
			   id="imconnector-telegrambot"
			   class="imconnector-checkbox" hidden<?
		if(!empty($arResult["PAGE"])):?> checked<?endif;
		?>>
	<div class="imconnector-wrapper">
	<?if(empty($arResult["ACTIVE_STATUS"]) || $arResult["PAGE"] == 'index'):?>
		<div class="imconnector-wrapper-step">
			<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_INDEX_DESCRIPTION')?></div>
			<div class="imconnector-create">
			<form action="<?=$arResult["URL"]["MASTER_NEW"]?>" method="post" class="webform-small-button webform-small-button-accept">
				<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
				<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
					   class="webform-small-button-text"
					   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_CREATE_NEW_BOT')?>">
				<?=thurly_sessid_post();?>
			</form>
			<form action="<?=$arResult["URL"]["MASTER"]?>" method="post" class="webform-small-button webform-small-button-accept">
				<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
				<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
					   class="webform-small-button webform-small-button-text"
					   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_THERE_IS_A_BOT_TO_KNOW_THE_TOKEN')?>">
				<?=thurly_sessid_post();?>
			</form>
			<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post" class="webform-small-button">
				<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
				<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active"
					   class="webform-small-button-text"
					   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_I_KNOW_TOKEN')?>">
				<?=thurly_sessid_post();?>
			</form>
			</div>
		</div>
	<?else:?>
		<?if($arResult["PAGE"] == 'master_new'):?>
		<div class="imconnector-wrapper-step">
			<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_6_STEPS_TITLE')?></div>
			<div class="imconnector-step">
					<div class="imconnector-step-item<?if(empty($arResult['error']) && empty($arResult["STATUS"])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-telegrambot-step-item-1">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_1_OF_6_TITLE')?>
							<span
								class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_1_OF_6')?></span>
						</label>
						<input type="radio"
							   id="imconnector-telegrambot-step-item-1"
							   class="imconnector-toggle"
							   name="imconnector-telegram-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-telegrambot-step-1">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_1_OF_6_DESCRIPTION_1')?>
							</div>
							<img  class="imconnector-step-img"
								  src="<?=$templateFolder?>/images/imconnector-telegram-step-1.png" alt="">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-telegrambot-step-item-2"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

					<div class="imconnector-step-item" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-telegrambot-step-item-2">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_2_OF_6_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_2_OF_6')?></span>
						</label>
						<input type="radio"
							   id="imconnector-telegrambot-step-item-2"
							   class="imconnector-toggle"
							   name="imconnector-telegram-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-telegrambot-step-2">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_2_OF_6_DESCRIPTION_1')?>
							</div>
							<img class="imconnector-step-img"
								  src="<?=$templateFolder?>/images/imconnector-telegram-step-2.png" alt="">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-telegrambot-step-item-3"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

					<div class="imconnector-step-item" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-telegrambot-step-item-3">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_3_OF_6_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_3_OF_6')?></span>
						</label>
						<input type="radio"
							   id="imconnector-telegrambot-step-item-3"
							   class="imconnector-toggle"
							   name="imconnector-telegram-accordeon"
							   hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-telegrambot-step-3">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_3_OF_6_DESCRIPTION_1')?>
							</div>
							<img  class="imconnector-step-img"
								  src="<?=$templateFolder?>/images/imconnector-telegram-step-3.png" alt="">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-telegrambot-step-item-4"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

					<div class="imconnector-step-item" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-telegrambot-step-item-4">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_4_OF_6_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_4_OF_6')?></span>
						</label>
						<input type="radio"
							   id="imconnector-telegrambot-step-item-4"
							   class="imconnector-toggle"
							   name="imconnector-telegram-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-telegrambot-step-4">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_4_OF_6_DESCRIPTION_1')?>
							</div>
							<img class="imconnector-step-img"
								  src="<?=$templateFolder?>/images/imconnector-telegram-step-4.png" alt="">
							<div class="imconnector-step-next">
								<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
									   for="imconnector-telegrambot-step-item-5"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
							</div>
						</div>
					</div>

					<div class="imconnector-step-item<?if(!empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
						<label class="imconnector-step-item-title"
							   for="imconnector-telegrambot-step-item-5">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_5_OF_6_TITLE')?>
							<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_5_OF_6')?></span>
						</label>
						<input type="radio"
							   id="imconnector-telegrambot-step-item-5"
							   class="imconnector-toggle"
							   name="imconnector-telegram-accordeon" hidden>
						<div class="imconnector-step-wrapper"
							 id="imconnector-telegrambot-step-5">
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
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_5_OF_6_DESCRIPTION_1')?>
							</div>
							<img class="imconnector-step-img"
								  src="<?=$templateFolder?>/images/imconnector-telegram-step-5.png" alt="">
							<div class="imconnector-step-text">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_5_OF_6_DESCRIPTION_2')?>
							</div>
							<form action="<?=$arResult["URL"]["MASTER_NEW"]?>" method="post">
								<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
							<table class="imconnector-step-table">
								<tr>
									<td>
										<input type="text"
											   name="api_token"
											   class="imconnector-step-input"
											   id="imconnector-telegrambot-step-input"
											   value="<?=$arResult["FORM"]["api_token"]?>"
												<?=$arResult["placeholder"]["api_token"]?$placeholder:'';?>
											   onkeyup="checkTelegramThird();"
											   onmouseout="checkTelegramThird()">
									</td>
									<td>
										<input type="submit"
											   name="<?=$arResult["CONNECTOR"]?>_save"
											   class="webform-small-button webform-small-button-accept"
											   id="webform-small-button"
											   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_SAVE')?>"
											   disabled>
									</td>
								</tr>
							</table>
								<?=thurly_sessid_post();?>
							</form>
							<script>
								var inputlinkCheckBotThird = document.getElementById('imconnector-telegrambot-step-input');
								document.getElementById('webform-small-button').disabled = inputlinkCheckBotThird.value ? false : "disabled";
								function checkTelegramThird() {
									document.getElementById('webform-small-button').disabled = inputlinkCheckBotThird.value ? false : "disabled";
								}
							</script>
						</div>
					</div>
			<?if(!empty($arResult["STATUS"])):?>
				<div class="imconnector-step-item<?if(empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-telegrambot-step-item-6">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_6_OF_6_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_6_OF_6')?></span>
						</label>
					<input type="radio"
						   id="imconnector-telegrambot-step-item-6"
						   class="imconnector-toggle"
						   name="imconnector-telegram-accordeon" hidden>
					<div class="imconnector-step-wrapper"
						 id="imconnector-telegrambot-step-6">

						<?include 'final.php';?>

					</div>
				</div>
			<?endif;?>
			</div>
		</div>
		<?elseif($arResult["PAGE"] == 'master'):?>
		<div class="imconnector-wrapper-step">
			<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_3_STEPS_TITLE')?></div>
			<div class="imconnector-step">
				<div class="imconnector-step-item<?if(empty($arResult['error']) && empty($arResult["STATUS"])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-telegrambot-step-item-1-1">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_1_OF_3_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_1_OF_3')?></span>
					</label>
					<input type="radio"
						   id="imconnector-telegrambot-step-item-1-1"
						   class="imconnector-toggle"
						   name="imconnector-telegram-accordeon" hidden>
					<div class="imconnector-step-wrapper"
						 id="imconnector-telegrambot-step-1-1">
						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_1_OF_3_DESCRIPTION_1')?>
						</div>
						<img  class="imconnector-step-img"
							  src="<?=$templateFolder?>/images/imconnector-telegram-step-1-1.png" alt="">
						<div class="imconnector-step-next">
							<label class="webform-small-button webform-small-button-transparent imconnector-button-arrow"
								   for="imconnector-telegrambot-step-item-2-2"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_NEXT')?></label>
						</div>
					</div>
				</div>

				<div class="imconnector-step-item<?if(!empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-telegrambot-step-item-2-2">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_2_OF_3_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_2_OF_3')?></span>
					</label>
					<input type="radio"
						   id="imconnector-telegrambot-step-item-2-2"
						   class="imconnector-toggle"
						   name="imconnector-telegram-accordeon" hidden>
					<div class="imconnector-step-wrapper"
						 id="imconnector-telegrambot-step-2-2">
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
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_2_OF_3_DESCRIPTION_1')?>
						</div>
						<img  class="imconnector-step-img"
							  src="<?=$templateFolder?>/images/imconnector-telegram-step-2-2.png" alt="">
						<div class="imconnector-step-text">
							<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_2_OF_3_DESCRIPTION_2')?>
						</div>
						<form action="<?=$arResult["URL"]["MASTER"]?>" method="post">
							<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
							<table class="imconnector-step-table">
								<tr>
									<td>
										<input type="text"
											   name="api_token"
											   class="imconnector-step-input"
											   id="imconnector-telegrambot-step-input-conn-b"
											   value="<?=$arResult["FORM"]["api_token"]?>"
											<?=$arResult["placeholder"]["api_token"]?$placeholder:'';?>
											   onkeyup="checkTelegramSecond()"
											   onmouseout="checkTelegramSecond()">
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
						<script>
							var inputlinkCheckBotSecond = document.getElementById('imconnector-telegrambot-step-input-conn-b');
							document.getElementById('webform-small-button-conn-b').disabled = inputlinkCheckBotSecond.value ? false : "disabled";
							function checkTelegramSecond() {
								document.getElementById('webform-small-button-conn-b').disabled = inputlinkCheckBotSecond.value ? false : "disabled";
							}
						</script>
					</div>
				</div>

			<?if(!empty($arResult["STATUS"])):?>
				<div class="imconnector-step-item<?if(empty($arResult['error'])):?> imconnector-step-item-show<?endif;?>" onclick="accordeon(this)">
					<label class="imconnector-step-item-title"
						   for="imconnector-telegrambot-step-item-3-3">
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_3_OF_3_TITLE')?>
						<span class="imconnector-step-item-nav"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_STEP_3_OF_3')?></span>
					</label>
					<input type="radio"
						   id="imconnector-telegrambot-step-item-3-3"
						   class="imconnector-toggle"
						   name="imconnector-telegram-accordeon" hidden>
					<div class="imconnector-step-wrapper"
						 id="imconnector-telegrambot-step-3-3">

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
					<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_SIMPLE_FORM_DESCRIPTION_1')?>
				</div>
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
				<div class="imconnector-step-text"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_API_TOKEN_NAME')?></div>
					<table class="imconnector-step-table">
					<tr>
						<td>
							<input type="text"
								   class="imconnector-step-input"
								   id="imconnector-telegrambot-have-bot"
								   name="api_token"
								   value="<?=$arResult["FORM"]["api_token"]?>"
									<?=$arResult["placeholder"]["api_token"]?$placeholder:'';?>
								   onkeyup="checkTelegramFirst()"
								   onmouseout="checkTelegramFirst()">
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
								<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_SIMPLE_FORM_DESCRIPTION_TESTED')?></div>
								<input type="submit"
									   class="webform-small-button webform-small-button-accept"
									   name="<?=$arResult["CONNECTOR"]?>_tested"
									   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_TESTED')?>">
					</div>
					<?endif;?>
					<?if($arResult["SAVE_STATUS"] && $arResult["CONNECTION_STATUS"]):?>
					<div class="imconnector-step-text">
								<div class="imconnector-intro"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_SIMPLE_FORM_DESCRIPTION_REGISTER')?></div>
								<input type="submit"
									   class="webform-small-button webform-small-button-accept"
									   name="<?=$arResult["CONNECTOR"]?>_register"
									   value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_TELEGRAMBOT_REGISTER')?>">
					</div>
					<?endif;?>
					<?=thurly_sessid_post();?>
				</form>

				<?if(!empty($arResult["STATUS"])):?>
					<?include 'final.php';?>
				<?endif;?>

				<script>
					var inputlinkCheckBot = document.getElementById('imconnector-telegrambot-have-bot');
					document.getElementById('webform-small-button-have-bot').disabled = inputlinkCheckBot.value ? false : "disabled";
					function checkTelegramFirst() {
						var inputlink = document.getElementById('imconnector-telegrambot-have-bot');
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
