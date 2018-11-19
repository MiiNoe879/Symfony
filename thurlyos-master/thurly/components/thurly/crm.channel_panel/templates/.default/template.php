<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
 * Thurly vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CCrmChannelPanelComponent $component
 */

use Thurly\Main\Localization\Loc;

if($component->hasItems())
{
	$APPLICATION->IncludeComponent(
		'thurly:crm.carousel',
		'',
		array(
			'GUID' => $component->getGuid(),
			'AUTO_REWIND' => $component->isAutoRewindEnabled(),
			'ENABLE_CLOSE_BUTTON' => true,
			'CLOSE_TITLE' => Loc::getMessage('CRM_CHANNEL_PANEL_CLOSE_TITLE'),
			'CLOSE_CONFIRM' => Loc::getMessage('CRM_CHANNEL_PANEL_CLOSE_CONFIRM'),
			'DEFAULT_BUTTON_TEXT' => Loc::getMessage('CRM_CHANNEL_PANEL_CONNECT_BUTTON'),
			'ITEMS' => $component->getItems()
		)
	);
?><script type="text/javascript">
	BX.ready(
		function()
		{
			var carouselId = "<?=CUtil::JSEscape($component->getGuid())?>";
			BX.addCustomEvent(
				window,
				"ON_CAROUSEL_CLOSE",
				function(sender, eventArgs)
				{
					if(sender.getId() === carouselId)
					{
						BX.ajax(
							{
								url: "/thurly/components/thurly/crm.channel_panel/ajax.php?<?=thurly_sessid_get()?>",
								method: "POST",
								dataType: "json",
								data:
								{ "ACTION" : "MARK_AS_ENABLED", "GUID" : carouselId, "ENABLED" : "N" }
							}
						);
					}
				}
			);
		}
	);
</script><?
}
