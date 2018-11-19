<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use \Thurly\Main\Localization\Loc;
?>
<div class="imconnector-settings-message imconnector-settings-message-align-left imconnector-settings-message-success">
	<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FINAL_FORM_DESCRIPTION_OK_1')?>
</div>
<div class="imconnector-step-text">
	<?=Loc::getMessage('IMCONNECTOR_COMPONENT_FINAL_FORM_DESCRIPTION_OK_2')?>
</div>

<?if(!empty($arResult["INFO_CONNECTION"])):?>
	<div class="imconnector-social-connected">
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_SKYPE"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_SKYPE"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_SKYPE')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-skype"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_SLACK"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_SLACK"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_SLACK')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-slack"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_KIK"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_KIK"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_KIK')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-kik"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_GROUPME"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_GROUPME"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_GROUPME')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-groupme"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_SMS"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_SMS"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_TWILIO')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-twilio"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_MSTEAMS"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_MSTEAMS"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_MSTEAMS')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-msteams"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_WEBCHAT"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_WEBCHAT"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_WEBCHAT')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-webchat"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_EMAIL"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_EMAIL"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_EMAILOFFICE365')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-emailoffice365"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_TELEGRAM"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_TELEGRAM"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_TELEGRAM')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-telegram"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_FACEBOOK"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_FACEBOOK"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_FACEBOOKMESSENGER')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-facebookmessenger"></div></a>
		<?endif;?>
		<?if(!empty($arResult["INFO_CONNECTION"]["URL_DIRECTLINE"])):?>
			<a href="<?=$arResult["INFO_CONNECTION"]["URL_DIRECTLINE"]?>" target="_blank"
			   title="<?=Loc::getMessage('IMCONNECTOR_NAME_CONNECTOR_BOTFRAMEWORK_DIRECTLINE')?>"><div class="connector-icon connector-icon-30 connector-icon-square connector-icon-botframework-directline"></div></a>
		<?endif;?>
	</div>
<?endif;?>
