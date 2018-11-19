<?
use \Thurly\Tasks\Util;
use \Thurly\Tasks\UI;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arResult['HELPER'] = $helper = new \Thurly\Tasks\UI\Component\TemplateHelper('TasksWidgetButtonsTask', $this, array(
	'RELATION' => array(
		'tasks_util',
		'popup',
		'tasks_util_widget',
		'tasks_dayplan',
	),
));
$arParams =& $helper->getComponent()->arParams; // make $arParams the same variable as $this->__component->arParams, as it really should be

$can =& $arParams["TASK"]["ACTION"];
$data =& $arParams["TASK"];
$taskId = intval($arParams["TASK"]["ID"]);

$data["TIME_ESTIMATE"] = intval($data["TIME_ESTIMATE"]);
$data["TIME_ELAPSED"] = intval($data["TIME_ELAPSED"]);

$this->__component->tryParseBooleanParameter($arParams["REDIRECT_TO_LIST_ON_DELETE"], true);

// urls
$arResult['VIEW_URL'] = UI\Task::makeActionUrl($arParams["PATH_TO_TASKS_TASK"], $taskId, 'view');
$arResult['EDIT_URL'] = UI\Task::makeActionUrl($arParams["PATH_TO_TASKS_TASK"], $taskId, 'edit');
$arResult['COPY_URL'] = UI\Task::makeActionUrl($arParams["PATH_TO_TASKS_TASK_COPY"], 0, 'edit');
$arResult['CREATE_SUBTASK_URL'] = UI\Task::makeActionUrl($arParams["PATH_TO_TASKS_TASK_CREATE_SUBTASK"], 0, 'edit');

$arResult['EDIT_URL'] = Util::replaceUrlParameters($arResult['EDIT_URL'], array(
	'BACKURL' => $arResult['VIEW_URL'],
	'SOURCE' => 'view',
), array(), array('encode' => true));
$arResult['COPY_URL'] = Util::replaceUrlParameters($arResult['COPY_URL'], array(
	//'BACKURL' => $arResult['VIEW_URL'],
	'SOURCE' => 'view',
), array(), array('encode' => true));
$arResult['CREATE_SUBTASK_URL'] = Util::replaceUrlParameters($arResult['CREATE_SUBTASK_URL'], array(
	//'BACKURL' => $arResult['VIEW_URL'],
	'SOURCE' => 'view',
), array(), array('encode' => true));

$classes = array();
if($can["DAYPLAN.TIMER.TOGGLE"])
{
	$classes[] = 'timer-visible';
	$classes[] = 'timer-'.($data["TIMER_IS_RUNNING_FOR_CURRENT_USER"] ? 'pause' : 'start');
}
else
{
	if ($data['ACTION']['PAUSE'])
	{
		$classes[] = 'pause';
	}
	elseif ($data['ACTION']['START'])
	{
		$classes[] = 'start';
	}
}

if ($can["COMPLETE"])
{
	$classes[] = 'complete';
}

if ($can["APPROVE"])
{
	$classes[] = 'approve';
}

if ($can["DISAPPROVE"])
{
	$classes[] = 'disapprove';
}

if ($can["EDIT"] && !$arParams["PUBLIC_MODE"])
{
	$classes[] = 'edit';
}

if ($data["TIME_ESTIMATE"] > 0 && $data["TIME_ELAPSED"] > $data["TIME_ESTIMATE"])
{
	$classes[] = 'timer-overtime';
}

if ($data['TIMER_IS_RUNNING_FOR_CURRENT_USER'])
{
	$classes[] = 'timer-running';
}

if (!$arParams["PUBLIC_MODE"] || $can["RENEW"])
{
	$classes[] = 'more-button';
}

$arResult['CLASSES'] = $classes;

$arResult['JS_DATA'] = array(
	'can' => $can,
	'taskId' => $taskId,
	'publicMode' => $arParams["PUBLIC_MODE"],
	'data' => array(
		'TIME_ESTIMATE' => $taskData['TIME_ESTIMATE'],
		'TIME_ELAPSED' => $taskData['TIME_ELAPSED'],
		'TIMER_IS_RUNNING_FOR_CURRENT_USER' => $taskData['TIMER_IS_RUNNING_FOR_CURRENT_USER']
	),
	'copyUrl' => $arResult['COPY_URL'],
	'createSubtaskUrl' => $arResult['CREATE_SUBTASK_URL'],
	'listUrl' => $arParams["PATH_TO_TASKS"],
	'goToListOnDelete' => $arParams["REDIRECT_TO_LIST_ON_DELETE"],
);