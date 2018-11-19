<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'pagetitle-toolbar-field-view tasks-pagetitle-view');

$isThurlyOSTemplate = SITE_TEMPLATE_ID === "thurlyos";

if ($isThurlyOSTemplate)
{
	$this->SetViewTarget("below_pagetitle");
}

$showViewMode = $arParams['SHOW_VIEW_MODE'] == 'Y';
?>

<? if (!$isThurlyOSTemplate):?>
<div class="tasks-interface-toolbar-container">
<? endif ?>
	<div id="counter_panel_container" class="tasks-counter">
		<div class="tasks-counter-title" id="<?=$arResult['HELPER']->getScopeId()?>"></div>
	</div>
	
<?php if($showViewMode):?>
<div class="tasks-view-switcher pagetitle-align-right-container">
    <div class="tasks-view-switcher-list">
        <?php
        $template = $arParams['GROUP_ID'] > 0 ? 'PATH_TO_GROUP_TASKS' : 'PATH_TO_USER_TASKS';
        $link = CComponentEngine::makePathFromTemplate($template, array('user_id'=>$arParams['USER_ID'], 'group_id'=>$arParams['GROUP_ID']));
        foreach($arResult['VIEW_LIST'] as $viewKey => $view):
			if($viewKey == 'VIEW_MODE_KANBAN' && (int)$arParams['GROUP_ID'] == 0)
			{
				continue;
			}

	        $active = array_key_exists('SELECTED', $view) && $view['SELECTED'] == 'Y';

	    $state = \Thurly\Tasks\Ui\Filter\Task::getListStateInstance()->getState();
//	    if(!empty($state['SPECIAL_PRESET_SELECTED']) && $state['SPECIAL_PRESET_SELECTED']['ID'] == -10) // favorite
//        {
//	        $url = '?F_STATE[]=sV' . CTaskListState::encodeState($view['ID']).'&F_CANCEL=Y&F_FILTER_SWITCH_PRESET=-10&F_STATE[]=sCb0000';
//        }
//        else
//        {
			$url = '?F_STATE=sV'.CTaskListState::encodeState($view['ID']);
//        }
        ?>
        <a href="<?=$url?>" id="tasks_<?=strtolower($viewKey)?>"   class="tasks-view-switcher-list-item <?=$active ? 'tasks-view-switcher-list-item-active' : '';?>"><?=$view['SHORT_TITLE']?></a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif?>

<? if (!$isThurlyOSTemplate):?>
	</div>
<? endif ?>

<?if($isThurlyOSTemplate)
{
    $this->EndViewTarget();
}
?>

<div style="<?=$state['VIEW_SELECTED']['CODENAME'] == 'VIEW_MODE_GANTT' ? 'margin:-15px -15px 15px  -15px' : ''?>">
    <?=\Thurly\Main\Update\Stepper::getHtml("tasks");?>
</div>

<?$arResult['HELPER']->initializeExtension();?>