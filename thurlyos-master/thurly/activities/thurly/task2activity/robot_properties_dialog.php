<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Thurly\Bizproc\Activity\PropertiesDialog $dialog */

$data = $dialog->getRuntimeData();
$errors = array();

$checkboxes = array();

foreach ($arDocumentFields as $fieldId => $field)
{
	if ($fieldId === 'PRIORITY' || !in_array($fieldId, $allowedTaskFields))
		continue;

	if ($field['BaseType'] === 'bool')
	{
		$checkboxes[$fieldId] = $field;
		continue;
	}

	$value = $arCurrentValues[$fieldId];
	?>
	<div class="crm-automation-popup-settings">
		<span class="crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete"><?=htmlspecialcharsbx($field['Name'])?>: </span>
		<?
	switch ($field['BaseType'])
	{
		case 'string':
			?>
			<input name="<?=htmlspecialcharsbx($fieldId)?>" type="text" class="crm-automation-popup-input"
					value="<?=htmlspecialcharsbx($value)?>"
					data-role="inline-selector-target"
			>
			<?
			break;
		case 'user':
			$userValue = $rawValues[$fieldId];
			if (!$userValue && $field['Required'])
			{
				$userValue = array('author');
			}
			?>
			<div data-role="user-selector" data-config="<?=htmlspecialcharsbx(
				\Thurly\Main\Web\Json::encode(array(
					'valueInputName' => $fieldId,
					'selected' => \Thurly\Crm\Automation\Helper::prepareUserSelectorEntities(
						$dialog->getDocumentType(),
						$userValue
					),
					'multiple' => $field['Multiple'],
					'required' => $field['Required'],
				))
			)?>"></div>
			<?
			break;
		case 'datetime':
			?>
			<input name="<?=htmlspecialcharsbx($fieldId)?>" type="text" class="crm-automation-popup-input crm-automation-popup-input-calendar"
				   value="<?=htmlspecialcharsbx($value)?>"
				   data-role="inline-selector-target"
				   data-selector-type="datetime"
				   data-selector-write-mode="replace"
			>
			<?
			break;
		case 'text':
			?>
			<textarea name="<?=htmlspecialcharsbx($fieldId)?>"
					  class="crm-automation-popup-textarea"
					  data-role="inline-selector-target"
			><?=htmlspecialcharsbx($value)?></textarea>
			<?
			break;
		case 'select':
			$options = isset($field['Options']) && is_array($field['Options'])
				? $field['Options'] : array();
			?>
			<select class="crm-automation-popup-settings-dropdown" name="<?=htmlspecialcharsbx($fieldId)?>">
				<?
				foreach ($options as $k => $v)
				{
					echo '<option value="'.htmlspecialcharsbx($k).'"'.($k == $value ? ' selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
				}
				?>
			</select>
			<?
			break;
	}
	?>
	</div>
<?
}
?>
<div class="crm-automation-popup-checkbox">
	<div class="crm-automation-popup-checkbox-item">
		<label class="crm-automation-popup-chk-label">
			<input type="hidden" name="PRIORITY" value="1">
			<input type="checkbox" name="PRIORITY" value="2" class="crm-automation-popup-chk" <?=$arCurrentValues['PRIORITY'] == 2 ? 'checked' : ''?>>
			<?=GetMessage('TASKS_BP_RPD_PRIORITY')?>
		</label>
	</div>
<?foreach ($checkboxes as $fieldId => $field):?>
	<div class="crm-automation-popup-checkbox-item">
		<label class="crm-automation-popup-chk-label">
			<input type="hidden" name="<?=htmlspecialcharsbx($fieldId)?>" value="N">
			<input type="checkbox" name="<?=htmlspecialcharsbx($fieldId)?>" value="Y" class="crm-automation-popup-chk" <?=$arCurrentValues[$fieldId] != 'N' ? 'checked' : ''?>>
			<?=htmlspecialcharsbx($field['Name'])?>
		</label>
	</div>
<?endforeach;?>
</div>
<input type="hidden" name="HOLD_TO_CLOSE" value="N">
<input type="hidden" name="AUTO_LINK_TO_CRM_ENTITY" value="Y">
