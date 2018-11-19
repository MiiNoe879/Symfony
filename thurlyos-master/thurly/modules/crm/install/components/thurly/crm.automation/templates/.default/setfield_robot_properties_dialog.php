<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var \Thurly\Bizproc\Activity\PropertiesDialog $dialog */
$dialog = $arResult['dialog'];

$data = $dialog->getRuntimeData();
extract($data);
?>
<div class="crm-automation-popup-settings crm-automation-popup-settings-text">
	<a class="crm-automation-popup-settings-link" data-role="crm-sfa-fields-list"><?=GetMessage('CRM_AUTOMATION_SFA_FIELDS_LIST')?></a>
</div>
<?= $javascriptFunctions ?>
<script>
	BX.ready(function()
	{
		var documentFields = <?=\Thurly\Main\Web\Json::encode($arDocumentFields)?>;
		var documentFieldsSort = <?=\Thurly\Main\Web\Json::encode(array_keys($arDocumentFields))?>;

		var i, menuItems = [];

		for (i = 0; i < documentFieldsSort.length; ++i)
		{
			var fieldId = documentFieldsSort[i], propertyType, isMultiple = false;
			if (!documentFields.hasOwnProperty(fieldId))
				continue;

			if (fieldId === 'STATUS_ID' || fieldId === 'STAGE_ID' || fieldId === 'CATEGORY_ID')
			{
				continue;
			}

			propertyType = documentFields[fieldId]['Type'];

			if (
				propertyType === 'user'
				|| propertyType === 'file'
				|| propertyType === 'UF:money'
				|| propertyType === 'UF:address'
			)
			{
				continue;
			}

			if (documentFields[fieldId]['Multiple'] === 'Y' || documentFields[fieldId]['Multiple'] === true)
			{
				isMultiple = true;
			}

			if (isMultiple)
			{
				// no multiple controls yet due interface errors:(
				continue;
			}

			menuItems.push({
				text: documentFields[fieldId]['Name'],
				fieldId: fieldId,
				onclick: function(e, item)
				{
					this.popupWindow.close();
					BWFVCAddCondition(item.fieldId, '');
				}
			});
		}

		var onFieldsListSelectClick = function(e)
		{
			var menuId = 'crm-sfa-' + Math.random();

			BX.PopupMenu.show(
				menuId,
				this,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(this)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					zIndex: 200,
					className: 'crm-automation-inline-selector-menu'
				}
			);

			return BX.PreventDefault(e);
		};

		var fieldsListSelect = document.querySelector('[data-role="crm-sfa-fields-list"]');
		if (fieldsListSelect)
		{
			BX.bind(fieldsListSelect, 'click', onFieldsListSelectClick);
		}

		function BWFVCChangeFieldType(controlWrapper, field, value)
		{
			var property = documentFields[field];
			if (!property)
				return;

			var node;

			switch (property['Type'])
			{
				case 'bool':
				case 'UF:boolean':
					node = BX.create('select', {
						attrs: {className: 'crm-automation-popup-settings-dropdown'},
						props: {name: field},
						children: [
							BX.create('option', {
								props: {value: ''},
								text: '<?=GetMessageJS('CRM_AUTOMATION_SFA_NOT_SELECTED')?>'
							})
						]
					});
					var optionY = BX.create('option', {
						props: {value: 'Y'},
						text: '<?=GetMessageJS('MAIN_YES')?>'
					});

					if (value == 'Y' || value == 1)
					{
						optionY.setAttribute('selected', 'selected');
					}

					var optionN = BX.create('option', {
						props: {value: 'N'},
						text: '<?=GetMessageJS('MAIN_NO')?>'
					});

					if (value == 'N' || value == 0)
					{
						optionN.setAttribute('selected', 'selected');
					}

					node.appendChild(optionY);
					node.appendChild(optionN);
					break;

				case 'date':
				case 'UF:date':
				case 'datetime':
					node = BX.create('input', {
						attrs: {
							className: 'crm-automation-popup-input',
							'data-role': 'inline-selector-target',
							'data-selector-type': property['Type']
						},
						props: {
							type: 'text',
							name: field,
							value: value
						}
					});
					break;

				case 'select':
				case 'internalselect':
					node = BX.create('select', {
						attrs: {className: 'crm-automation-popup-settings-dropdown'},
						props: {name: field},
						children: [
							BX.create('option', {
								props: {value: ''},
								text: '<?=GetMessageJS('CRM_AUTOMATION_SFA_NOT_SELECTED')?>'
							})
						]
					});
					if (BX.type.isPlainObject(property['Options']))
					{
						for (var key in property['Options'])
						{
							if (!property['Options'].hasOwnProperty(key))
								continue;

							var option = BX.create('option', {
								props: {value: key},
								text: property['Options'][key]
							});

							if (key == value)
							{
								option.setAttribute('selected', 'selected');
							}

							node.appendChild(option);
						}
					}
					else if (BX.type.isArray(property['Options']))
					{
						for (var i = 0; i < property['Options'].length; ++i)
						{
							var option = BX.create('option', {
								props: {value: i},
								text: property['Options'][i]
							});

							if (i == value)
							{
								option.setAttribute('selected', 'selected');
							}

							node.appendChild(option);
						}
					}

					break;

				case 'text':
					node = BX.create('textarea', {
						attrs: {
							className: 'crm-automation-popup-textarea',
							'data-role': 'inline-selector-target'
						},
						props: {name: field},
						text: value
					});
					break;

				case 'int':
				case 'double':
				case 'string':
					node = BX.create('input', {
						attrs: {
							className: 'crm-automation-popup-input',
							'data-role': 'inline-selector-target'
						},
						props: {
							type: 'text',
							name: field,
							value: value
						}
					});
					break;
				default:
					objFields.GetFieldInputControl(
						objFields.arDocumentFields[field],
						value,
						{'Field':field, 'Form':'<?= $formName ?>'},
						function(v){
							if (v)
							{
								controlWrapper.innerHTML = v;
							}
						},
						false
					);
					break;
			}

			if (node)
			{
				controlWrapper.innerHTML = "";
				controlWrapper.appendChild(node);
			}
		}

		var bwfvc_counter = -1;

		function BWFVCAddCondition(fieldId, val)
		{
			var field = documentFields[fieldId];

			var addrowTable = document.getElementById('bwfvc_addrow_table');

			bwfvc_counter++;
			var newRow = BX.create('div', {attrs: {className: 'crm-automation-popup-settings'}});

			newRow.appendChild(BX.create('span', {
				text: field.Name,
				attrs: {
					className: 'crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete'
				}
			}));

			var inputHidden = BX.create("input", {props: {type: 'hidden'}});
			inputHidden.name = "document_field_" + bwfvc_counter;
			inputHidden.value = fieldId;

			newRow.appendChild(inputHidden);

			var controlWrapper = BX.create('div');
			newRow.appendChild(controlWrapper);

			var deleteButton = BX.create('a', {
				attrs: {
					className: 'crm-automation-popup-settings-delete crm-automation-popup-settings-link crm-automation-popup-settings-link-light'
				},
				props: {href: '#'},
				events: {
					click: BX.delegate(BWFVCDeleteCondition, newRow)
				},
				text: '<?=GetMessageJS('CRM_AUTOMATION_SFA_DELETE')?>'
			});
			newRow.appendChild(deleteButton);

			BWFVCChangeFieldType(controlWrapper, fieldId, val);

			addrowTable.appendChild(newRow);

			var dlg = BX.Crm.Automation.Runtime.getRobotSettingsDialog();
			if (dlg)
			{
				dlg.template.initRobotSettingsControls(dlg.robot, newRow);
			}
		}

		function BWFVCDeleteCondition(e)
		{
			BX.remove(this);
			BX.PreventDefault(e);
		}

<?
		foreach ($arCurrentValues as $fieldKey => $documentFieldValue)
		{
		if (!array_key_exists($fieldKey, $arDocumentFields))
			continue;
		?>BWFVCAddCondition('<?= CUtil::JSEscape($fieldKey) ?>', <?= CUtil::PhpToJSObject($documentFieldValue) ?>);<?
		}
		if (count($arCurrentValues) <= 0)
		{
			$fieldIds = array_keys($arDocumentFields);
			?>BWFVCAddCondition("<?=CUtil::JSEscape($fieldIds[0])?>", "");<?
		}
?>

	});
</script>
<div id="bwfvc_addrow_table"></div>