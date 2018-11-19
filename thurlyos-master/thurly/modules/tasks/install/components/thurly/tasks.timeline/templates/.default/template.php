<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!empty($arResult['ERRORS']))
{
	ShowError(implode("\n", $arResult['ERRORS']));
	return;
}

use \Thurly\Main\Localization\Loc;
use \Thurly\Tasks\UI\Filter;

Loc::loadMessages(__FILE__);

$data = $arResult['DATA'];
\CJSCore::Init('task_kanban_timeline');

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-all-paddings');

if (isset($arParams['INCLUDE_INTERFACE_HEADER']) && $arParams['INCLUDE_INTERFACE_HEADER'] == 'Y')
{
	$filter = Filter\Task::getFilters();
	$presets = Filter\Task::getPresets();
	$gridID = Filter\Task::getFilterId();

	$APPLICATION->IncludeComponent(
		'thurly:tasks.interface.header',
		'',
		array(
			'FILTER_ID' => $gridID,
			'GRID_ID' => $gridID,

			'FILTER' => $filter,
			'PRESETS' => $presets,

			'USER_ID' => $arParams['USER_ID'],
			'GROUP_ID' => $arParams['GROUP_ID'],
			'TEMPLATES_LIST' => $arResult['TEMPLATES_LIST'],

			'MARK_ACTIVE_ROLE' => $arParams['MARK_ACTIVE_ROLE'],
			'MARK_SECTION_ALL' => $arParams['MARK_SECTION_ALL'],
			'MARK_SPECIAL_PRESET' => $arParams['MARK_SPECIAL_PRESET'],

			'PATH_TO_USER_TASKS' => $arParams['~PATH_TO_USER_TASKS'],
			'PATH_TO_USER_TASKS_TASK' => $arParams['~PATH_TO_USER_TASKS_TASK'],
			'PATH_TO_USER_TASKS_TEMPLATES' => $arParams['~PATH_TO_USER_TASKS_TEMPLATES'],
			'PATH_TO_USER_TASKS_VIEW' =>
				isset($arParams['PATH_TO_USER_TASKS_VIEW'])
				? $arParams['PATH_TO_USER_TASKS_VIEW'] : '',
			'PATH_TO_USER_TASKS_REPORT' =>
				isset($arParams['PATH_TO_USER_TASKS_REPORT'])
				? $arParams['PATH_TO_USER_TASKS_REPORT'] : '',
			'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' =>
				isset($arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'])
				? $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'] : '',

			'PATH_TO_GROUP_TASKS_TASK' => $arParams['~PATH_TO_GROUP_TASKS_TASK'],
			'PATH_TO_GROUP_TASKS' => $arParams['~PATH_TO_GROUP_TASKS'],
			'PATH_TO_GROUP' =>
				isset($arParams['PATH_TO_GROUP'])
				? $arParams['PATH_TO_GROUP'] : '',
			'PATH_TO_GROUP_TASKS_VIEW' =>
				isset($arParams['PATH_TO_GROUP_TASKS_VIEW'])
				? $arParams['PATH_TO_GROUP_TASKS_VIEW'] : '',
			'PATH_TO_GROUP_TASKS_REPORT' =>
				isset($arParams['PATH_TO_GROUP_TASKS_REPORT'])
				? $arParams['PATH_TO_GROUP_TASKS_REPORT'] : '',

			'PATH_TO_USER_PROFILE' => $arParams['~PATH_TO_USER_PROFILE'],
			'PATH_TO_MESSAGES_CHAT' =>
				isset($arParams['PATH_TO_MESSAGES_CHAT'])
				? $arParams['PATH_TO_MESSAGES_CHAT'] : '',
			'PATH_TO_VIDEO_CALL' =>
				isset($arParams['PATH_TO_VIDEO_CALL'])
				? $arParams['PATH_TO_VIDEO_CALL'] : '',
			'PATH_TO_CONPANY_DEPARTMENT' =>
				isset($arParams['PATH_TO_CONPANY_DEPARTMENT'])
				? $arParams['PATH_TO_CONPANY_DEPARTMENT'] : '',

			'USE_GROUP_SELECTOR' => 'N',
			'SHOW_QUICK_FORM'=>'N',
			'USE_EXPORT'=>'N'
		),
		$component,
		array('HIDE_ICONS' => true)
	);
}
?>



<div id="task_timeline"></div>

<script type="text/javascript">
(function() {

	"use strict";

	var grid = new BX.Tasks.Timeline.Grid({
		renderTo: BX("task_timeline"),
		itemType: "BX.Tasks.Timeline.Item",
		canAddColumn: false,
		canEditColumn: false,
		canRemoveColumn: false,
		canAddItem: true,
		bgColor: <?= (SITE_TEMPLATE_ID === "thurlyos" ? '"transparent"' : "null")?>,
		columns: <?= \CUtil::PhpToJSObject($data['columns'], false, false, true)?>,
		items: <?= \CUtil::PhpToJSObject($data['items'], false, false, true)?>,
		data: {
			ajaxHandlerPath: "<?= $this->GetComponent()->getPath()?>/ajax.php",
			pathToTask: "<?= \CUtil::JSEscape(str_replace('#action#', 'view', $arParams['~PATH_TO_TASKS_TASK']))?>",
			pathToUser: "<?= \CUtil::JSEscape($arParams['~PATH_TO_USER_PROFILE'])?>",
			columnIdComplete: "COMPLETE",
			columnIdOverdue: "OVERDUE",
			params: {
				USER_ID: "<?= $arParams['USER_ID']?>",
				GROUP_ID: "<?= $arParams['GROUP_ID']?>",
				GROUP_ID_CODE: "GROUP_ID"
			}
		}
	});

	grid.draw();

})();
</script>
