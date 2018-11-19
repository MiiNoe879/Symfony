<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

Thurly\Main\Page\Asset::getInstance()->addJs('/thurly/js/main/utils.js');

if (!empty($arResult['BUTTONS']))
{
	$type = $arParams['TYPE'];
	$APPLICATION->IncludeComponent(
		'thurly:crm.interface.toolbar',
		$type === 'list' ?  (SITE_TEMPLATE_ID === 'thurlyos' ? 'title' : '') : 'type2',
		array(
			'TOOLBAR_ID' => 'crm_invoice_toolbar',
			'BUTTONS' => $arResult['BUTTONS']
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
}

