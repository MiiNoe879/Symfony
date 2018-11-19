<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$defaultMenuTarget = SITE_TEMPLATE_ID === "thurlyos" ? "above_pagetitle" : "task_menu";

\CJSCore::init("spotlight");

if(SITE_TEMPLATE_ID === "thurlyos")
{
	$this->SetViewTarget($defaultMenuTarget, 200);
}

$menuId = intval($arParams["GROUP_ID"]) ? "tasks_panel_menu_group" : "tasks_panel_menu";

if((int)$arParams["GROUP_ID"] == 0 && $arParams['USER_ID'] == $arParams['LOGGED_USER_ID'])
{
	?>

	<div class="" id="<?=$arResult['HELPER']->getScopeId()?>">
	<?
	$APPLICATION->IncludeComponent(
		'thurly:main.interface.buttons',
		'',
		array(
			'ID' => $menuId,
			'ITEMS' => $arResult['ITEMS'],
			'DISABLE_SETTINGS' => $arParams["USER_ID"] !== \Thurly\Tasks\Util\User::getId()
		),
		$component,
		array('HIDE_ICONS' => true)
	);
	?></div><?
}

if(SITE_TEMPLATE_ID === "thurlyos")
{
	$this->EndViewTarget();
}


$arResult['HELPER']->initializeExtension();