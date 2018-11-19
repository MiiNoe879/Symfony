<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Thurly\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CThurlyComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var \Thurly\Disk\Internals\BaseComponent $component */

$arButtons = array();
$arButtons[] = array(
	"TEXT"  => Loc::getMessage("DISK_BIZPROC_BACK_TEXT"),
	"TITLE" => Loc::getMessage("DISK_BIZPROC_BACK_TITLE"),
	"LINK"  => CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_FOLDER_LIST"], array("PATH" => "")),
	"ICON"  => "back");

$APPLICATION->includeComponent(
	'thurly:disk.interface.toolbar',
	'',
	array(
		'TOOLBAR_ID' => 'bp_toolbar',
		'CLASS_NAME' => 'bx-filepage',
		'BUTTONS'    => $arButtons,
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);
?>
<div class="bx-disk-bizproc-section">
<?
$APPLICATION->IncludeComponent("thurly:bizproc.task.list", "", Array(
	"USER_ID" => "", 
	"WORKFLOW_ID" => "", 
	"TASK_EDIT_URL" => $arResult["PATH_TO_DISK_TASK"],
	"PAGE_ELEMENTS" => 0, 
	"PAGE_NAVIGATION_TEMPLATE" => "",
	"SET_TITLE" => "Y",
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>
</div>