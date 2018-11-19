<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
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
/** @var \Thurly\Disk\Folder $arParams["FOLDER"] */
if (!isset($arParams["INPUT_CONTAINER"]))
	return;
include_once(__DIR__."/message.php");
CJSCore::Init(array('uploader'));
//$arParams["INPUT_CONTAINER"];
//$arParams["CID"];
//$arParams["DROPZONE"];
$statusStartBizProc = isset($arParams['STATUS_START_BIZPROC']) ? $arParams['STATUS_START_BIZPROC'] : '';
$bizProcParameters = isset($arParams['BIZPROC_PARAMETERS']) ? $arParams['BIZPROC_PARAMETERS'] : '';
$bizProcParametersRequired = isset($arParams['BIZPROC_PARAMETERS_REQUIRED']) ? $arParams['BIZPROC_PARAMETERS_REQUIRED'] : '';
?>
<script>
BX.ready(function() {
	BX.DiskUpload.initialize({
		bp: '<?= $statusStartBizProc ?>',
		bpParameters: '<?= $bizProcParameters ?>',
		bpParametersRequired: <?= \Thurly\Main\Web\Json::encode($bizProcParametersRequired) ?>,
		storageId: <?= $arParams['STORAGE_ID'] ?>,
		CID : '<?=CUtil::JSEscape($arParams["CID"])?>',
		<?if (!empty($arParams["FILE_ID"])): ?>targetFileId : '<?=CUtil::JSEscape($arParams["FILE_ID"])?>',<?
		else: ?>targetFolderId : '<?=CUtil::JSEscape(($arParams["FOLDER"] ? $arParams["FOLDER"]->getId() : ''))?>',<? endif; ?>
		inputContainer : <?=$arParams["~INPUT_CONTAINER"]?>,
		urlUpload : '/thurly/components/thurly/disk.file.upload/ajax.php',
		dropZone : <?=(isset($arParams["~DROPZONE"]) ? $arParams["~DROPZONE"] : 'null')?>});
});
</script>
<?
global $USER;
if(
	\Thurly\Disk\Integration\ThurlyOSManager::isEnabled()
)
{
	?>
	<div id="bx-thurlyos-business-tools-info" style="display: none; width: 600px; margin: 9px;">
		<? $APPLICATION->IncludeComponent('thurly:thurlyos.business.tools.info', '', array()); ?>
	</div>
<?
}