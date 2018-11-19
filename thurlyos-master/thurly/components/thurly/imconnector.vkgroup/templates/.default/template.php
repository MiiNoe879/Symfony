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
			<div class="imconnector-vkgroup-block-title imconnector-vkgroup-block-title-no-connect">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<?if($arResult["ERROR_STATUS"]):?>
					<table class="imconnector-connect-table">
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CONNECTOR_ERROR_STATUS')?></td>
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
			<div class="imconnector-vkgroup-block-title">
				<?=$arResult["NAME"]?>
			</div>
			<div class="imconnector-connect">
				<table class="imconnector-connect-table">
					<?if(!empty($arResult["FORM"]["GROUP"]["NAME"])):?>
							<tr>
								<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_GROUP')?></td>
								<td>
									<a href="<?=$arResult["FORM"]["GROUP"]["URL"]?>"
									   class="imconnector-connect-link"
									   target="_blank">
										<?=$arResult["FORM"]["GROUP"]["NAME"]?>
									</a>
								</td>
							</tr>
					<?endif;?>
					<?if(!empty($arResult["FORM"]["GROUP"]["URL_IM"])):?>
						<tr>
							<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_GROUP_IM')?></td>
							<td>
								<a href="<?=$arResult["FORM"]["GROUP"]["URL_IM"]?>"
								   class="imconnector-connect-link"
								   target="_blank">
									<?=$arResult["FORM"]["GROUP"]["URL_IM"]?>
								</a>
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
	<label class="imconnector-vkgroup-button" for="imconnector-vkgroup" onclick="showHideImconnectors(this)">
		<?=$arResult["NAME"]?>
		<span class="imconnector-button-show"
			  id="imconnector-vkgroup-button-show"><?
			if(!empty($arResult["PAGE"])):?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_COLLAPSE')?><?
			else:?><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_DEPLOY')?><?
			endif;?></span>
	</label>
	<input type="checkbox"
		   id="imconnector-vkgroup"
		   class="imconnector-checkbox" hidden>
	<div class="imconnector-wrapper">
			<?if(!empty($arResult["ACTIVE_STATUS"]) && !empty($arResult["FORM"]["STEP"])):?>
				<div class="imconnector-wrapper-title-in">
					<?
					switch ($arResult["FORM"]["STEP"])
					{
						case 1:
							echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_STEP_1_OF_3_TITLE');
							break;
						case 2:
							echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_STEP_2_OF_3_TITLE');
							break;
						case 3:
							echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_STEP_3_OF_3_TITLE');
							break;
					}
					?>
					<span class="imconnector-wrapper-title-nav"><?=str_replace('#STEP#', $arResult["FORM"]["STEP"], Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_STEP_N_OF_3'))?></span>
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
				<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_INDEX_DESCRIPTION_NEW')?>
			</div>
				<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
					<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
					<input type="submit" name="<?=$arResult["CONNECTOR"]?>_active" class="webform-small-button webform-small-button-accept webform-small-button-accept-nomargin" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CONNECT')?>">
					<?=thurly_sessid_post();?>
				</form>
			<?else:?>
				<?if(!empty($arResult["PAGE"])):?>
					<?if(empty($arResult["FORM"]["USER"]["INFO"])):?>
						<table class="imconnector-vkgroup-table">
							<tr>
								<td><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_LOG_IN_UNDER_AN_ADMINISTRATOR_ACCOUNT_ENTITY')?></td>
								<td><?if(!empty($arResult["FORM"]["USER"]["URI"])):?>
									<a href="javascript:void(0)" onclick="BX.util.popup('<?=$arResult["FORM"]["USER"]["URI"]?>', 700, 525)" class="imconnector-vkgroup-button-autorize"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_LOG_IN')?></a>
								<?endif;?></td>
							</tr>
						</table>
					<?else:?>
						<table class="imconnector-vkgroup-table">
							<tr>
								<td>
									<div class="imconnector-vkgroup-autorizate"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_SETTINGS_YOU_ARE_LOGGED_AS')?>:</div>
									<img class="imconnector-vkgroup-avatar" src="<?=$templateFolder?>/images/imconnector-vk-icon.png" alt="">
									<div class="imconnector-vkgroup-name"><a href="<?=$arResult["FORM"]["USER"]["INFO"]["URL"]?>" target="_blank" ><?=$arResult["FORM"]["USER"]["INFO"]["NAME"]?></a></div>
								</td>
								<td>
									<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
										<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
										<input type="hidden" name="user_id" value="<?=$arResult["FORM"]["USER"]["INFO"]["ID"]?>">
										<input type="submit" name="<?=$arResult["CONNECTOR"]?>_del_user" class="imconnector-vkgroup-link-a" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_DEL_REFERENCE')?>">
										<?=thurly_sessid_post();?>
									</form>
							</tr>
						</table>
					<?endif;?>
					<?if(!empty($arResult["FORM"]["USER"]["INFO"])):?>
						<?if(empty($arResult["FORM"]["GROUPS"])):?>
					<div class="imconnector-intro">
						<div class="imconnector-intro-text">
										<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_THERE_IS_NO_ENTITY_WHERE_THE_ADMINISTRATOR')?>
						</div>
						<a href="https://vk.com/groups?tab=admin" class="webform-small-button webform-small-button-accept webform-small-button-accept-nomargin" target="_blank"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_TO_CREATE')?></a>
					</div>
						<?else:?>
							<?if(empty($arResult["FORM"]["GROUP"])):?>
								<div class="imconnector-vkgroup-select-page">
									<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_SELECT_THE_ENTITY')?>:
									<table class="imconnector-vkgroup-table-2">
										<?foreach ($arResult["FORM"]["GROUPS"] as $group):?>
											<?if(empty($group['ACTIVE'])):?>
												<tr>
													<td>
														<img class="imconnector-vkgroup-avatar" src="<?=$templateFolder?>/images/imconnector-vk-icon.png" alt="">
														<div class="imconnector-vkgroup-name"><a href="<?=$group["INFO"]["URL"]?>" target="_blank"><?=$group["INFO"]["NAME"]?></a></div></td>
													<td>
														<a href="javascript:void(0)" onclick="BX.util.popup('<?=$group["URI"]?>', 700, 525)" class="webform-small-button webform-small-button-transparent"><?
															if($group["INFO"]["TYPE"] == "event")
																echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CONNECT_PUBLIC_EVENT');
															elseif($group["INFO"]["TYPE"] == "page")
																echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CONNECT_PUBLIC_PAGE');
															else
																echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CONNECT_GROUP');
															?></a>
													</td>
												</tr>
											<?endif;?>
										<?endforeach;?>
									</table>
								</div>
							<?else:?>
								<ul class="imconnector-vkgroup-ul" id="imconnector-vkgroup-ul">
									<li class="imconnector-vkgroup-li">
										<div class="imconnector-vkgroup-autorizate"><?
											if($arResult["FORM"]["GROUP"]["TYPE"] == "event")
												echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CONNECTED_PUBLIC_EVENT');
											elseif($arResult["FORM"]["GROUP"]["TYPE"] == "page")
												echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CONNECTED_PUBLIC_PAGE');
											else
												echo Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CONNECTED_GROUP');?>:</div>
										<img class="imconnector-vkgroup-avatar" src="<?=$templateFolder?>/images/imconnector-vk-icon.png" alt="">
										<div class="imconnector-vkgroup-name"><a href="<?=$arResult["FORM"]["GROUP"]["URL"]?>" target="_blank"><?=$arResult["FORM"]["GROUP"]["NAME"]?></a></div>
										<form action="<?=$arResult["URL"]["SIMPLE_FORM"]?>" method="post">
											<input type="hidden" name="<?=$arResult["CONNECTOR"]?>_form" value="true">
											<input type="hidden" name="group_id" value="<?=$arResult["FORM"]["GROUP"]["ID"]?>">
											<input type="submit" name="<?=$arResult["CONNECTOR"]?>_del_group" class="imconnector-vkgroup-link-a" value="<?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_DEL_REFERENCE')?>">
											<?=thurly_sessid_post();?>
										</form>
									</li>
									<?if(count($arResult["FORM"]["GROUPS"])>1):?>
										<li class="imconnector-vkgroup-li imconnector-vkgroup-li-hidden"
											id="imconnector-vkgroup-li-hidden">
											<table id="imconnector-vkgroup-li-table">
												<?
												$flag = false;
												foreach ($arResult["FORM"]["GROUPS"] as $group):?>
													<?if(empty($group['ACTIVE'])):?>
														<tr>
															<td><?if(empty($flag)):
																	?><div class="imconnector-vkgroup-autorizate"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_OTHER_ENTITY')?>:</div><?
																	$flag = true;
																endif;?></td>
															<td>
																<img class="imconnector-vkgroup-avatar" src="<?=$templateFolder?>/images/imconnector-vk-icon.png" alt="">
																<div class="imconnector-vkgroup-name"><a href="<?=$group["INFO"]["URL"]?>" target="_blank"><?=$group["INFO"]["NAME"]?></a></div></td>
															<td>
																<a href="javascript:void(0)" onclick="BX.util.popup('<?=$group["URI"]?>', 700, 525)" class="webform-small-button webform-small-button-transparent"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_CHANGE')?></a>
															</td>
														</tr>
													<?endif;?>
												<?endforeach;?>
											</table>
										</li>
										<li class="imconnector-vkgroup-li imconnector-vkgroup-li-show"
											id="imconnector-vkgroup-li-show">
											<a href=""
											   class="imconnector-vkgroup-li-show-link"
											   onclick="return showhideulvk()"><?=Loc::getMessage('IMCONNECTOR_COMPONENT_VKGROUP_MY_OTHER_ENTITY')?></a>
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