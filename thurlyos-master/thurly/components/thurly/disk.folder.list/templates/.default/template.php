<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
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
use Thurly\Disk\Internals\Grid\FolderListOptions;
use Thurly\Main\Localization\Loc;
?>

<?
CJSCore::Init(array('viewer', 'disk', 'disk_information_popups', 'socnetlogdest', 'access', 'tooltip'));

$jsSettingsDropdown = $jsDropdown = array();

if(!empty($arResult['STORAGE']['CAN_CHANGE_RIGHTS_ON_STORAGE']))
{
	$jsSettingsDropdown[] = array(
		'text' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_CHANGE_RIGHTS'),
		'title' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_CHANGE_RIGHTS'),
		'href' => "javascript:BX.Disk['FolderListClass_{$component->getComponentId()}'].showRightsOnStorage();",
	);
}
if(!empty($arResult['STORAGE']['CAN_CHANGE_SETTINGS_ON_STORAGE']) && $arResult['STORAGE']['CAN_CHANGE_SETTINGS_ON_BIZPROC_EXCEPT_USER'] && \Thurly\Disk\Integration\BizProcManager::isAvailable())
{
	$jsSettingsDropdown[] = array(
		'text' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_BIZPROC_SETTINGS'),
		'title' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_BIZPROC_SETTINGS'),
		'href' => "javascript:BX.Disk['FolderListClass_{$component->getComponentId()}'].showSettingsOnBizproc();",
	);
}
if(!empty($arResult['STORAGE']['CAN_CHANGE_SETTINGS_ON_BIZPROC']) && $arResult['STORAGE']['CAN_CHANGE_SETTINGS_ON_BIZPROC_EXCEPT_USER'] && $arResult['STORAGE']['SHOW_BIZPROC'])
{
	$jsSettingsDropdown[] = array(
		'text' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_BIZPROC'),
		'title' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_BIZPROC'),
		'href' => $arParams["PATH_TO_DISK_BIZPROC_WORKFLOW_ADMIN"],
	);
}
$linkOnNetworkDrive = CUtil::JSescape($arResult['STORAGE']['NETWORK_DRIVE_LINK']);
$jsSettingsDropdown[] = array(
	'text' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_NETWORK_DRIVE'),
	'title' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_NETWORK_DRIVE'),
	'href' => "javascript:BX.Disk['FolderListClass_{$component->getComponentId()}'].showNetworkDriveConnect({
		link: '{$linkOnNetworkDrive}'
	});",
);
$jsSettingsDropdown[] = array(
	'text' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_SETTINGS_DOCS'),
	'title' => Loc::getMessage('DISK_FOLDER_LIST_PAGE_TITLE_SETTINGS_DOCS'),
	'href' => "javascript:BX.Disk['FolderListClass_{$component->getComponentId()}'].openWindowForSelectDocumentService({});",
);
if (!empty($arResult["PATH_TO_DISK_VOLUME"]))
{
	$jsSettingsDropdown[] = array(
		'text' => Loc::getMessage('DISK_FOLDER_LIST_VOLUME_PURIFY'),
		'title' => Loc::getMessage('DISK_FOLDER_LIST_VOLUME_PURIFY'),
		'href' => $arResult["PATH_TO_DISK_VOLUME"],
	);
}
$currentPage = $APPLICATION->GetCurPageParam('', array($arResult['GRID']["SORT_VARS"]["order"], $arResult['GRID']["SORT_VARS"]["by"]));
foreach($arResult['GRID']['COLUMN_FOR_SORTING'] as $name => $column)
{
	$jsDropdown[] = array(
		'text' => $column['LABEL'],
		'title' => $column['LABEL'],
		'href' => CHTTP::urlAddParams($currentPage, array($arResult['GRID']["SORT_VARS"]["by"] => $name, $arResult['GRID']["SORT_VARS"]["order"] => 'DESC')),
	);
}
unset($column, $name);

$byColumn = key($arResult['GRID']['SORT']);
$direction = $arResult['GRID']['SORT'][$byColumn];
$inverseDirection = strtolower($direction) == 'desc'? 'asc' : 'desc';
$label = $arResult['GRID']['COLUMN_FOR_SORTING'][$byColumn]['LABEL'];

if($jsDropdown)
{
	$isDescDirection = $inverseDirection === 'desc';
	$isMixSorting = $arResult['GRID']['SORT_MODE'] === FolderListOptions::SORT_MODE_MIX;

	$jsDropdown[] = array(
		'delimiter' => true,
	);
	$jsDropdown[] = array(
		'className' => $isDescDirection? '' : 'menu-popup-item-accept',
		'text' => Loc::getMessage('DISK_FOLDER_LIST_LABEL_SORT_INVERSE_DIRECTION'),
		'title' => Loc::getMessage('DISK_FOLDER_LIST_LABEL_SORT_INVERSE_DIRECTION'),
		'href' => CHTTP::urlAddParams($currentPage, array($arResult['GRID']["SORT_VARS"]["by"] => $byColumn, $arResult['GRID']["SORT_VARS"]["order"] => $inverseDirection)),
	);
	$jsDropdown[] = array(
		'delimiter' => true,
	);
	$jsDropdown[] = array(
		'className' => !$isMixSorting? '' : 'menu-popup-item-accept',
		'text' => Loc::getMessage('DISK_FOLDER_LIST_LABEL_SORT_MIX_MODE'),
		'title' => Loc::getMessage('DISK_FOLDER_LIST_LABEL_SORT_MIX_MODE'),
		'href' => CHTTP::urlAddParams($currentPage, array('sortMode' => $isMixSorting? FolderListOptions::SORT_MODE_ORDINARY : FolderListOptions::SORT_MODE_MIX)),
	);
}
?>

<div class="bx-disk-interface-toolbar-container" style="max-height: 60px; overflow: hidden;">
	<div class="bx-disk-interface-sort">
		<?= Loc::getMessage('DISK_FOLDER_LIST_LABEL_SORT_BY') ?>:
		<span id="sort_by_column" class="popup-control">
			<span class="popup-current">
				<span class="popup-current-text">
					<?= $label ?>
					<span class="icon-arrow"></span>
				</span>
			</span>
		</span>

		<span class="bx-disk-interface-view-mode">
			<span class="view-mode-tile <?= ($arResult['GRID']['MODE'] == 'tile'? 'current' : '') ?>" onclick="jsUtils.Redirect(arguments, '?viewMode=tile');"></span>
			<span class="view-mode-grid <?= ($arResult['GRID']['MODE'] == 'grid'? 'current' : '') ?>" onclick="jsUtils.Redirect(arguments, '?viewMode=grid');"></span>
		</span>
	</div>

	<?
	$APPLICATION->IncludeComponent(
		'thurly:disk.breadcrumbs',
		'',
		array(
			'BREADCRUMBS_ROOT' => $arResult['BREADCRUMBS_ROOT'],
			'BREADCRUMBS' => $arResult['BREADCRUMBS'],
		)
	);
	?>

	<div style="clear: both;"></div>
</div>

<? if($arParams['STATUS_BIZPROC']) { ?>
	<div style="display:none;">
		<form id="parametersFormBp">
		<div id="divStartBizProc" class="bx-disk-form-bizproc-start-div">
			<table class="bx-disk-form-bizproc-start-table">
				<col class="bx-disk-col-table-left">
				<col class="bx-disk-col-table-right">
				<? if(!empty($arResult['WORKFLOW_TEMPLATES'])) {
					if($arResult['BIZPROC_PARAMETERS']) {?>
						<tr>
							<td class="bx-disk-form-bizproc-start-td-title" colspan="2">
								<?= Loc::getMessage('DISK_FOLDER_LIST_LABEL_START_BIZPROC') ?>
							</td>
						</tr>
						<tr id="errorTr">
							<td id="errorTd" class="bx-disk-form-bizproc-start-td-error" colspan="2">

							</td>
						</tr>
					<? }
					foreach($arResult['WORKFLOW_TEMPLATES'] as $workflowTemplate)
					{
						if(!empty($workflowTemplate['PARAMETERS'])) { ?>
							<tr>
								<td class="bx-disk-form-bizproc-start-td-name-bizproc" colspan="2">
									<?= $workflowTemplate['NAME'] ?>
									<input type="hidden" value="1" name="checkBp" />
									<input type="hidden" value="create" name="autoExecute" />
								</td>
							</tr>
						<?CBPDocument::StartWorkflowParametersShow($workflowTemplate['ID'], $workflowTemplate['PARAMETERS'], 'formAutoloadBizProc', false);
						}else { ?>
							<tr>
								<td class="bx-disk-form-bizproc-start-td-name-bizproc" colspan="2">
									<input type="hidden" value="1" name="checkBp" />
									<input type="hidden" value="create" name="autoExecute" />
								</td>
							</tr>
						<? }
					}
				}
				?>
			</table>
		</div>
		</form>
	</div>
<? } ?>

<div class="bx-disk-interface-filelist">
	<?
	$APPLICATION->IncludeComponent(
		'thurly:disk.interface.grid',
		'',
		array(
			'DATA_FOR_PAGINATION' => $arResult['GRID']['DATA_FOR_PAGINATION'],
			'MODE' => $arResult['GRID']['MODE'],
			'GRID_ID' => $arResult['GRID']['ID'],
			'HEADERS' => $arResult['GRID']['HEADERS'],
			'SORT' => $arResult['GRID']['SORT'],
			'SORT_VARS' => $arResult['GRID']['SORT_VARS'],
			'ROWS' => $arResult['GRID']['ROWS'],
			'FOOTER' => array(
				array(
					'title' => Loc::getMessage('DISK_FOLDER_LIST_LABEL_GRID_TOTAL'),
					'value' => $arResult['GRID']['ROWS_COUNT'],
					'id' => 'bx-disk-total-grid-item',
				),
				array(
					'place_for_pagination' => true,
				),
				array(
					'custom_html' => '
						<td class="tar" style="width: 100%;"><span id="bx-btn-disk-files-number" class="bx-disk-files-number" title="' . Loc::getMessage('DISK_FOLDER_LIST_FOOTER_HINT_DISK_CALC_FILES') . '">' . Loc::getMessage('DISK_FOLDER_LIST_FOOTER_LABEL_DISK_COUNT_FILES') . ': </span><span id="bx-disk-files-count-data" class="bx-disk-amt">--</span></td>
					',
				),
				array(
					'custom_html' => '
						<td class="tar" style="width: 100%;">' . Loc::getMessage('DISK_FOLDER_LIST_FOOTER_LABEL_DISK_SIZE_FILES') . ': <span id="bx-disk-files-size-data" class="bx-disk-amt">--</span></td>
					',
				),
			),
			'EDITABLE' => !$arResult['GRID']['ONLY_READ_ACTIONS'],
			'ALLOW_EDIT' => true,
			'ACTIONS' => array(
				'delete' => !$arResult['GRID']['ONLY_READ_ACTIONS'],
				'before_custom_html' => '
					<span class="popup-control">
						<span class="popup-current">
							<span id="folder-list-action-all-btn" class="popup-current-text fwn" data-group-action="' . ($arResult['GRID']['ONLY_READ_ACTIONS']? 'copy' : 'move') . '">
								<span class="js-text-group-action"></span>
								<span class="icon-arrow"></span>
							</span>
						</span>
					</span>
				',
				'custom_html' => '
					<span class="popup-control">
						<span class="popup-current">
							<span id="folder-list-action-show-tree" class="popup-current-text fwn">
								<span>' . Loc::getMessage('DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_DEST_LABEL') . '</span>
								<span class="icon-arrow"></span>
							</span>
						</span>
					</span>
				',
			),
			'ACTION_ALL_ROWS' => true,
			//'FILTER' => $arResult['GRID']['FILTER'],
		),
		$component
	);
	?>
</div>
<script type="text/javascript">
	BX.message({
		'wd_desktop_disk_is_installed': '<?= (bool)\Thurly\Disk\Desktop::isDesktopDiskInstall() ?>'
	});
</script>

<script type="text/javascript">
BX.message({
	DISK_FOLDER_LIST_INVITE_MODAL_TAB_PROCESS_DIE_ACCESS: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TAB_PROCESS_DIE_ACCESS")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_TAB_PROCESS_DIE_ACCESS_SUCCESS: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TAB_PROCESS_DIE_ACCESS_SUCCESS")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_TAB_PROCESS_ACCESS: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TAB_PROCESS_ACCESS")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_TAB_PROCESS_ACCESS_SUCCESS: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TAB_PROCESS_ACCESS_SUCCESS")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_TITLE_DIE_SELF_ACCESS_SIMPLE: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TITLE_DIE_SELF_ACCESS_SIMPLE")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_BTN_DIE_SELF_ACCESS_SIMPLE: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_BTN_DIE_SELF_ACCESS_SIMPLE")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_TAB_COMMON_SHARED_SECTION_PROCESS_DIE_ACCESS: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TAB_COMMON_SHARED_SECTION_PROCESS_DIE_ACCESS")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_TAB_COMMON_SHARED_SECTION_PROCESS_DIE_ACCESS_SUCCESS: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TAB_COMMON_SHARED_SECTION_PROCESS_DIE_ACCESS_SUCCESS")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_TITLE_DIE_ALL_ACCESS_SIMPLE: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TITLE_DIE_ALL_ACCESS_SIMPLE")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_TITLE_DIE_ALL_ACCESS_DESCR: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_TITLE_DIE_ALL_ACCESS_DESCR")?>',
	DISK_FOLDER_LIST_INVITE_MODAL_BTN_DIE_SELF_ACCESS_SIMPLE_CANCEL: '<?=GetMessageJS("DISK_FOLDER_LIST_INVITE_MODAL_BTN_DIE_SELF_ACCESS_SIMPLE_CANCEL")?>',
	DISK_FOLDER_LIST_TRASH_DELETE_DESTROY_FILE_CONFIRM: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_DELETE_DESTROY_FILE_CONFIRM")?>',
	DISK_FOLDER_LIST_TRASH_DELETE_DESTROY_FOLDER_CONFIRM: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_DELETE_DESTROY_FOLDER_CONFIRM")?>',
	DISK_FOLDER_LIST_TRASH_DELETE_FOLDER_CONFIRM: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_DELETE_FOLDER_CONFIRM")?>',
	DISK_FOLDER_LIST_TRASH_DELETE_FILE_CONFIRM: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_DELETE_FILE_CONFIRM")?>',
	DISK_FOLDER_LIST_TRASH_DELETE_GROUP_CONFIRM: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_DELETE_GROUP_CONFIRM")?>',
	DISK_FOLDER_LIST_TRASH_DESTROY_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_DESTROY_BUTTON")?>',
	DISK_FOLDER_LIST_TRASH_DELETE_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_DELETE_BUTTON")?>',
	DISK_FOLDER_LIST_TRASH_CANCEL_DELETE_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_CANCEL_DELETE_BUTTON")?>',
	DISK_FOLDER_LIST_TRASH_DELETE_TITLE: '<?=GetMessageJS("DISK_FOLDER_LIST_TRASH_DELETE_TITLE")?>',
	DISK_FOLDER_LIST_DETACH_FILE_TITLE: '<?= GetMessageJS('DISK_FOLDER_LIST_DETACH_FILE_TITLE') ?>',
	DISK_FOLDER_LIST_DETACH_FOLDER_TITLE: '<?= GetMessageJS('DISK_FOLDER_LIST_DETACH_FOLDER_TITLE') ?>',
	DISK_FOLDER_LIST_DETACH_FOLDER_CONFIRM: '<?= GetMessageJS('DISK_FOLDER_LIST_DETACH_FOLDER_CONFIRM') ?>',
	DISK_FOLDER_LIST_DETACH_FILE_CONFIRM: '<?= GetMessageJS('DISK_FOLDER_LIST_DETACH_FILE_CONFIRM') ?>',
	DISK_FOLDER_LIST_DETACH_BUTTON: '<?= GetMessageJS('DISK_FOLDER_LIST_DETACH_BUTTON') ?>',
	DISK_FOLDER_LIST_UNSHARE_SECTION_CONFIRM: '<?=GetMessageJS("DISK_FOLDER_LIST_UNSHARE_SECTION_CONFIRM")?>',
	DISK_FOLDER_LIST_SUCCESS_CONNECT_TO_DISK_FOLDER: '<?=GetMessageJS("DISK_FOLDER_LIST_SUCCESS_CONNECT_TO_DISK_FOLDER")?>',
	DISK_FOLDER_LIST_SUCCESS_CONNECT_TO_DISK_FILE: '<?=GetMessageJS("DISK_FOLDER_LIST_SUCCESS_CONNECT_TO_DISK_FILE")?>',
	DISK_FOLDER_LIST_SUCCESS_LOCKED_FILE: '<?=GetMessageJS("DISK_FOLDER_LIST_SUCCESS_LOCKED_FILE")?>',
	DISK_FOLDER_LIST_SUCCESS_UNLOCKED_FILE: '<?=GetMessageJS("DISK_FOLDER_LIST_SUCCESS_UNLOCKED_FILE")?>',
	DISK_FOLDER_LIST_TITLE_MODAL_GET_EXT_LINK: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_MODAL_GET_EXT_LINK")?>',
	DISK_FOLDER_LIST_DETAIL_SHARE_INFO_OWNER: '<?=GetMessageJS("DISK_FOLDER_LIST_DETAIL_SHARE_INFO_OWNER")?>',
	DISK_FOLDER_LIST_DETAIL_SHARE_INFO_HAVE_ACCESS: '<?=GetMessageJS("DISK_FOLDER_LIST_DETAIL_SHARE_INFO_HAVE_ACCESS")?>',
	DISK_FOLDER_LIST_TITLE_MODAL_TREE: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_MODAL_TREE")?>',
	DISK_FOLDER_LIST_TITLE_MODAL_MOVE_TO: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_MODAL_MOVE_TO")?>',
	DISK_FOLDER_LIST_TITLE_MODAL_COPY_TO: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_MODAL_COPY_TO")?>',
	DISK_FOLDER_LIST_TITLE_MODAL_MANY_COPY_TO: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_MODAL_MANY_COPY_TO")?>',
	DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_COPY_TO_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_COPY_TO_BUTTON")?>',
	DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_DOWNLOAD_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_DOWNLOAD_BUTTON")?>',
	DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_DELETE_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_SIDEBAR_MANY_DELETE_BUTTON")?>',
	DISK_FOLDER_LIST_TITLE_MODAL_MOVE_TO_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_MODAL_MOVE_TO_BUTTON")?>',
	DISK_FOLDER_LIST_TITLE_MODAL_COPY_TO_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_MODAL_COPY_TO_BUTTON")?>',
	DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_COPY_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_COPY_BUTTON")?>',
	DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_MOVE_BUTTON: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_GRID_TOOLBAR_MOVE_BUTTON")?>',
	DISK_FOLDER_LIST_TITLE_SIDEBAR_INT_LINK: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_SIDEBAR_INT_LINK")?>',
	DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK")?>',
	DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_PARAMS: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_PARAMS_2")?>',
	DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USE_DEATH_TIME: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USE_DEATH_TIME")?>',
	DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USE_PASSWORD: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USE_PASSWORD")?>',
	DISK_FOLDER_LIST_TITLE_EXT_PARAMS_INPUT_PASSWORD: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_EXT_PARAMS_INPUT_PASSWORD")?>',
	DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_MIN: '<?= GetMessageJS('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_MIN') ?>',
	DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_HOUR: '<?= GetMessageJS('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_HOUR') ?>',
	DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_DAY: '<?= GetMessageJS('DISK_FOLDER_LIST_TITLE_EXT_PARAMS_TIME_DAY') ?>',
	DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_ON: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_ON")?>',
	DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_OFF: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_SIDEBAR_EXT_LINK_OFF")?>',
	DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USED_DEATH_TIME: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USED_DEATH_TIME")?>',
	DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USED_PASSWORD: '<?=GetMessageJS("DISK_FOLDER_LIST_TITLE_EXT_PARAMS_USED_PASSWORD")?>',
	DISK_FOLDER_LIST_SELECTED_OBJECT_1: '<?= GetMessageJS('DISK_FOLDER_LIST_SELECTED_OBJECT_1') ?>',
	DISK_FOLDER_LIST_SELECTED_OBJECT_21: '<?= GetMessageJS('DISK_FOLDER_LIST_SELECTED_OBJECT_21') ?>',
	DISK_FOLDER_LIST_SELECTED_OBJECT_2_4: '<?= GetMessageJS('DISK_FOLDER_LIST_SELECTED_OBJECT_2_4') ?>',
	DISK_FOLDER_LIST_SELECTED_OBJECT_5_20: '<?= GetMessageJS('DISK_FOLDER_LIST_SELECTED_OBJECT_5_20') ?>',
	DISK_FOLDER_LIST_RIGHTS_TITLE_MODAL: '<?= GetMessageJS('DISK_FOLDER_LIST_RIGHTS_TITLE_MODAL') ?>',
	DISK_FOLDER_LIST_BIZPROC_TITLE_MODAL: '<?= GetMessageJS('DISK_FOLDER_LIST_BIZPROC_TITLE_MODAL') ?>',
	DISK_FOLDER_LIST_BIZPROC_LABEL: '<?= GetMessageJS('DISK_FOLDER_LIST_BIZPROC_LABEL') ?>',
	DISK_FOLDER_LIST_SHARING_TITLE_MODAL_2: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_TITLE_MODAL_2') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_RIGHTS_FOLDER: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_RIGHTS_FOLDER') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS_USER: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS_USER') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_NAME_RIGHTS') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_NAME_ADD_RIGHTS_USER: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_NAME_ADD_RIGHTS_USER') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_NAME_ALLOW_SHARING_RIGHTS_USER: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_NAME_ALLOW_SHARING_RIGHTS_USER') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_RIGHT_READ: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_RIGHT_READ') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_RIGHT_EDIT: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_RIGHT_EDIT') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_RIGHT_FULL: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_RIGHT_FULL') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_TOOLTIP_SHARING: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_TOOLTIP_SHARING') ?>',
	DISK_FOLDER_LIST_SHARING_LABEL_OWNER: '<?= GetMessageJS('DISK_FOLDER_LIST_SHARING_LABEL_OWNER') ?>',
	DISK_FOLDER_LIST_BTN_CLOSE: '<?= GetMessageJS('DISK_FOLDER_LIST_BTN_CLOSE') ?>',
	DISK_FOLDER_LIST_BTN_SAVE: '<?= GetMessageJS('DISK_FOLDER_LIST_BTN_SAVE') ?>',
	DISK_FOLDER_LIST_ACT_COPY_INTERNAL_LINK: '<?=GetMessageJS("DISK_FOLDER_LIST_ACT_COPY_INTERNAL_LINK")?>',
	DISK_FOLDER_LIST_PAGE_TITLE_NETWORK_DRIVE: '<?=GetMessageJS("DISK_FOLDER_LIST_PAGE_TITLE_NETWORK_DRIVE")?>',
	DISK_FOLDER_LIST_PAGE_TITLE_NETWORK_DRIVE_DESCR_MODAL: '<?=GetMessageJS("DISK_FOLDER_LIST_PAGE_TITLE_NETWORK_DRIVE_DESCR_MODAL")?>',
	DISK_FOLDER_LIST_LABEL_NAME_CREATE_FOLDER: '<?=GetMessageJS("DISK_FOLDER_LIST_LABEL_NAME_CREATE_FOLDER")?>',
	DISK_FOLDER_LIST_LABEL_LIVE_UPDATE_FILE: '<?=GetMessageJS("DISK_FOLDER_LIST_LABEL_LIVE_UPDATE_FILE")?>',
	DISK_FOLDER_LIST_LABEL_ALREADY_CONNECT_DISK: '<?=GetMessageJS("DISK_FOLDER_LIST_LABEL_ALREADY_CONNECT_DISK")?>',
	DISK_FOLDER_LIST_LABEL_CONNECT_DISK: '<?=GetMessageJS("DISK_FOLDER_LIST_LABEL_CONNECT_DISK")?>',
	DISK_FOLDER_LIST_LABEL_DISCONNECTED_DISK: '<?=GetMessageJS("DISK_FOLDER_LIST_LABEL_DISCONNECTED_DISK")?>',
	DISK_FOLDER_LIST_CREATE_FOLDER_MODAL: '<?=GetMessageJS("DISK_FOLDER_LIST_CREATE_FOLDER_MODAL")?>',
	DISK_FOLDER_LIST_LABEL_SHOW_EXTENDED_RIGHTS: '<?=GetMessageJS("DISK_FOLDER_LIST_LABEL_SHOW_EXTENDED_RIGHTS")?>'
});
BX(function () {
	BX.Disk.storePathToUser('<?= CUtil::JSUrlEscape($arParams['PATH_TO_USER']) ?>');
	BX.Disk['FolderListClass_<?= $component->getComponentId() ?>'] = new BX.Disk.FolderListClass({
		rootObject: {
			id: <?= $arResult['BREADCRUMBS_ROOT']['ID'] ?>,
			canAdd: <?= $arResult['STORAGE']['CAN_ADD']? 1 : 0 ?>,
			name: '<?= CUtil::JSEscape($arResult['BREADCRUMBS_ROOT']['NAME']) ?>'
		},
		currentFolder: {
			id: <?= (int)$arResult['FOLDER']['ID'] ?>
		},
		storage: {
			id: <?= $arResult['STORAGE']['ID'] ?>,
			name: '<?= CUtil::JSEscape($arResult['STORAGE']['NAME']) ?>',
			rootObject: {
				id: <?= $arResult['STORAGE']['ROOT_OBJECT_ID'] ?>
			},
			manage: {
				connectButtonId: 'bx-disk-disconnect-connect-disk',
				link: {
					object: {
						id: <?= isset($arResult['STORAGE']['CONNECTED_SOCNET_GROUP_OBJECT_ID'])? $arResult['STORAGE']['CONNECTED_SOCNET_GROUP_OBJECT_ID'] : 'null' ?>
					}
				}
			}
		},
		getFilesCountAndSize: {
			button: BX('bx-btn-disk-files-number'),
			sizeContainer: BX('bx-disk-files-size-data'),
			countContainer: BX('bx-disk-files-count-data')
		},
		enabledModZip: <?= $arResult['ENABLED_MOD_ZIP']? 'true' : 'false' ?>,
		enabledExternalLink: <?= $arResult['ENABLED_EXTERNAL_LINK']? 'true' : 'false' ?>,
		enabledObjectLock: <?= $arResult['ENABLED_OBJECT_LOCK']? 'true' : 'false' ?>,
		isThurlyOS: <?= $arResult['IS_THURLY24']? 'true' : 'false' ?>,
		grid: bxGrid_<?= $arResult['GRID']['ID'] ?>,
		gridGroupActionButton: 'folder-list-action-all-btn',
		gridShowTreeButton: 'folder-list-action-show-tree',
		infoPanelContainer: 'disk_info_panel',
		errors: <?= Thurly\Main\Web\Json::encode($arResult['ERRORS_IN_GRID_ACTIONS']) ?>,
		information: '<?= CUtil::JSEscape($arResult['GRID_INFORMATION']) ?>',
		queryUrl: ''
	});

	BX.bind(
		BX('sort_by_column'),
		'click',
		function(){
			BX.PopupMenu.show(
				'sort_by_column_menu',
				BX('sort_by_column'),
				<?= CUtil::PhpToJSObject($jsDropdown) ?>,
				{
					autoHide : true,
					offsetTop: 0,
					offsetLeft: 55,
					angle: { offset: 45 },
					events:
					{
						onPopupClose : function(){}
					}
				}
			);
		}
	);

	BX.bind(
		BX('bx-disk-settings-change-btn'),
		'click',
		function(e){
			BX.PreventDefault(e);
			var menu = BX.PopupMenu.getMenuById('settings_disk');
			if(menu && menu.popupWindow)
			{
				if(menu.popupWindow.isShown())
				{
					BX.PopupMenu.destroy('settings_disk');
					return;
				}
			}
			BX.PopupMenu.show(
				'settings_disk',
				BX('bx-disk-settings-change-btn'),
				<?= CUtil::PhpToJSObject($jsSettingsDropdown) ?>,
				{
					autoHide : true,
					offsetTop: 0,
					offsetLeft: 0,
					angle: { offset: 25 },
					events:
					{
						onPopupClose : function(){}
					}
				}
			);
		}
	);

	BX.viewElementBind(
		'<?=$arResult['GRID']['ID']?>',
		{showTitle: true},
		{attr: 'data-bx-viewer'}
	);

	if (window.location.href.match(/[#]disconnect/)) {
		var objectId = window.location.href.match(/objectId=([0-9]+)/);
		if(objectId)
		{
			diskOpenConfirmDetach(objectId[1], '<?= $arResult['URL_TO_DETACH_OBJECT'] ?>');
		}
	}
});
</script>

<?
// set title buttons
$this->setViewTarget("pagetitle");
?>
	<div class="bx-disk-searchbox">
	<? if(!empty($arResult['STORAGE']['FOR_SOCNET_GROUP'])){ ?>
		<span id="bx-disk-disconnect-connect-disk" class="webform-small-button webform-small-button-transparent <?= ($arResult['STORAGE']['CONNECTED_SOCNET_GROUP']? 'webform-small-button-check-round disconnect' : 'webform-small-button-disk connect') ?>">
			<span class="webform-small-button-icon"></span>
			<span class="webform-small-button-text" id="bx-disk-disconnect-connect-disk-text"><?= ($arResult['STORAGE']['CONNECTED_SOCNET_GROUP']? Loc::getMessage('DISK_FOLDER_LIST_LABEL_ALREADY_CONNECT_DISK') : Loc::getMessage('DISK_FOLDER_LIST_LABEL_CONNECT_DISK')) ?></span>
		</span>
	<? } ?>
		<span id="bx-disk-settings-change-btn" class="webform-small-button webform-small-button-transparent webform-cogwheel">
			<span class="webform-button-icon"></span>
		</span>
	</div>
<?
$this->endViewTarget();

$APPLICATION->IncludeComponent('thurly:disk.help.network.drive','');

global $USER;
if(
	\Thurly\Disk\Integration\ThurlyOSManager::isEnabled()
)
{
	?>
	<div id="bx-thurlyos-business-tools-info" style="display: none; width: 600px; margin: 9px;">
		<? $APPLICATION->IncludeComponent('thurly:thurlyos.business.tools.info', '', array()); ?>
	</div>
	<script type="text/javascript">
	BX.message({
		disk_restriction: <?= (!\Thurly\Disk\Integration\ThurlyOSManager::checkAccessEnabled('disk', $USER->getId())? 'true' : 'false') ?>
	});
	</script>
<?
}
?>