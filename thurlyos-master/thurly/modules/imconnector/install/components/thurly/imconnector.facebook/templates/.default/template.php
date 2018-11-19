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
			<div class="imconnector-facebook-block-title imconnector-facebook-block-title-no-connect">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<?if($arResult["ERROR_STATUS"]):?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_CONNECTOR_ERROR_STATUS')?></td>
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
			<div class="imconnector-facebook-block-title">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<table class="imconnector-connect-table">
					<?if(!empty($arResult["FORM"]["PAGE"]["NAME"])):?>
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_PAGE')?></td>
							<td>
								<a href="<?=$arResult["FORM"]["PAGE"]["URL"]?>"
								   class="imconnector-connect-link"
								   target="_blank">
									<?=$arResult["FORM"]["PAGE"]["NAME"]?>
								</a>
							</td>
						</tr>
						<?if($arResult["FORM"]["PAGE"]["URL_IM"]):?>
							<tr>
								<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_PAGE_IM')?></td>
								<td>
									<a href="<?=$arResult["FORM"]["PAGE"]["URL_IM"]?>"
									   class="imconnector-connect-link"
									   target="_blank">
										<?=$arResult["FORM"]["PAGE"]["URL_IM"]?>
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
		<?endif;?>
		<label class="imconnector-facebook-button" for="imconnector-facebook" onclick="showHideImconnectors(this)">
			<?=$arResult["NAME"]?>
			<span class="imconnector-button-show"
				  id="imconnector-facebook-button-show"><?
				if(!empty($arResult["PAGE"])):?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_COLLAPSE')?><?
				else:?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DEPLOY')?><?
				endif;?></span>
		</label>
		<input type="checkbox"
			   id="imconnector-facebook"
			   class="imconnector-checkbox" hidden>
		<div class="imconnector-wrapper">
			<?if(!empty($arResult["ACTIVE_STATUS"]) && !empty($arResult["FORM"]["STEP"])):?>
				<div class="imconnector-wrapper-title-in">
					<?
					switch ($arResult["FORM"]["STEP"])
					{
						case 1:
							echo Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_STEP_1_OF_3_TITLE');
							break;
						case 2:
							echo Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_STEP_2_OF_3_TITLE');
							break;
						case 3:
							echo Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_STEP_3_OF_3_TITLE');
							break;
					}
					?>
					<span class="imconnector-wrapper-title-nav"><?=str_replace('#STEP#', $arResult["FORM"]["STEP"], Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_STEP_N_OF_3'))?></span>
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
						<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_INDEX_DESCRIPTION')?>
					</div>
					<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
						<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
						<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active" class="webform-small-button webform-small-button-accept webform-small-button-accept-nomargin" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_CONNECT_PAGE')?>">
						<?=thurly_sessid_post();?>
					</form>
				<?else:?>
					<?if(!empty($arResult["PAGE"])):?>
						<?if(empty($arResult["FORM"]["USER"]["INFO"]) && empty($arResult["FORM"]["GROUP"])):?>
							<?if(!empty($arResult["FORM"]["USER"]["URI"])):?>
							<table class="imconnector-facebook-table">
								<tr>
									<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_LOG_IN_UNDER_AN_ADMINISTRATOR_ACCOUNT_PAGE')?>:</td>
									<td>
										<a href="javascript:void(0)" onclick="BX.util.popup('<?=$arResult["FORM"]["USER"]["URI"]?>', 700, 525)" class="imconnector-facebook-button-autorize"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_LOG_IN')?></a>
									</td>
								</tr>
							</table>
							<?endif;?>
						<?elseif(!empty($arResult["FORM"]['USER']['IS_EXPIRED_TOKEN_USER'])):?>
							<table class="imconnector-facebook-table">
								<tr>
									<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_EXPIRED_ACCOUNT_TOKEN')?>:</td>
									<td><?if(!empty($arResult["FORM"]["USER"]["URI"])):?>
										<a href="javascript:void(0)" onclick="BX.util.popup('<?=$arResult["FORM"]["USER"]["URI"]?>', 700, 525)" class="imconnector-facebook-button-autorize"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_LOG_IN')?></a>
								<?endif;?></td>
								</tr>
							</table>
							<div class="imconnector-settings-message imconnector-settings-message-info imconnector-settings-message-align-left">
								<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_EXPIRED_ACCOUNT_TOKEN_WARNING')?>
							</div>
						<?else:?>
							<table class="imconnector-facebook-table">
								<tr>
									<td>
										<div class="imconnector-facebook-autorizate"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_YOU_ARE_LOGGED_AS')?>:</div>
										<img class="imconnector-facebook-avatar" src="<?=$templateFolder?>/images/imconnector-facebook-icon.png" alt="">
										<div class="imconnector-facebook-name"><a href="<?=$arResult["FORM"]["USER"]["INFO"]["URL"]?>" target="_blank" ><?=$arResult["FORM"]["USER"]["INFO"]["NAME"]?></a></div>
									</td>
									<td>
										<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
											<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
											<input type="hidden" name="user_id" value="<?=$arResult["FORM"]["USER"]["INFO"]["ID"]?>">
											<input type="submit" name="<?=$arResult["CONNECTOR"]?>_del_user" class="imconnector-facebook-link-a" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_DEL_REFERENCE')?>">
											<?=thurly_sessid_post();?>
										</form>
								</tr>
							</table>
						<?endif;?>


						<?if(!empty($arResult["FORM"]["USER"]["INFO"]) || !empty($arResult["FORM"]['USER']['IS_EXPIRED_TOKEN_USER'])):?>
							<?if(empty($arResult["FORM"]["PAGES"]) && empty($arResult["FORM"]['USER']['IS_EXPIRED_TOKEN_USER'])):?>
				<div class="imconnector-intro">
					<div class="imconnector-intro-text">
											<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_THERE_IS_NO_PAGE_WHERE_THE_ADMINISTRATOR')?>
					</div>
								<a href="https://www.facebook.com/pages/create/" class="webform-small-button webform-small-button-accept webform-small-button-accept-nomargin" target="_blank"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_TO_CREATE_A_PAGE')?></a>
				</div>
							<?else:?>
								<?if(empty($arResult["FORM"]["PAGE"])):?>
									<div class="imconnector-facebook-select-page">
										<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_SELECT_THE_PAGE')?>:
										<table class="imconnector-facebook-table-2">
											<?foreach ($arResult["FORM"]["PAGES"] as $page):?>
												<?if(empty($page['ACTIVE'])):?>
													<tr>
														<td>
															<img class="imconnector-facebook-avatar" src="<?=$templateFolder?>/images/imconnector-facebook-icon.png" alt="">
															<div class="imconnector-facebook-name"><a href="<?=$page["INFO"]["URL"]?>" target="_blank"><?=$page["INFO"]["NAME"]?></a></div></td>
														<td>
															<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
																<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
																<input type="hidden" name="page_id" value="<?=$page["INFO"]["ID"]?>">
																<input type="submit" name="<?=$arResult["CONNECTOR"]?>_authorization_page" class="webform-small-button webform-small-button-transparent" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_CONNECT_PAGE')?>">
																<?=thurly_sessid_post();?>
															</form>
														</td>
													</tr>
												<?endif;?>
											<?endforeach;?>
										</table>
									</div>
								<?else:?>
									<ul class="imconnector-facebook-ul" id="imconnector-facebook-ul">
										<li class="imconnector-facebook-li">
											<div class="imconnector-facebook-autorizate"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_CONNECTED_PAGE')?>:</div>
											<img class="imconnector-facebook-avatar" src="<?=$templateFolder?>/images/imconnector-facebook-icon.png" alt="">
											<div class="imconnector-facebook-name"><a href="<?=$arResult["FORM"]["PAGE"]["URL"]?>" target="_blank"><?=$arResult["FORM"]["PAGE"]["NAME"]?></a></div>
											<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
												<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
												<input type="hidden" name="page_id" value="<?=$arResult["FORM"]["PAGE"]["ID"]?>">
												<input type="submit" name="<?=$arResult["CONNECTOR"]?>_del_page" class="imconnector-facebook-link-a" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_DEL_REFERENCE')?>">
												<?=thurly_sessid_post();?>
											</form>
										</li>
										<?if(count($arResult["FORM"]["PAGES"])>1):?>
											<li class="imconnector-facebook-li imconnector-facebook-li-hidden"
												id="imconnector-facebook-li-hidden">
												<table id="imconnector-facebook-li-table">
													<?
													$flag = false;
													foreach ($arResult["FORM"]["PAGES"] as $page):?>
														<?if(empty($page['ACTIVE'])):?>
															<tr>
																<td><?if(empty($flag)):
																		?><div class="imconnector-facebook-autorizate"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_OTHER_PAGES')?>:</div><?
																		$flag = true;
																	endif;?></td>
																<td>
																	<img class="imconnector-facebook-avatar" src="<?=$templateFolder?>/images/imconnector-facebook-icon.png" alt="">
																	<div class="imconnector-facebook-name"><a href="<?=$page["INFO"]["URL"]?>" target="_blank"><?=$page["INFO"]["NAME"]?></a></div></td>
																<td>
																	<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
																		<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
																		<input type="hidden" name="page_id" value="<?=$page["INFO"]["ID"]?>">
																		<input type="submit" name="<?=$arResult["CONNECTOR"]?>_authorization_page" class="webform-small-button webform-small-button-transparent" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_CHANGE_PAGE')?>">
																		<?=thurly_sessid_post();?>
																	</form>
																</td>
															</tr>
														<?endif;?>
													<?endforeach;?>
												</table>
											</li>
											<li class="imconnector-facebook-li imconnector-facebook-li-show"
												id="imconnector-facebook-li-show">
												<a href=""
												   class="imconnector-facebook-li-show-link"
												   onclick="return showhideulfb()"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_FACEBOOK_MY_OTHER_PAGES')?></a>
											</li>
										<?endif;?>
									</ul>
								<?endif;?>
							<?endif;?>
						<?endif?>
					<?endif;?>
				<?endif;?>
			</div>
		</div>
	</div>
<?endif;?>
