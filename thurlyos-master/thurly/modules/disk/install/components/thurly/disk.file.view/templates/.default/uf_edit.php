<?php
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

$APPLICATION->ShowAjaxHead();

Loc::loadMessages(__DIR__ . '/template.php');
?>
<div id="bx-disk-file-uf-errors" class="errortext" style="color: red; padding-bottom: 10px;"></div>
<form action="<?php echo POST_FORM_ACTION_URI ?>" method="post" name="file-edit-form" id="file-edit-form" enctype="multipart/form-data">
<?php echo thurly_sessid_post() ?>
	<input type="hidden" name="fileId" value="<?= $arResult['FILE']['ID'] ?>">
<table>
	<tbody>
		<? foreach($arResult["USER_FIELDS"] as $arUserField) {?>
		<tr>
			<td class="bx-disk-filepage-fileinfo-param"><?php echo htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"])?>:</td>
			<td class="bx-disk-filepage-fileinfo-value">
				<?
					$APPLICATION->IncludeComponent(
						"thurly:system.field.edit",
						$arUserField["USER_TYPE"]["USER_TYPE_ID"],
						array(
							"bVarsFromForm" => false,
							"arUserField" => $arUserField,
							"form_name" => "file-edit-form",
						), null, array("HIDE_ICONS" => "Y")
					);
				 ?>
			</td>
		</tr>
		<? }?>
	</tbody>
</table>
	<div style="text-align: center; width: 100%">
		<button id="bx-disk-submit-uf-file-edit-form" type="submit" class="bx-disk-btn bx-disk-btn-big bx-disk-btn-green"><?= Loc::getMessage('DISK_FILE_VIEW_BTN_EDIT_USER_FIELDS') ?></button>
		<a id="bx-disk-submit-uf-file-discard-form" class="bx-disk-btn bx-disk-btn-big"><?= Loc::getMessage('DISK_FILE_VIEW_BTN_DISCARD_USER_FIELDS') ?></a>
	</div>
</form>

<script type="text/javascript">
	BX(function () {
		var submitForm = function(e){
			if(BX.hasClass(BX('bx-disk-submit-uf-file-edit-form'), 'clock'))
			{
				BX.PreventDefault(e);
				return;
			}
			BX('bx-disk-submit-uf-file-discard-form').style.opacity = '0';
			BX.addClass(BX('bx-disk-submit-uf-file-edit-form'), 'clock');
			BX.ajax.submitAjax(BX('file-edit-form'), {
				url: BX.Disk.addToLinkParam('/thurly/components/thurly/disk.file.view/ajax.php', 'action', 'saveUserField'),
				dataType : "json",
				method : "POST",
				onsuccess: BX.delegate(function (response){

					if (!response) {
						BX.removeClass(BX('bx-disk-submit-uf-file-edit-form'), 'clock');
						return;
					}
					if(response.status === 'error')
					{
						BX.removeClass(BX('bx-disk-submit-uf-file-edit-form'), 'clock');

						var msg = [];
						for(var i in response.errors)
						{
							if(!response.errors.hasOwnProperty(i))
								continue;
							msg.push(response.errors[i].message);
						}
						BX.adjust(BX('bx-disk-file-uf-errors'), {
							html: msg.join('<br/>')
						});
						BX.scrollToNode(BX('bx-disk-file-uf-errors'));
					}
					if(response.status === 'success')
					{
						var editContentNode = BX('file-edit-form').parentNode;
						var showContentNode = BX.findChild(editContentNode.parentNode, {className: 'bx-disk-uf-show-content'});
						BX.cleanNode(showContentNode);

						BX.Disk.ajax({
							url: BX.Disk.addToLinkParam(document.location.href.replace(document.location.hash, ''), 'action', 'showUserField'),
							method: 'POST',
							dataType: 'html',
							data: {},
							onsuccess: BX.delegate(function (data)
							{
								BX.adjust(showContentNode, {html: data});
								BX.show(showContentNode, 'block');
								BX.cleanNode(editContentNode);
								BX.removeClass(BX('bx-disk-submit-uf-file-edit-form'), 'clock');
								//BX.remove(loader);
							}, this)
						});

					}
				}, this)
			});

			BX.PreventDefault(e);
		};
		var discardForm = function(e){
			var editContentNode = BX('file-edit-form').parentNode;
			var showContentNode = BX.findChild(editContentNode.parentNode, {className: 'bx-disk-uf-show-content'});

			BX.unbind(BX('file-edit-form'), 'submit', submitForm);
			BX.unbind(BX('bx-disk-submit-uf-file-discard-form'), 'click', discardForm);
			BX.cleanNode(editContentNode);
			BX.show(showContentNode, 'block');

			BX.PreventDefault(e);
		};

		BX.bind(BX('bx-disk-submit-uf-file-discard-form'), 'click', discardForm);

		BX.bind(BX('file-edit-form'), 'submit', submitForm);
	});
</script>

