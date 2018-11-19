<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

Thurly\Main\Page\Asset::getInstance()->addJs('/thurly/js/crm/category.js');

if (!empty($arResult['BUTTONS']))
{
	$type = $arParams['TYPE'];
	$template = 'type2';
	if($type === 'list')
	{
		$template = SITE_TEMPLATE_ID === 'thurlyos' ? 'title' : '';
	}
	else if($type === 'details')
	{
		$template = SITE_TEMPLATE_ID === 'thurlyos' ? 'slider' : 'type2';
	}

	$APPLICATION->IncludeComponent(
		'thurly:crm.interface.toolbar',
		$template,
		array(
			'TOOLBAR_ID' => $arResult['TOOLBAR_ID'],
			'BUTTONS' => $arResult['BUTTONS']
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
}

if(isset($arResult['SONET_SUBSCRIBE']) && is_array($arResult['SONET_SUBSCRIBE'])):
	$subscribe = $arResult['SONET_SUBSCRIBE'];
?><script type="text/javascript">
BX.ready(
	function()
	{
		BX.CrmSonetSubscription.create(
			"<?=CUtil::JSEscape($subscribe['ID'])?>",
			{
				"entityType": "<?=CCrmOwnerType::DealName?>",
				"serviceUrl": "<?=CUtil::JSEscape($subscribe['SERVICE_URL'])?>",
				"actionName": "<?=CUtil::JSEscape($subscribe['ACTION_NAME'])?>"
			}
		);
	}
);
</script><?
endif;

if(isset($arResult['CATEGORY_SELECTOR']) && is_array($arResult['CATEGORY_SELECTOR'])):
	$categorySelector = $arResult['CATEGORY_SELECTOR'];
?><script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmDealCategory.infos = <?=CUtil::PhpToJSObject($categorySelector['INFOS'])?>;
			BX.CrmDealCategorySelector.messages =
			{
				"create": "<?=CUtil::JSEscape($categorySelector['MESSAGES']['CREATE'])?>"
			};

			BX.CrmDealCategorySelector.create(
				"<?=CUtil::JSEscape($categorySelector['ID'])?>",
				{
					"createUrl": "<?=CUtil::JSEscape($categorySelector['CREATE_URL'])?>",
					"categoryListUrl": "<?=CUtil::JSEscape($categorySelector['CATEGORY_LIST_URL'])?>",
					"categoryCreateUrl": "<?=CUtil::JSEscape($categorySelector['CATEGORY_CREATE_URL'])?>",
					"canCreateCategory": <?=$categorySelector['CAN_CREATE_CATEGORY'] ? 'true' : 'false'?>
				}
			);
		}
	);
</script><?
endif;

if(isset($arResult['CATEGORY_CHANGER'])):
	$categoryChanger = $arResult['CATEGORY_CHANGER'];
?><script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmDealCategory.infos = <?=CUtil::PhpToJSObject(
				\Thurly\Crm\Category\DealCategory::getJavaScriptInfos()
			)?>;

			BX.CrmDealCategorySelectDialog.messages =
			{
				title: "<?=GetMessageJS('CRM_DEAL_CATEGORY_SELECT_DLG_TITLE')?>",
				field: "<?=GetMessageJS('CRM_DEAL_CATEGORY_SELECT_DLG_FIELD')?>",
				saveButton: "<?=GetMessageJS('CRM_BUTTON_SAVE')?>",
				cancelButton: "<?=GetMessageJS('CRM_BUTTON_CANCEL')?>"
			};

			BX.Crm.DealCategoryChanger.create(
				"<?=CUtil::JSEscape($categoryChanger['ID'])?>",
				{
					entityId: <?=$categoryChanger['ENTITY_ID']?>,
					categoryIds: <?=CUtil::PhpToJSObject($categoryChanger['CATEGORY_IDS'])?>,
					serviceUrl: "<?=CUtil::JSEscape($categoryChanger['SERVICE_URL'])?>",
					action: "<?=CUtil::JSEscape($categoryChanger['ACTION_NAME'])?>"
				}
			);

			BX.Crm.DealCategoryChanger.messages =
			{
				dialogTitle: "<?=GetMessageJS('CRM_DEAL_MOVE_TO_CATEGORY_DLG_TITLE')?>",
				dialogSummary: "<?=GetMessageJS('CRM_DEAL_MOVE_TO_CATEGORY_DLG_SUMMARY')?>"
			};
		}
	);
</script><?
endif;