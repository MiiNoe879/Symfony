<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */

/** @var \Thurly\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
$responsibleAttributeValue = htmlspecialcharsbx(\Thurly\Main\Web\Json::encode(array(
	'valueInputName' => $map['Responsible']['FieldName'],
	'selected'       => \Thurly\Crm\Automation\Helper::prepareUserSelectorEntities(
		$dialog->getDocumentType(),
		$dialog->getCurrentValue($map['Responsible']['FieldName'], $map['Responsible']['Default'])
	),
	'multiple' => false,
	'required' => false,
)));
?>
<div class="crm-automation-popup-settings">
	<input name="<?=htmlspecialcharsbx($map['Subject']['FieldName'])?>" type="text" class="crm-automation-popup-input"
		value="<?=htmlspecialcharsbx($dialog->getCurrentValue($map['Subject']['FieldName']))?>"
		placeholder="<?=htmlspecialcharsbx($map['Subject']['Name'])?>"
		data-role="inline-selector-target"
	>
</div>
<div class="crm-automation-popup-settings">
<textarea name="<?=htmlspecialcharsbx($map['Description']['FieldName'])?>"
		class="crm-automation-popup-textarea"
		placeholder="<?=htmlspecialcharsbx($map['Description']['Name'])?>"
		data-role="inline-selector-target"
><?=htmlspecialcharsbx($dialog->getCurrentValue($map['Description']['FieldName']))?></textarea>
</div>
<div class="crm-automation-popup-settings">
<span class="crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete">
	<?=htmlspecialcharsbx($map['Responsible']['Name'])?>:
</span>
	<div data-role="user-selector" data-config="<?= $responsibleAttributeValue ?>"></div>
</div>
<div class="crm-automation-popup-checkbox">
	<div class="crm-automation-popup-checkbox-item">
		<label class="crm-automation-popup-chk-label">
			<input type="checkbox" name="<?=htmlspecialcharsbx($map['IsImportant']['FieldName'])?>" value="Y" class="crm-automation-popup-chk" <?=$dialog->getCurrentValue($map['IsImportant']['FieldName']) === 'Y' ? 'checked' : ''?>>
			<?=htmlspecialcharsbx($map['IsImportant']['Name'])?>
		</label>
	</div>
	<div class="crm-automation-popup-checkbox-item">
		<label class="crm-automation-popup-chk-label">
			<input type="checkbox"
				name="<?=htmlspecialcharsbx($map['AutoComplete']['FieldName'])?>"
				value="Y"
				class="crm-automation-popup-chk"<?=$dialog->getCurrentValue($map['AutoComplete']['FieldName']) === 'Y' ? 'checked' : ''?>
				data-role="save-state-checkbox"
				data-save-state-key="activity_auto_complete"
			>
			<?=htmlspecialcharsbx($map['AutoComplete']['Name'])?>
		</label>
	</div>
</div>