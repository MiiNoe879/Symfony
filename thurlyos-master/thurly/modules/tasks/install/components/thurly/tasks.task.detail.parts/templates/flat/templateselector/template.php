<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); 

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$templateId = $arResult['TEMPLATE_DATA']['ID'];
$templates = $arResult['TEMPLATE_DATA']['DATA']['TEMPLATES'];

CJSCore::Init('tasks_style_legacy');
?>

<div id="bx-component-scope-<?=$templateId?>" class="task-template-selector">

	<?$hasButton = $arParams['TEMPLATE_DATA']['BUTTON_LABEL'] != '';?>

	<span data-bx-id="templateselector-open" class="webform-small-button webform-small-button-transparent webform-small-button-dropdown" title="<?=Loc::getMessage('TASKS_TTDP_TEMPLATESELECTOR_CREATE_HINT')?>">
		<?if($hasButton):?>
			<span class="webform-small-button-text"><?=htmlspecialcharsbx($arParams['TEMPLATE_DATA']['BUTTON_LABEL'])?></span>
		<?endif?>
		<span class="webform-small-button-icon"></span>
	</span>

</div>

<script>
	new BX.Tasks.Component.TaskDetailPartsTemplateSelector(<?=CUtil::PhpToJSObject(array(
		'id' => $templateId,
		'menuItems' => $arResult['MENU_ITEMS'],
		'toTemplates' => CComponentEngine::MakePathFromTemplate($arParams['TEMPLATE_DATA']["PATH_TO_TASKS_TEMPLATES"], array()),
		'useSlider' => $arParams['TEMPLATE_DATA']['USE_SLIDER'] != 'N',
		'commonUrl' => $arResult['COMMON_URL'],
	), false, false, true)?>);
</script>