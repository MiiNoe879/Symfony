<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Thurly\Main\Localization\Loc;
use \Thurly\Tasks\Ui\Filter;

// rebuild templates url
if (!empty($arResult['TEMPLATES_LIST']))
{
	$templates = array();
	$tplUrl = $arParams['~PATH_TO_TASKS_TASK'] . '?TEMPLATE_ID=#TPL_ID#';
	$tplUrl = str_replace(
				array('#action#', '#task_id#'),
				array('edit', 0),
				$tplUrl
			);
	foreach ($arResult['TEMPLATES_LIST'] as $template)
	{
		$templates[] = array(
			'ID' => $template['ID'],
			'TITLE' => $template['TITLE'],
			'HREF' => str_replace('#TPL_ID#', $template['ID'], $tplUrl)
		);
	}

	$templates[] = array(
		'ID' => 0,
		'TITLE' => Loc::getMessage('KANBAN_TEMPLATE_LIST'),
		'HREF' => $arParams['~PATH_TO_TEMPLATES']
	);

	$arResult['TEMPLATES_LIST'] = $templates;
}

// selected items of menu
if (isset($arParams['INCLUDE_INTERFACE_HEADER']) && $arParams['INCLUDE_INTERFACE_HEADER'] == 'Y')
{
	$arParams['MARK_ACTIVE_ROLE'] = 'Y';
	$arParams['MARK_SECTION_ALL'] = 'N';

	$state = Filter\Task::getListStateInstance()->getState();

	if (
		isset($state['SECTION_SELECTED']['CODENAME']) &&
		$state['SECTION_SELECTED']['CODENAME'] == 'VIEW_SECTION_ADVANCED_FILTER'
	)
	{
		$arParams['MARK_SECTION_ALL'] = 'Y';
		$arParams['MARK_ACTIVE_ROLE'] = 'N';
	}

	if (isset($state['SPECIAL_PRESETS']) && is_array($state['SPECIAL_PRESETS']))
	{
		foreach ($state['SPECIAL_PRESETS'] as $preset)
		{
			if ($preset[ 'SELECTED' ] == 'Y')
			{
				$arParams['MARK_SPECIAL_PRESET'] = 'Y';
				$arParams['MARK_SECTION_ALL'] = 'N';
				$arParams['MARK_ACTIVE_ROLE'] = 'N';
				break;
            }
		}
	}
}