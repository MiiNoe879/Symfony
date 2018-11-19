<?php
use Thurly\Disk\Banner;
use Thurly\Disk\Desktop;
use Thurly\Main\Localization\Loc;

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

$folder = \Thurly\Disk\Folder::loadById($arResult['VARIABLES']['FOLDER_ID']);
switch(LANGUAGE_ID)
{
	case 'en':
	case 'ru':
	case 'ua':
	case 'de':
	case 'br':
	case 'la':
	case 'sc':
	case 'tc':
		$bannerName = 'disk_banner2_' . LANGUAGE_ID .  '.png';
		break;
	default:
		$bannerName = 'disk_banner2_' . Loc::getDefaultLang(LANGUAGE_ID) .  '.png';
		break;
}
?>
<div class="bx-disk-container posr" id="bx-disk-container">
			<div class="bx-disk-grid-right" style="width: 221px">
				<div id="bx_disk_empty_select_section" class="bx-disk-sidebar-section">
					<div class="bx-disk-info-panel">
						<div class="bx-disk-info-panel-relative tac">
							<div class="bx-disk-info-panel-icon-empty"><br></div>
								<div class="bx-disk-info-panel-empty-text">
									<?= Loc::getMessage('DISK_VIEW_SMALL_DETAIL_SIDEBAR') ?>
								</div>
						</div>
					</div>
				</div>

				<div id="disk_info_panel"></div>

				<div class="bx-disk-sidebar-section">
					<?php
					$APPLICATION->IncludeComponent(
						'thurly:disk.breadcrumbs.tree',
						'',
						array_merge(array_intersect_key($arResult, array(
							'STORAGE' => true,
							'PATH_TO_FOLDER_LIST' => true,
							'PATH_TO_FILE_VIEW' => true,
						)), array(
							'FOLDER' => $folder,
							'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
							'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
						)),
						$component
					);?>


				</div>
				<? if(!Desktop::isDesktopDiskInstall() && Banner::isActive('install_disk')) {?>
				<div class="bx-disk-sidebar-section">
					<a href="javascript: BX.Disk.deactiveBanner('install_disk'); BX.Disk.getDownloadDesktop();"><img src="/thurly/images/disk/<?= $bannerName ?>" alt=""></a>
				</div>
				<? }
				if ($folder->canAdd($arResult['STORAGE']->getCurrentUserSecurityContext()))
				{
				?>
				<div class="bx-disk-sidebar-section">
					<div class="wduf-uploader">
						<span class="bx-disk wd-fa-add-file-light">
							<span class="wd-fa-add-file-light-text">
								<span class="wd-fa-add-file-light-title">
									<span class="wd-fa-add-file-light-title-text"><?= Loc::getMessage('DISK_UPLOAD_FILE_OR_IMAGE') ?></span>
								</span>
								<span class="wd-fa-add-file-light-descript"><?= Loc::getMessage('DISK_UPLOAD_WITH_FILE_DRAG_AND_DROP') ?></span>
							</span>
						</span>

						<input type="file" multiple="multiple" class="wduf-fileUploader" id="BXDiskRightInputPlug"></div>
				</div>
				<?
				}
				?>
			</div>
			<div class="bx-disk-grid-left" style="margin-right: 221px">
				<div class="bx-disk-interface-toolbar-container">
					<?php
					$APPLICATION->IncludeComponent(
						'thurly:disk.folder.toolbar',
						'',
						array(
							'STORAGE' => $arResult['STORAGE'],
							'FOLDER' => $folder,

							'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_TRASHCAN_LIST'], array('TRASH_PATH' => '')),
							'URL_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_FOLDER_LIST'], array('PATH' => '')),
							'PATH_TO_EXTERNAL_LINK_LIST' => $arResult['PATH_TO_EXTERNAL_LINK_LIST'],
							'PATH_TO_FOLDER_LIST' => $arResult['PATH_TO_FOLDER_LIST'],
							'PATH_TO_FILE_VIEW' => $arResult['PATH_TO_FILE_VIEW'],
							'PATH_TO_TRASHCAN_LIST' => $arResult['PATH_TO_TRASHCAN_LIST'],

							'TYPE' => 'list',
							'EXTENDED_ADD_FOLDER' => true && !$folder->getParentId(),

							'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
							'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
						),
						$component
					);
					?>
				</div>
				<?php
				$APPLICATION->IncludeComponent(
					'thurly:disk.folder.list',
					'',
					array_merge(array_intersect_key($arResult, array(
						'STORAGE' => true,
						'PATH_TO_FOLDER_LIST' => true,
						'PATH_TO_FILE_VIEW' => true,
						'PATH_TO_DISK_BIZPROC_WORKFLOW_ADMIN' => true,
						'PATH_TO_DISK_START_BIZPROC' => true,
						'PATH_TO_DISK_TASK_LIST' => true,
						'PATH_TO_DISK_TASK' => true,
						'PATH_TO_DISK_VOLUME' => true,
					)), array(
						'FOLDER' => $folder,
						'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
						'RELATIVE_ITEMS' => $arResult['VARIABLES']['RELATIVE_ITEMS'],
					)),
					$component
				);?>
			</div>
<?$APPLICATION->IncludeComponent(
	'thurly:disk.file.upload',
	'',
	array(
		'STORAGE' => $arResult['STORAGE'],
		'FOLDER' => $folder,
		'CID' => 'FolderList',
		'INPUT_CONTAINER' => '((BX.__tmpvar=BX.findChild(BX("folder_toolbar"), {className : "element-upload"}, true))&&BX.__tmpvar?BX.__tmpvar.parentNode:null)',
		'DROPZONE' => 'BX("bx-disk-container")'
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
<script type="text/javascript">
BX.ready(function(){
	if (BX('BXDiskRightInputPlug') && BX.DiskUpload.getObj('FolderList'))
	{
		BX.DiskUpload.getObj('FolderList').agent.init(BX('BXDiskRightInputPlug'));
	}
});
</script>
</div>

