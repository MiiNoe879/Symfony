<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use \Thurly\Crm\Kanban\Helper;

\Thurly\Main\Page\Asset::getInstance()->addJs('/thurly/js/crm/interface_grid.js');

$filter = Helper::getFilter($arParams['ENTITY_TYPE']);
$presets = Helper::getPresets($arParams['ENTITY_TYPE']);
$grid = Helper::getGrid($arParams['ENTITY_TYPE']);
$gridId = Helper::getGridId($arParams['ENTITY_TYPE']);
$gridFilter = (array)$grid->GetFilter($filter);

$APPLICATION->IncludeComponent(
	'thurly:crm.interface.filter',
	'title',
	array(
		'GRID_ID' => $gridId,
		'FILTER_ID' => $gridId,
		'FILTER' => $filter,
		'FILTER_FIELDS' => $gridFilter,
		'FILTER_PRESETS' => $presets,
		'ENABLE_LIVE_SEARCH' => true,
		'NAVIGATION_BAR' => $arParams['NAVIGATION_BAR']
	),
	$component,
	array('HIDE_ICONS' => true)
);