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

Loc::loadMessages(__FILE__);

CUtil::InitJSCore(array("socnetlogdest"));
if (\Thurly\Main\Loader::includeModule('thurlyos'))
	\CThurlyOS::initLicenseInfoPopupJS();
?>
<script type="text/javascript">
	function imolOpenTrialPopup(dialogId, text)
	{
		if (typeof(B24) != 'undefined' && typeof(B24.licenseInfoPopup) != 'undefined')
		{
			B24.licenseInfoPopup.show(dialogId, "<?=CUtil::JSEscape(Loc::getMessage("IMOL_CONFIG_EDIT_POPUP_LIMITED_TITLE"))?>", text);
		}
		else
		{
			alert(text);
		}
	}
</script>
<div class="tel-set-main-wrap" id="tel-set-main-wrap">
	<div class="tel-set-top-title"><?=htmlspecialcharsbx($arResult['CONFIG']['LINE_NAME'])?></div>
	<div class="tel-set-inner-wrap">
		<form action="<?=POST_FORM_ACTION_URI?>" method="POST" id="imol_config_edit_form">
		<?=thurly_sessid_post()?>
		<input type="hidden" name="CONFIG_ID" value="<?=$arResult['CONFIG']['ID']?>" />
		<input type="hidden" name="form" value="imopenlines_edit_form" />
		<input type="hidden" name="action" value="save" id="imol_config_edit_form_action" />
		<div class="tel-set-cont-block">
			<?if(strlen($arResult["ERROR"])>0):?>
				<div class="tel-set-cont-error"><?=$arResult['ERROR']?></div>
			<?endif;?>
			<div class="tel-set-item">
				<div class="tel-set-item-num"></div>
				<div class="tel-set-item-cont-block">
					<div class="tel-set-item-cont">
						<div class="tel-set-item-select-block">
							<span class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_NAME")?> &nbsp; &mdash; &nbsp;</span>
							<input class="tel-set-inp tel-set-item-input" name="CONFIG[LINE_NAME]" value="<?=htmlspecialcharsbx($arResult['CONFIG']['LINE_NAME'])?>">
						</div>
					</div>
				</div>
			</div>
			<?if(defined('IMOL_FDC')):?>
			<div class="tel-set-item">
				<div class="tel-set-item-num"></div>
				<div class="tel-set-item-cont-block">
					<div class="tel-set-item-cont">
						<div class="tel-set-item-select-block">
							<span class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_LANG_SESSION_PRIORITY")?> &nbsp; &mdash; &nbsp;</span>
							<input class="tel-set-inp tel-set-item-input" name="CONFIG[SESSION_PRIORITY]" value="<?=htmlspecialcharsbx($arResult['CONFIG']['SESSION_PRIORITY'])?>" type="number" min="0" max="86400" style="min-width: 80px;width: 80px">
							<span><?=Loc::getMessage("IMOL_CONFIG_EDIT_LANG_SESSION_PRIORITY_2")?></span>
							<span class="tel-context-help" data-text="<?=htmlspecialcharsbx(Loc::getMessage("IMOL_CONFIG_EDIT_LANG_SESSION_PRIORITY_TIP"))?>">?</span>
						</div>
					</div>
				</div>
			</div>
			<?endif;?>
			<div class="tel-set-item">
				<div class="tel-set-item-num"></div>
				<div class="tel-set-item-cont-block">
					<div class="tel-set-item-cont">
						<div class="tel-set-item-select-block">
							<span class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CONNECTED_SOURCE")?> &nbsp; &mdash; &nbsp;</span>
							<div class="tel-set-item-connectors">
								<? $APPLICATION->IncludeComponent("thurly:imconnector.settings.status", "", Array("LINE" => $arResult['CONFIG']['ID'], "LINK_ON" => $arResult['CAN_EDIT_CONNECTOR'] ? "Y" : "")); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tel-set-cont-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_FLOW")?></div>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<input type="checkbox" id="id<?=(++$i)?>" name="CONFIG[CRM]" <? if ($arResult['CONFIG']['CRM'] == "Y" && $arResult['IS_CRM_INSTALLED'] == "Y") { ?>checked<? } ?> <? if ($arResult['IS_CRM_INSTALLED'] == "N") { ?>disabled<? } ?> value="Y" class="tel-set-checkbox"/>
					<span class="tel-set-item-num-text"><?=$i?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label for="id<?=$i?>" class="tel-set-cont-item-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM")?></label>
					<div class="tel-set-item-cont"  <? if ($arResult['IS_CRM_INSTALLED'] == "Y") { ?>style="display:none"<? } ?>>
						<div class="tel-set-item-text">
						<?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM_DISABLED")?>
						</div>
					</div>
					<div class="tel-set-item-cont"  <? if ($arResult['IS_CRM_INSTALLED'] == "N") { ?>style="display:none"<? } ?>>
						<div class="tel-set-item-select-block">
							<input id="imol_crm_forward" type="checkbox" name="CONFIG[CRM_FORWARD]" <?if($arResult["CONFIG"]["CRM_FORWARD"] == "Y") { ?>checked<? }?>  value="Y" class="tel-set-checkbox"/>
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM_FORWARD")?></div>
						</div>
						<div class="tel-set-item-select-block" style="background-color: #fff; padding-top: 10px;">
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM_CREATE")?> &nbsp; &mdash; &nbsp;</div>
							<select class="tel-set-inp tel-set-item-select" name="CONFIG[CRM_CREATE]" id="imol_crm_create">
								<option value="none" <?if($arResult["CONFIG"]["CRM_CREATE"] == "none") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM_CREATE_IN_CHAT")?></option>
								<option value="lead" <?if($arResult["CONFIG"]["CRM_CREATE"] == "lead") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM_CREATE_LEAD")?></option>
							</select>
							<span id="imol_crm_lead_desc" style="margin-top: 3px;margin-bottom: 10px; height: 15px;" class="tel-set-cont-item-title-description tel-set-item-crm-rule">
								(<?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM_CREATE_LEAD_DESC")?>)
							</span>
						</div>
						<script type="text/javascript">
							BX.bind(BX('imol_crm_create'), 'change', function(e){

								if (this.options[this.selectedIndex].value != 'none')
								{
									BX('imol_crm_source_rule').style.height = '19px';
								}
								else
								{
									BX('imol_crm_source_rule').style.height = '0';
								}
							});
						</script>
						<div id="imol_crm_source"  class="tel-set-item-select-block tel-set-item-crm-rule" style="background-color: #fff; height: 55px;">
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM_SOURCE")?> &nbsp; &mdash; &nbsp;</div>
							<select class="tel-set-inp tel-set-item-select" name="CONFIG[CRM_SOURCE]" id="imol_crm_source_select">
								<?foreach ($arResult['CRM_SOURCES'] as $value => $name):?>
									<option value="<?=$value?>" <?if($arResult["CONFIG"]["CRM_SOURCE"] == $value) { ?>selected<? }?> ><?=htmlspecialcharsbx($name)?></option>
								<?endforeach;?>
							</select>
							<span class="tel-context-help" data-text="<?=htmlspecialcharsbx(Loc::getMessage("IMOL_CONFIG_EDIT_CRM_SOURCE_TIP"))?>">?</span>
						</div>
						<div id="imol_crm_source_rule" class="tel-set-item-select-block tel-set-item-crm-rule"  style="<?=($arResult["CONFIG"]["CRM_CREATE"] != 'none'? 'height: 19px;': '')?>">
							<input type="checkbox" name="CONFIG[CRM_TRANSFER_CHANGE]" <?if($arResult["CONFIG"]["CRM_TRANSFER_CHANGE"] == "Y") { ?>checked<? }?>  value="Y" class="tel-set-checkbox"/>
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CRM_TRANSFER_CHANGE_2")?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<span class="tel-set-item-num-text"><?=(++$i)?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label class="tel-set-cont-item-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE")?></label>
					<div class="tel-set-item-cont">
						<div class="tel-set-item-text">
							<?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_DESC")?>
							<?if ($arResult['BUSINESS_USERS_LIMIT'] == 'Y'):?>
							<div class="tel-lock-holder-select" title="<?=GetMessage("IMOL_CONFIG_LOCK_ALT")?>"><div onclick='imolOpenTrialPopup("imol_queue", "<?=CUtil::JSEscape(Loc::getMessage("IMOL_CONFIG_EDIT_POPUP_LIMITED_TEXT"))?>")' class="tel-lock tel-lock-half"></div></div>
							<?endif;?>
						</div>
						<div class="tel-set-destination-container" id="users_for_queue">
						<?if (!$arResult['CAN_EDIT']):?>
							<?foreach ($arResult["QUEUE_DESTINATION"]["SELECTED"]["USERS"] as $userId):?>
							<span><span class="bx-destination-wrap-item"></span><span class="bx-destination bx-destination-users"><span class="bx-destination-text"><?=$arResult["QUEUE_DESTINATION"]["USERS"]['U'.$userId]["name"]?></span></span></span>
							<?endforeach;?>
						<?endif;?>
						</div>
						<div class="tel-set-item-select-block" style=" margin-top: 35px">
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TYPE")?>  &nbsp; &mdash; &nbsp;</div>
							<select class="tel-set-inp tel-set-item-select" name="CONFIG[QUEUE_TYPE]" id="QUEUE_TYPE">
									<option value="evenly" <?if($arResult["CONFIG"]["QUEUE_TYPE"] == "evenly") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TYPE_EVENLY")?></option>
									<option value="strictly" <?if($arResult["CONFIG"]["QUEUE_TYPE"] == "strictly") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TYPE_STRICTLY")?></option>
									<option value="all" <?if($arResult["CONFIG"]["QUEUE_TYPE"] == "all") { ?>selected<? }?> <?if(!\Thurly\Imopenlines\Limit::canUseQueueAll()) { ?>disabled<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TYPE_ALL")?></option>
							</select>
							<span class="tel-context-help" data-text="<?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TYPE_TIP")?><br><br><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TYPE_TIP_2")?><br><i><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TYPE_TIP_ASTERISK_2")?></i>">?</span>
							<?if (!\Thurly\Imopenlines\Limit::canUseQueueAll() || \Thurly\Imopenlines\Limit::isDemoLicense()):?>
							<span class="tel-lock-holder-select" title="<?=GetMessage("IMOL_CONFIG_LOCK_ALT")?>"><span onclick='imolOpenTrialPopup("imol_queue_all", "<?=CUtil::JSEscape(Loc::getMessage("IMOL_CONFIG_EDIT_POPUP_LIMITED_QUEUE_ALL"))?>")' class="tel-lock tel-lock-half"></span></span>
							<?endif;?>
							<script type="text/javascript">
								BX.bind(BX('QUEUE_TYPE'), 'change', function(e){
									var noAnswerBox = BX('imol_no_answer_rule');

									if (typeof(noAnswerBoxValue) == 'undefined' || noAnswerBox.options[noAnswerBox.options.selectedIndex].value != 'queue')
										noAnswerBoxValue = noAnswerBox.options[noAnswerBox.options.selectedIndex].value;

									noAnswerBox.innerHTML = '';

									var colorAnimate = false;
									if (this.options[this.selectedIndex].value == 'strictly' || this.options[this.selectedIndex].value == 'all')
									{
										//noAnswerBox.options[noAnswerBox.length] = BX.create("option", {attrs: { value: "form", disabled: "true"}, html: "<?=Loc::getMessage("IMOL_CONFIG_EDIT_NO_ANSWER_RULE_FORM")?>" });
										noAnswerBox.options[noAnswerBox.length] = BX.create("option", {attrs: { value: "text"}, html: "<?=Loc::getMessage("IMOL_CONFIG_EDIT_NO_ANSWER_RULE_TEXT")?>" });
										noAnswerBox.options[noAnswerBox.length] = BX.create("option", {attrs: { value: "none"}, html: "<?=Loc::getMessage("IMOL_CONFIG_EDIT_NO_ANSWER_RULE_NONE")?>" });
										if (this.options[this.selectedIndex].value == 'all')
										{
											BX('imol_queue_time_title').innerHTML = BX.message('IMOL_CONFIG_EDIT_NA_TIME');
											colorAnimate = true;
										}
										else if (BX('imol_queue_time_title').innerHTML != BX.message('IMOL_CONFIG_EDIT_QUEUE_TIME'))
										{
											BX('imol_queue_time_title').innerHTML = BX.message('IMOL_CONFIG_EDIT_QUEUE_TIME');
											colorAnimate = true;
										}
									}
									else
									{
										//noAnswerBox.options[noAnswerBox.length] = BX.create("option", {attrs: { value: "form", disabled: "true"}, html: "<?=Loc::getMessage("IMOL_CONFIG_EDIT_NO_ANSWER_RULE_FORM")?>" });
										noAnswerBox.options[noAnswerBox.length] = BX.create("option", {attrs: { value: "text"}, html: "<?=Loc::getMessage("IMOL_CONFIG_EDIT_NO_ANSWER_RULE_TEXT")?>" });
										noAnswerBox.options[noAnswerBox.length] = BX.create("option", {attrs: { value: "queue"}, html: "<?=Loc::getMessage("IMOL_CONFIG_EDIT_NO_ANSWER_RULE_QUEUE")?>" });
										noAnswerBox.options[noAnswerBox.length] = BX.create("option", {attrs: { value: "none"}, html: "<?=Loc::getMessage("IMOL_CONFIG_EDIT_NO_ANSWER_RULE_NONE")?>" });

										if (BX('imol_queue_time_title').innerHTML != BX.message('IMOL_CONFIG_EDIT_QUEUE_TIME'))
										{
											BX('imol_queue_time_title').innerHTML = BX.message('IMOL_CONFIG_EDIT_QUEUE_TIME');
											colorAnimate = true;
										}
									}
									if (colorAnimate)
									{
										BX.fx.colorAnimate.addRule('animationRule1', "#000", "#ccc", "color", 100, 1, true);
										BX.fx.colorAnimate(BX('imol_queue_time_title'), 'animationRule1');
									}

									for (var i = 0; i < noAnswerBox.options.length; i++)
									{
										if (noAnswerBox.options[i].value == noAnswerBoxValue)
										{
											noAnswerBox.options.selectedIndex = i;
										}
									}

									BX.OpenLinesConfigEdit.toggleSelectFormText(
										BX('imol_no_answer_rule'),
										BX('imol_no_answer_rule_form_form'),
										BX('imol_no_answer_rule_form_text'),
										BX('imol_no_answer_rule_text')
									);
								});
							</script>
						</div>
						<div class="tel-set-item-select-block tel-set-item-crm-rule" style="height: 55px">
							<script type="text/javascript">
								BX.message({'IMOL_CONFIG_EDIT_QUEUE_TIME': '<?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME")?>'});
								BX.message({'IMOL_CONFIG_EDIT_NA_TIME': '<?=Loc::getMessage("IMOL_CONFIG_EDIT_NA_TIME")?>'});
							</script>
							<?if($arResult["CONFIG"]["QUEUE_TYPE"] == "all"):?>
							<div class="tel-set-item-select-text"><span id="imol_queue_time_title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_NA_TIME")?></span>  &nbsp; &mdash; &nbsp;</div>
							<?else:?>
							<div class="tel-set-item-select-text"><span id="imol_queue_time_title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME")?></span>  &nbsp; &mdash; &nbsp;</div>
							<?endif?>
							<select class="tel-set-inp tel-set-item-select" name="CONFIG[QUEUE_TIME]">
								<option value="60" <?if($arResult["CONFIG"]["QUEUE_TIME"] == "60") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_1")?></option>
								<option value="180" <?if($arResult["CONFIG"]["QUEUE_TIME"] == "180") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_3")?></option>
								<option value="300" <?if($arResult["CONFIG"]["QUEUE_TIME"] == "300") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_5")?></option>
								<option value="600" <?if($arResult["CONFIG"]["QUEUE_TIME"] == "600") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_10")?></option>
								<option value="900" <?if($arResult["CONFIG"]["QUEUE_TIME"] == "900") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_15")?></option>
								<option value="1800" <?if($arResult["CONFIG"]["QUEUE_TIME"] == "1800") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_30")?></option>
							</select>
						</div>
						<div class="tel-set-item-select-block">
							<input id="imol_timeman" type="checkbox" name="CONFIG[TIMEMAN]" <?if($arResult["CONFIG"]["TIMEMAN"] == "Y") { ?>checked<? }?> value="Y" class="tel-set-checkbox"/>
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_TIMEMAN")?></div>
						</div>
						<?if (!IsModuleInstalled("timeman")):?>
						<script type="text/javascript">
							BX.bind(BX('imol_timeman'), 'change', function(e){
								BX('imol_timeman').checked = false;
								alert('<?=GetMessage(!IsModuleInstalled("thurlyos")? "IMOL_CONFIG_EDIT_TIMEMAN_SUPPORT_B24": "IMOL_CONFIG_EDIT_TIMEMAN_SUPPORT_CP")?>');
							});
						</script>
						<?endif;?>
						<div class="tel-set-item-select-block" style="margin-top: 15px">
							<input id="imol_welcome_bot" type="checkbox" name="CONFIG[WELCOME_BOT_ENABLE]" <?if($arResult["CONFIG"]["WELCOME_BOT_ENABLE"] == "Y") { ?>checked<? }?> value="Y" class="tel-set-checkbox"/>
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_BOT_JOIN_2")?> <span class="tel-context-help" data-text="<?=Loc::getMessage("IMOL_CONFIG_EDIT_BOT_JOIN_TIP")?>">?</span></div>
						</div>
						<div id="imol_welcome_bot_div" class="tel-set-item-crm-rule" style="height: <?=($arResult["CONFIG"]["WELCOME_BOT_ENABLE"] == "Y"? '215px': '0')?>;margin-left: 23px;">
							<div class="tel-set-item-select-block">
								<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_BOT_ID")?>  &nbsp; &mdash; &nbsp;</div>
								<select id="WELCOME_BOT_ID" class="tel-set-inp tel-set-item-select" name="CONFIG[WELCOME_BOT_ID]">
									<?foreach ($arResult['BOT_LIST'] as $value => $name):?>
										<option value="<?=$value?>" <?if($arResult["CONFIG"]["WELCOME_BOT_ID"] == $value) { ?>selected<? }?> ><?=htmlspecialcharsbx($name)?></option>
									<?endforeach;?>
								</select>
							</div>
							<div class="tel-set-item-select-block">
								<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_BOT_JOIN")?>  &nbsp; &mdash; &nbsp;</div>
								<select class="tel-set-inp tel-set-item-select" name="CONFIG[WELCOME_BOT_JOIN]">
									<option value="first" <?if($arResult["CONFIG"]["WELCOME_BOT_JOIN"] == "first") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_BOT_JOIN_FIRST")?></option>
									<option value="always" <?if($arResult["CONFIG"]["WELCOME_BOT_JOIN"] == "always") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_BOT_JOIN_ALWAYS")?></option>
								</select>
							</div>
							<div class="tel-set-item-select-block">
								<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_BOT_TIME")?>  &nbsp; &mdash; &nbsp;</div>
								<select class="tel-set-inp tel-set-item-select" name="CONFIG[WELCOME_BOT_TIME]">
									<option value="60" <?if($arResult["CONFIG"]["WELCOME_BOT_TIME"] == "60") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_1")?></option>
									<option value="180" <?if($arResult["CONFIG"]["WELCOME_BOT_TIME"] == "180") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_3")?></option>
									<option value="300" <?if($arResult["CONFIG"]["WELCOME_BOT_TIME"] == "300") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_5")?></option>
									<option value="600" <?if($arResult["CONFIG"]["WELCOME_BOT_TIME"] == "600") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_10")?></option>
									<option value="900" <?if($arResult["CONFIG"]["WELCOME_BOT_TIME"] == "900") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_15")?></option>
									<option value="1800" <?if($arResult["CONFIG"]["WELCOME_BOT_TIME"] == "1800") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_30")?></option>
									<option value="0" <?if($arResult["CONFIG"]["WELCOME_BOT_TIME"] == "0") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUEUE_TIME_0")?></option>
								</select>
								<span class="tel-context-help" data-text="<?=Loc::getMessage("IMOL_CONFIG_EDIT_BOT_TIME_TIP")?>">?</span>
							</div>
							<div class="tel-set-item-select-block">
								<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_BOT_LEFT")?>  &nbsp; &mdash; &nbsp;</div>
								<select class="tel-set-inp tel-set-item-select" name="CONFIG[WELCOME_BOT_LEFT]">
									<option value="queue" <?if($arResult["CONFIG"]["WELCOME_BOT_LEFT"] == "queue") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_BOT_LEFT_QUEUE")?></option>
									<option value="close" <?if($arResult["CONFIG"]["WELCOME_BOT_LEFT"] == "close") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_BOT_LEFT_CLOSE")?></option>
								</select>
							</div>
						</div>
						<script type="text/javascript">
							BX.bind(BX('imol_welcome_bot'), 'change', function(e){
							<?if(empty($arResult['BOT_LIST'])):?>
								BX('imol_welcome_bot').checked = false;
								alert('<?=GetMessage("IMOL_CONFIG_EDIT_BOT_EMPTY")?>');
							<?else:?>
								if (this.checked)
								{
									BX('imol_welcome_bot_div').style.height = '215px';
								}
								else
								{
									BX('imol_welcome_bot_div').style.height = '0';
								}
							<?endif?>
							});
						</script>
					</div>
				</div>
			</div>
			<?if ($arResult['CAN_EDIT']):?>
			<script type="text/javascript">
				BX.ready(function(){
					BX.message({LM_ADD1 : '<?=Loc::getMessage("IMOL_CONFIG_EDIT_LM_ADD1")?>', LM_ADD2 : '<?=Loc::getMessage("IMOL_CONFIG_EDIT_LM_ADD2")?>', LM_ERROR_BUSINESS: '<?=Loc::getMessage("IMOL_CONFIG_EDIT_LM_ERROR_BUSINESS")?>', 'LM_BUSINESS_USERS': '<?=$arResult['BUSINESS_USERS']?>', 'LM_BUSINESS_USERS_ON': '<?=$arResult['BUSINESS_USERS_LIMIT']?>'});
					BX.OpenLinesConfigEdit.initDestination(BX('users_for_queue'), 'QUEUE', <?=CUtil::PhpToJSObject($arResult["QUEUE_DESTINATION"])?>);
				});
			</script>
			<?endif;?>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<span class="tel-set-item-num-text"><?=(++$i)?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label class="tel-set-cont-item-title"><?=Loc::getMessage("IMOL_CONFIG_NO_ANSWER")?></label>
					<div class="tel-set-item-cont">
						<div class="tel-set-item-text">
							<?=Loc::getMessage("IMOL_CONFIG_NO_ANSWER_DESC")?>
						</div>
						<div class="tel-set-item-select-block">
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_NO_ANSWER_RULE")?>  &nbsp; &mdash; &nbsp;</div>
							<select class="tel-set-inp tel-set-item-select" name="CONFIG[NO_ANSWER_RULE]" id="imol_no_answer_rule">
								<?foreach($arResult["NO_ANSWER_RULES"] as $value=>$name):?>
									<option value="<?=$value?>" <?if($arResult["CONFIG"]["NO_ANSWER_RULE"] == $value) { ?>selected<? }?> <?if($value == 'disabled') { ?>disabled<? }?>><?=$name?></option>
								<?endforeach?>
							</select>
						</div>
						<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_no_answer_rule_form_form">
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_NO_ANSWER_FORM_ID")?>  &nbsp; &mdash; &nbsp;</div>
							<select class="tel-set-inp tel-set-item-select" name="CONFIG[NO_ANSWER_FORM_ID]">
							</select>
						</div>
						<div class="tel-set-item-text tel-set-item-crm-rule" id="imol_no_answer_rule_form_text">
							<?=Loc::getMessage("IMOL_CONFIG_NO_ANSWER_FORM_TEXT")?>
						</div>
						<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_no_answer_rule_text">
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_NO_ANSWER_TEXT")?>  &nbsp; &mdash; &nbsp;</div>
							<textarea class="tel-set-inp tel-set-item-textarea" name="CONFIG[NO_ANSWER_TEXT]"><?=htmlspecialcharsbx($arResult["CONFIG"]["NO_ANSWER_TEXT"])?></textarea>
						</div>
						<script type="text/javascript">
							BX.OpenLinesConfigEdit.toggleSelectFormText(
								BX('imol_no_answer_rule'),
								BX('imol_no_answer_rule_form_form'),
								BX('imol_no_answer_rule_form_text'),
								BX('imol_no_answer_rule_text')
							);
							BX.bind(BX('imol_no_answer_rule'), 'change', function(e){
								BX.OpenLinesConfigEdit.toggleSelectFormText(
									this,
									BX('imol_no_answer_rule_form_form'),
									BX('imol_no_answer_rule_form_text'),
									BX('imol_no_answer_rule_text')
								);
							});
						</script>
					</div>
				</div>
			</div>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					<input type="checkbox" id="id5" name="CONFIG[RECORDING]" checked value="Y" disabled class="tel-set-checkbox"/>
					<span class="tel-set-item-num-text"><?=(++$i)?>.</span>
				</div>
				<div class="tel-set-item-cont-block">
					<label for="id5" class="tel-set-cont-item-title">
						<?=Loc::getMessage("IMOL_CONFIG_RECORDING")?>
					</label>
					<span class="tel-set-cont-item-title-description" style="margin-top: -12px;margin-bottom: 12px;">(<?=Loc::getMessage("IMOL_CONFIG_RECORDING_DESC")?>)</span>
				</div>
			</div>
			<div class="tel-set-item">
				<div class="tel-set-item-cont-block">
					<div class="tel-set-item-cont">
						<div class="tel-set-item-select-block tel-set-item-select-quick">
							<span class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_QUICK_ANSWERS_STORAGE")?> &nbsp; &mdash; &nbsp;</span>
							<select class="tel-set-inp tel-set-item-select" name="CONFIG[QUICK_ANSWERS_IBLOCK_ID]">
								<?foreach($arResult['QUICK_ANSWERS_STORAGE_LIST'] as $id => $item)
								{?>
									<option value="<?=intval($id);?>"<?if($id == $arResult['CONFIG']['QUICK_ANSWERS_IBLOCK_ID']){?> selected<?}?>><?=htmlspecialcharsbx($item['NAME']);?></option>
								<?}?>
							</select>
							<?if($arResult['CONFIG']['QUICK_ANSWERS_IBLOCK_ID'] > 0)
							{
								echo Loc::getMessage('IMOL_CONFIG_QUICK_ANSWERS_LIST_MANAGE', array('#LIST_URL#' => $arResult['QUICK_ANSWERS_MANAGE_URL']));
							}
							else
							{
								echo Loc::getMessage('IMOL_CONFIG_QUICK_ANSWERS_CREATE_NEW', array('#LIST_URL#' => $arResult['QUICK_ANSWERS_MANAGE_URL']));
							}?>
						</div>
					</div>
					<span class="tel-set-cont-item-title-description" style="margin-top: -12px;margin-bottom: 12px;">(<?=Loc::getMessage("IMOL_CONFIG_QUICK_ANSWERS_DESC")?>)</span>
				</div>
			</div>
		</div>
		<!-- work time-->
		<div class="tel-set-cont-block">
			<div class="tel-set-cont-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME")?></div>
			<div class="tel-set-item">
				<div class="tel-set-item-num">
					&nbsp;<input type="checkbox" name="CONFIG[WORKTIME_ENABLE]" id="WORKTIME_ENABLE" class="tel-set-checkbox" value="Y" <? if ($arResult['CONFIG']['WORKTIME_ENABLE'] == "Y") { ?>checked<? } ?> />
				</div>
				<div class="tel-set-item-cont-block">
					<label for="WORKTIME_ENABLE" class="tel-set-cont-item-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_ENABLE")?></label>
					<div class="tel-set-item-cont tel-set-item-crm-rule" id="imol_worktime"<? if ($arResult['CONFIG']['WORKTIME_ENABLE'] == "Y") { ?>style="height: auto"<? } ?> >
						<table class="tel-set-item-table">
							<tr>
								<td class="tel-set-item-table-td">
									<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_TIMEZONE")?></div>
								</td>
								<td class="tel-set-item-table-td">&nbsp; &mdash; &nbsp;</td>
								<td class="tel-set-item-table-td">
									<select name="CONFIG[WORKTIME_TIMEZONE]" class="tel-set-inp tel-set-item-select">
										<?if (is_array($arResult["TIME_ZONE_LIST"]) && !empty($arResult["TIME_ZONE_LIST"])):?>
											<?foreach($arResult["TIME_ZONE_LIST"] as $tz=>$tz_name):?>
												<option value="<?=htmlspecialcharsbx($tz)?>"<?=($arResult["CONFIG"]["WORKTIME_TIMEZONE"] == $tz? ' selected="selected"' : '')?>><?=htmlspecialcharsbx($tz_name)?></option>
											<?endforeach?>
										<?endif?>
									</select>
								</td>
							</tr>
							<?if (!empty($arResult["WORKTIME_LIST_FROM"]) && !empty($arResult["WORKTIME_LIST_TO"])):?>
							<tr>
								<td class="tel-set-item-table-td">
									<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_TIME")?></div>
								</td>
								<td class="tel-set-item-table-td">&nbsp; &mdash; &nbsp;</td>
								<td class="tel-set-item-table-td">
									<select name="CONFIG[WORKTIME_FROM]" class="tel-set-inp tel-set-item-select" style="min-width: 70px">
										<?foreach($arResult["WORKTIME_LIST_FROM"] as $key => $val):?>
											<option value="<?= $key?>" <?if ($arResult["CONFIG"]["WORKTIME_FROM"] == $key) echo ' selected="selected" ';?>><?= $val?></option>
										<?endforeach;?>
									</select>
									&nbsp; &mdash; &nbsp;
									<select name="CONFIG[WORKTIME_TO]" class="tel-set-inp tel-set-item-select" style="min-width: 70px">
										<?foreach($arResult["WORKTIME_LIST_TO"] as $key => $val):?>
											<option value="<?= $key?>" <?if ($arResult["CONFIG"]["WORKTIME_TO"] == $key) echo ' selected="selected" ';?>><?= $val?></option>
										<?endforeach;?>
									</select>
								</td>
							</tr>
							<?endif?>
							<tr>
								<td class="tel-set-item-table-td">
									<div class="tel-set-item-select-text" style="vertical-align: top"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_DAYOFF")?></div>
								</td>
								<td class="tel-set-item-table-td">&nbsp; &mdash; &nbsp;</td>
								<td class="tel-set-item-table-td">
									<select size="7" multiple=true name="CONFIG[WORKTIME_DAYOFF][]" class="tel-set-inp tel-set-item-select-multiple ">
										<?foreach($arResult["WEEK_DAYS"] as $day):?>
											<option value="<?=$day?>" <?=(is_array($arResult["CONFIG"]["WORKTIME_DAYOFF"]) && in_array($day, $arResult["CONFIG"]["WORKTIME_DAYOFF"]) ? ' selected="selected"' : '')?>><?= GetMessage('IMOL_CONFIG_WEEK_'.$day)?></option>
										<?endforeach;?>
									</select>
								</td>
							</tr>

							<tr>
								<td class="tel-set-item-table-td" style="vertical-align: top; padding-top: 12px;">
									<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_HOLIDAYS")?></div>
								</td>
								<td class="tel-set-item-table-td" style="vertical-align: top; padding-top: 12px;">&nbsp; &mdash; &nbsp;</td>
								<td class="tel-set-item-table-td">
									<input type="text" name="CONFIG[WORKTIME_HOLIDAYS]" class="tel-set-inp" value="<?=htmlspecialcharsbx($arResult["CONFIG"]["WORKTIME_HOLIDAYS"])?>"/>
									<div class="tel-set-item-text" style="margin-top: 5px">(<?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_HOLIDAYS_EXAMPLE")?>)</div>
								</td>
							</tr>

							<tr>
								<td class="tel-set-item-table-td">
									<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_DAYOFF_RULE")?></div>
								</td>
								<td class="tel-set-item-table-td">&nbsp; &mdash; &nbsp;</td>
								<td class="tel-set-item-table-td">
									<select name="CONFIG[WORKTIME_DAYOFF_RULE]" id="imol_worktime_dayoff_rule" class="tel-set-inp tel-set-item-select">
										<?foreach($arResult["SELECT_RULES"] as $value=>$name):?>
											<option value="<?=$value?>" <?if($arResult["CONFIG"]["WORKTIME_DAYOFF_RULE"] == $value) { ?>selected<? }?> <?if($value == 'disabled') { ?>disabled<? }?>><?=$name?></option>
										<?endforeach?>
									</select>
								</td>
							</tr>

						</table>
						<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_worktime_dayoff_rule_form_form">
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_DAYOFF_FORM_ID")?>  &nbsp; &mdash; &nbsp;</div>
							<select class="tel-set-inp tel-set-item-select" name="CONFIG[WORKTIME_DAYOFF_FORM_ID]">
							</select>
						</div>
						<div class="tel-set-item-text tel-set-item-crm-rule" id="imol_worktime_dayoff_rule_form_text">
							<?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_DAYOFF_FORM_ID_NOTICE")?>
						</div>
						<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_worktime_dayoff_rule_text">
							<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WORKTIME_DAYOFF_TEXT")?>  &nbsp; &mdash; &nbsp;</div>
							<textarea class="tel-set-inp tel-set-item-textarea" name="CONFIG[WORKTIME_DAYOFF_TEXT]"><?=htmlspecialcharsbx($arResult["CONFIG"]["WORKTIME_DAYOFF_TEXT"])?></textarea>
						</div>
						<script type="text/javascript">
							BX.OpenLinesConfigEdit.toggleSelectFormText(
								BX('imol_worktime_dayoff_rule'),
								BX('imol_worktime_dayoff_rule_form_form'),
								BX('imol_worktime_dayoff_rule_form_text'),
								BX('imol_worktime_dayoff_rule_text')
							);
							BX.bind(BX('imol_worktime_dayoff_rule'), 'change', function(e){
								BX.OpenLinesConfigEdit.toggleSelectFormText(
									this,
									BX('imol_worktime_dayoff_rule_form_form'),
									BX('imol_worktime_dayoff_rule_form_text'),
									BX('imol_worktime_dayoff_rule_text')
								);
							});
						</script>
					</div>
				</div>
			</div>
		</div>
		<script>
			BX.ready(function(){
				BX.bind(BX('WORKTIME_ENABLE'), 'change', function(e){
					if (BX('WORKTIME_ENABLE').checked)
					{
						BX('imol_worktime').style.height = '464px';
						setTimeout(function(){BX('imol_worktime').style.height = 'auto';}, 500);
					}
					else
					{
						BX('imol_worktime').style.height = '464px';
						setTimeout(function(){BX('imol_worktime').style.height = '0';}, 100);
					}
				});
			});
		</script>
		<!-- //work time-->

		<div class="tel-set-cont-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_ACTIONS")?></div>
		<div class="tel-set-item">
			<div class="tel-set-item-num">
				<input type="checkbox" id="imol_welcome_message" name="CONFIG[WELCOME_MESSAGE]" <? if ($arResult['CONFIG']['WELCOME_MESSAGE'] == "Y") { ?>checked<? } ?> value="Y" class="tel-set-checkbox"/>
				<span class="tel-set-item-num-text"></span>
			</div>
			<div class="tel-set-item-cont-block">
				<label for="imol_welcome_message" class="tel-set-cont-item-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_MESSAGE")?></label> <span class="tel-context-help" data-text="<?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_MESSAGE_TIP")?>">?</span>
				<div class="tel-set-item-cont">
					<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_action_welcome">
						<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_WELCOME_MESSAGE_TEXT")?> &nbsp; &mdash; &nbsp;</div>
						<textarea class="tel-set-inp tel-set-item-textarea" name="CONFIG[WELCOME_MESSAGE_TEXT]"><?=htmlspecialcharsbx($arResult["CONFIG"]["WELCOME_MESSAGE_TEXT"])?></textarea>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				BX.OpenLinesConfigEdit.toggleCheckboxText(
					BX('imol_welcome_message'),
					BX('imol_action_welcome')
				);
				BX.bind(BX('imol_welcome_message'), 'change', function(e){
					BX.OpenLinesConfigEdit.toggleCheckboxText(
						this,
						BX('imol_action_welcome')
					);
				});
			</script>
		</div>
		<div class="tel-set-item">
			<div class="tel-set-item-num">
				<input type="checkbox" id="imol_agreement_message" name="CONFIG[AGREEMENT_MESSAGE]" <? if ($arResult['CONFIG']['AGREEMENT_MESSAGE'] == "Y") { ?>checked<? } ?> value="Y" class="tel-set-checkbox"/>
				<span class="tel-set-item-num-text"></span>
			</div>
			<div class="tel-set-item-cont-block">
				<label for="imol_agreement_message" class="tel-set-cont-item-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_AGREEMENT_MESSAGE")?></label>
				<div class="tel-set-item-cont">
					<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_agreement_message_block">
					<?$APPLICATION->IncludeComponent(
						"thurly:intranet.userconsent.selector",
						"",
						array(
							'ID' => $arResult['CONFIG']['AGREEMENT_ID'],
							'INPUT_NAME' => 'CONFIG[AGREEMENT_ID]'
						)
					);?>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				BX.OpenLinesConfigEdit.toggleCheckboxAgreement(
					BX('imol_agreement_message'),
					BX('imol_agreement_message_block')
				);
				BX.bind(BX('imol_agreement_message'), 'change', function(e){
					BX.OpenLinesConfigEdit.toggleCheckboxAgreement(
						this,
						BX('imol_agreement_message_block')
					);
				});
			</script>
		</div>
		<div class="tel-set-item">
			<div class="tel-set-item-num">
				<span class="tel-set-item-num-text"></span>
			</div>
			<div class="tel-set-item-cont-block">
				<label for="id1" class="tel-set-cont-item-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CLOSE_ACTION")?></label>
				<div class="tel-set-item-cont">
					<div class="tel-set-item-select-block">
						<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CLOSE_RULE")?>  &nbsp; &mdash; &nbsp;</div>
						<select class="tel-set-inp tel-set-item-select" name="CONFIG[CLOSE_RULE]" id="imol_action_close">
							<?foreach($arResult["CLOSE_RULES"] as $value=>$name):?>
								<option value="<?=$value?>" <?if($arResult["CONFIG"]["CLOSE_RULE"] == $value) { ?>selected<? }?> <?if($value == 'disabled') { ?>disabled<? }?>><?=$name?></option>
							<?endforeach?>
						</select>
					</div>
					<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_action_close_form">
						<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CLOSE_FORM_ID")?>  &nbsp; &mdash; &nbsp;</div>
						<select class="tel-set-inp tel-set-item-select" name="CONFIG[CLOSE_FORM_ID]">
						</select>
					</div>
					<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_action_close_text">
						<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_CLOSE_TEXT")?>  &nbsp; &mdash; &nbsp;</div>
						<textarea class="tel-set-inp tel-set-item-textarea" name="CONFIG[CLOSE_TEXT]"><?=htmlspecialcharsbx($arResult["CONFIG"]["CLOSE_TEXT"])?></textarea>
					</div>
					<script type="text/javascript">
						BX.OpenLinesConfigEdit.toggleSelectFormOrText(
							BX('imol_action_close'),
							BX('imol_action_close_form'),
							BX('imol_action_close_text')
						);
						BX.bind(BX('imol_action_close'), 'change', function(e){
							BX.OpenLinesConfigEdit.toggleSelectFormOrText(
								this,
								BX('imol_action_close_form'),
								BX('imol_action_close_text')
							);
						});
					</script>
				</div>
			</div>
		</div>
		<div class="tel-set-item">
			<div class="tel-set-item-num">
				<span class="tel-set-item-num-text"></span>
			</div>
			<div class="tel-set-item-cont-block">
				<label for="id1" class="tel-set-cont-item-title"><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_ACTION")?></label>
				<div id="imol_queue_time" class="tel-set-item-select-block tel-set-item-crm-rule" style="height: 55px">
					<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TIME")?>  &nbsp; &mdash; &nbsp;</div>
					<select class="tel-set-inp tel-set-item-select" name="CONFIG[AUTO_CLOSE_TIME]">
						<option value="3600" <?if($arResult["CONFIG"]["AUTO_CLOSE_TIME"] == "3600") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TIME_1_H")?></option>
						<option value="14400" <?if($arResult["CONFIG"]["AUTO_CLOSE_TIME"] == "14400") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TIME_4_H")?></option>
						<option value="28800" <?if($arResult["CONFIG"]["AUTO_CLOSE_TIME"] == "28800") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TIME_8_H")?></option>
						<option value="86400" <?if($arResult["CONFIG"]["AUTO_CLOSE_TIME"] == "86400") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TIME_1_D")?></option>
						<option value="172800" <?if($arResult["CONFIG"]["AUTO_CLOSE_TIME"] == "172800") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TIME_2_D")?></option>
						<option value="604800" <?if($arResult["CONFIG"]["AUTO_CLOSE_TIME"] == "604800") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TIME_1_W")?></option>
						<option value="2678400" <?if($arResult["CONFIG"]["AUTO_CLOSE_TIME"] == "2678400") { ?>selected<? }?>><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TIME_1_M")?></option>
					</select>
				</div>
				<div class="tel-set-item-cont">
					<div class="tel-set-item-select-block">
						<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_RULE")?>  &nbsp; &mdash; &nbsp;</div>
						<select class="tel-set-inp tel-set-item-select" name="CONFIG[AUTO_CLOSE_RULE]" id="imol_action_auto_close">
							<?foreach($arResult["CLOSE_RULES"] as $value=>$name):?>
								<option value="<?=$value?>" <?if($arResult["CONFIG"]["AUTO_CLOSE_RULE"] == $value) { ?>selected<? }?> <?if($value == 'disabled') { ?>disabled<? }?>><?=$name?></option>
							<?endforeach?>
						</select>
					</div>
					<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_action_auto_close_form">
						<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_FORM_ID")?>  &nbsp; &mdash; &nbsp;</div>
						<select class="tel-set-inp tel-set-item-select" name="CONFIG[AUTO_CLOSE_FORM_ID]">
						</select>
					</div>
					<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_action_auto_close_text">
						<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_AUTO_CLOSE_TEXT")?>  &nbsp; &mdash; &nbsp;</div>
						<textarea class="tel-set-inp tel-set-item-textarea" name="CONFIG[AUTO_CLOSE_TEXT]"><?=htmlspecialcharsbx($arResult["CONFIG"]["AUTO_CLOSE_TEXT"])?></textarea>
					</div>
					<script type="text/javascript">
						BX.OpenLinesConfigEdit.toggleSelectFormOrText(
							BX('imol_action_auto_close'),
							BX('imol_action_auto_close_form'),
							BX('imol_action_auto_close_text')
						);
						BX.bind(BX('imol_action_auto_close'), 'change', function(e){
							BX.OpenLinesConfigEdit.toggleSelectFormOrText(
								this,
								BX('imol_action_auto_close_form'),
								BX('imol_action_auto_close_text')
							);
						});
					</script>
				</div>
			</div>
		</div>
		<div class="tel-set-item">
			<div class="tel-set-item-num">
				<input type="checkbox" id="imol_vote_message" name="CONFIG[VOTE_MESSAGE]" <? if ($arResult['CONFIG']['VOTE_MESSAGE'] == "Y") { ?>checked<? } ?> value="Y" class="tel-set-checkbox"/>
				<span class="tel-set-item-num-text"></span>
			</div>
			<div class="tel-set-item-cont-block">
				<label for="imol_vote_message" class="tel-set-cont-item-title imol-vote-main-button"><?=Loc::getMessage("IMOL_CONFIG_EDIT_VOTE_MESSAGE")?></label>
				<?if (!\Thurly\Imopenlines\Limit::canUseVoteClient() || \Thurly\Imopenlines\Limit::isDemoLicense()):?>
				<span class="tel-lock-holder-select" title="<?=GetMessage("IMOL_CONFIG_LOCK_ALT")?>"><span onclick='imolOpenTrialPopup("imol_vote", "<?=CUtil::JSEscape(Loc::getMessage("IMOL_CONFIG_EDIT_POPUP_LIMITED_VOTE"))?>")' class="tel-lock <?=(\Thurly\Imopenlines\Limit::isDemoLicense()? 'tel-lock-half': '')?>"></span></span>
					<?if (!\Thurly\Imopenlines\Limit::canUseVoteClient()):?>
					<script type="text/javascript">
						BX.bind(BX('imol_vote_message'), 'change', function(e){
							BX('imol_vote_message').checked = false;
							imolOpenTrialPopup('imol_vote', "<?=CUtil::JSEscape(Loc::getMessage("IMOL_CONFIG_EDIT_POPUP_LIMITED_VOTE"))?>")
						});
					</script>
					<?endif;?>
				<?endif;?>
				<div class="tel-set-item-cont">
					<div class="tel-set-item-select-block tel-set-item-crm-rule" id="imol_vote_message_text">
						<div class="imol-vote-container">
							<div class="imol-vote-title"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_1_TITLE')?></div>
							<div class="imol-vote-inner">
								<div class="imol-vote-block">
									<div class="imol-vote-description"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_TEXT')?></div>
									<div class="imol-vote-content-border-element imol-vote-content-middle">
										<div class="imol-vote-content-element-block">
											<div class="imol-vote-content-element">
												<span class="imol-vote-text-container-input">
													<span class="imol-vote-text-container-input-text"><?=str_replace(array('[BR]', '[br]', '#BR#', "\n"), '<br/>', htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_1_TEXT']))?></span>
													<div class="imol-vote-content-button"onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'imol-vote-state');"><!--<span class="imol-vote-icon-pencil"></span>--><div class="imol-vote-content-button-item"><?=Loc::getMessage('IMOL_CONFIG_EDIT_BUTTON')?></div></div>
												</span>
											</div>
											<textarea class="imol-vote-content-text-control imol-vote-content-hidden-element imol-vote-content-border-element imol-vote-content-textarea imol-vote-content-textarea-small" name="CONFIG[VOTE_MESSAGE_1_TEXT]"><?=str_replace(array('[BR]', '[br]', '#BR#'), "\n", htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_1_TEXT']))?></textarea>
										</div>
										<div class="imol-vote-content-icon-block">
											<div class="imol-vote-content-icon imol-vote-content-icon-like-big"></div>
											<div class="imol-vote-content-icon imol-vote-content-icon-dislike-big"></div>
										</div>
									</div>
								</div>
								<div class="imol-vote-block-icon imol-vote-icon-like-small"></div>
								<div class="imol-vote-block">
									<div class="imol-vote-description"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_LIKE')?></div>
									<div class="imol-vote-content-border-element imol-vote-content-middle">
										<div class="imol-vote-content-element-block">
											<div class="imol-vote-content-element">
												<span class="imol-vote-text-container-input">
													<span class="imol-vote-text-container-input-text"><?=str_replace(array('[BR]', '[br]', '#BR#', "\n"), '<br/>', htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_1_LIKE']))?></span>
													<div class="imol-vote-content-button" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'imol-vote-state');"><!--<span class="imol-vote-icon-pencil"></span>--><div class="imol-vote-content-button-item"><?=Loc::getMessage('IMOL_CONFIG_EDIT_BUTTON')?></div></div>
												</span>
											</div>
											<textarea class="imol-vote-content-text-control imol-vote-content-hidden-element imol-vote-content-border-element imol-vote-content-textarea imol-vote-content-textarea-small" name="CONFIG[VOTE_MESSAGE_1_LIKE]"><?=str_replace(array('[BR]', '[br]', '#BR#'), "\n", htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_1_LIKE']))?></textarea>
										</div>
										<div class="imol-vote-content-icon imol-vote-content-icon-smile"></div>
									</div>
								</div>
								<div class="imol-vote-block-icon imol-vote-icon-dislike-small"></div>
								<div class="imol-vote-block">
									<div class="imol-vote-description"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_DISLIKE')?></div>
									<div class="imol-vote-content-border-element imol-vote-content-middle">
										<div class="imol-vote-content-element-block">
											<div class="imol-vote-content-element">
												<span class="imol-vote-text-container-input">
													<span class="imol-vote-text-container-input-text"><?=str_replace(array('[BR]', '[br]', '#BR#', "\n"), '<br/>', htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_1_DISLIKE']))?></span>
													<div class="imol-vote-content-button" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'imol-vote-state');"><!--<span class="imol-vote-icon-pencil"></span>--><div class="imol-vote-content-button-item"><?=Loc::getMessage('IMOL_CONFIG_EDIT_BUTTON')?></div></div>
												</span>
											</div>
											<textarea class="imol-vote-content-text-control imol-vote-content-hidden-element imol-vote-content-border-element imol-vote-content-textarea imol-vote-content-textarea-small" name="CONFIG[VOTE_MESSAGE_1_DISLIKE]"><?=str_replace(array('[BR]', '[br]', '#BR#'), "\n", htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_1_DISLIKE']))?></textarea>
										</div>
										<div class="imol-vote-content-icon imol-vote-content-icon-sad"></div>
									</div>
								</div>
							</div>
							<div class="imol-vote-important-info"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_1_DESC')?></div>
						</div>

						<div class="imol-vote-container">
							<div class="imol-vote-title"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_2_TITLE')?></div>
							<div class="imol-vote-inner">
								<div class="imol-vote-block">
									<div class="imol-vote-description"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_TEXT')?></div>
									<div class="imol-vote-content-border-element imol-vote-content-small">
										<div class="imol-vote-content-element">
											<span class="imol-vote-text-container">
												<span class="imol-vote-text-container-input"><?=str_replace(array('[BR]', '[br]', '#BR#', "\n"), '<br/>', htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_2_TEXT']))?></span>
											</span>
											<div class="imol-vote-content-button" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'imol-vote-state');"><!--<span class="imol-vote-icon-pencil"></span>--><div class="imol-vote-content-button-item"><?=Loc::getMessage('IMOL_CONFIG_EDIT_BUTTON')?></div></div>
										</div>
										<textarea class="imol-vote-content-text-control imol-vote-content-hidden-element imol-vote-content-border-element imol-vote-content-textarea" name="CONFIG[VOTE_MESSAGE_2_TEXT]"><?=str_replace(array('[BR]', '[br]', '#BR#'), "\n", htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_2_TEXT']))?></textarea>
									</div>
								</div>
								<div class="imol-vote-block-icon imol-vote-icon-like-small"></div>
								<div class="imol-vote-block">
									<div class="imol-vote-description"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_LIKE')?></div>
									<div class="imol-vote-content-border-element imol-vote-content-small">
										<div class="imol-vote-content-element">
											<span class="imol-vote-text-container">
												<span class="imol-vote-text-container-input"><?=str_replace(array('[BR]', '[br]', '#BR#', "\n"), '<br/>', htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_2_LIKE']))?></span>
											</span>
											<div class="imol-vote-content-button" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'imol-vote-state');"><!--<span class="imol-vote-icon-pencil"></span>--><div class="imol-vote-content-button-item"><?=Loc::getMessage('IMOL_CONFIG_EDIT_BUTTON')?></div></div>
										</div>
										<textarea class="imol-vote-content-text-control imol-vote-content-hidden-element imol-vote-content-border-element imol-vote-content-textarea" name="CONFIG[VOTE_MESSAGE_2_LIKE]"><?=str_replace(array('[BR]', '[br]', '#BR#'), "\n", htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_2_LIKE']))?></textarea>
									</div>
								</div>
								<div class="imol-vote-block-icon imol-vote-icon-dislike-small"></div>
								<div class="imol-vote-block">
									<div class="imol-vote-description"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_DISLIKE')?></div>
									<div class="imol-vote-content-border-element imol-vote-content-small">
										<div class="imol-vote-content-element">
											<span class="imol-vote-text-container">
												<span class="imol-vote-text-container-input"><?=str_replace(array('[BR]', '[br]', '#BR#', "\n"), '<br/>', htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_2_DISLIKE']))?></span>
											</span>
											<div class="imol-vote-content-button" onclick="BX.toggleClass(this.parentNode.parentNode.parentNode, 'imol-vote-state');"><!--<span class="imol-vote-icon-pencil"></span>--><div class="imol-vote-content-button-item"><?=Loc::getMessage('IMOL_CONFIG_EDIT_BUTTON')?></div></div>
										</div>
										<textarea class="imol-vote-content-text-control imol-vote-content-hidden-element imol-vote-content-border-element imol-vote-content-textarea" name="CONFIG[VOTE_MESSAGE_2_DISLIKE]"><?=str_replace(array('[BR]', '[br]', '#BR#'), "\n", htmlspecialcharsbx($arResult['CONFIG']['VOTE_MESSAGE_2_DISLIKE']))?></textarea>
									</div>
								</div>
							</div>
							<div class="imol-vote-important-info"><?=Loc::getMessage('IMOL_CONFIG_EDIT_VOTE_MESSAGE_2_DESC')?></div>
						</div>

					</div>
				</div>
			</div>
			<script type="text/javascript">
				BX.OpenLinesConfigEdit.toggleCheckboxVote(
					BX('imol_vote_message'),
					BX('imol_vote_message_text')
				);
				BX.bind(BX('imol_vote_message'), 'change', function(e){
					BX.OpenLinesConfigEdit.toggleCheckboxVote(
						this,
						BX('imol_vote_message_text')
					);
				});
			</script>
		</div>
		<div class="tel-set-cont-title"><?=Loc::getMessage('IMOL_CONFIG_EDIT_LANG')?></div>
		<div class="tel-set-item">
			<div class="tel-set-item-num">
				<span class="tel-set-item-num-text"></span>
			</div>
			<div class="tel-set-item-cont-block">
				<div id="imol_queue_time" class="tel-set-item-select-block tel-set-item-crm-rule" style="height: 55px">
					<div class="tel-set-item-select-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_LANG_EMAIL")?>  &nbsp; &mdash; &nbsp;</div>
					<select class="tel-set-inp tel-set-item-select" name="CONFIG[LANGUAGE_ID]">
						<?foreach($arResult['LANGUAGE_LIST'] as $lang => $langText):?>
						<option value="<?=$lang?>" <?if($arResult["CONFIG"]["LANGUAGE_ID"] == $lang) { ?>selected<? }?>><?=$langText?></option>
						<?endforeach;?>
					</select>
					<span class="tel-context-help" data-text="<?=htmlspecialcharsbx(Loc::getMessage("IMOL_CONFIG_EDIT_LANG_EMAIL_TIP"))?>">?</span>
				</div>
			</div>
		</div>
		</form>
		<?if ($arResult['CAN_EDIT_CONNECTOR']):?>
			<? $APPLICATION->IncludeComponent("thurly:imconnector.settings", "", Array("LINE" => $arResult['CONFIG']['ID'])); ?>
		<?else:?>
			<div class="tel-set-item tel-set-item-border"></div>
		<?endif?>
		<div class="tel-set-footer-btn">
			<?if ($arResult['CAN_EDIT']):?>
			<span class="webform-button webform-button-accept" onclick="BX.submit(BX('imol_config_edit_form'))">
				<span class="webform-button-left"></span><span class="webform-button-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_SAVE")?></span><span class="webform-button-right"></span>
			</span>
			<span class="webform-button" onclick="BX('imol_config_edit_form_action').value = 'apply';BX.submit(BX('imol_config_edit_form'))">
				<span class="webform-button-left"></span><span class="webform-button-text"><?=Loc::getMessage("IMOL_CONFIG_EDIT_APPLY")?></span><span class="webform-button-right"></span>
			</span>
			<?endif?>
			<a href="<?=$arResult['PATH_TO_LIST']?>" class="webform-small-button-link"><?=Loc::getMessage("IMOL_CONFIG_EDIT_BACK")?></a>
		</div>
	</div>
</div>