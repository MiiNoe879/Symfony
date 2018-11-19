<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
/** @var array $arResult */

/** @var \Thurly\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
$renderMode = \Thurly\Bizproc\FieldType::RENDER_MODE_DESIGNER;

$config = array(
	'valueInputName' => $map['Responsible']['FieldName'],
	'selected' => \Thurly\Crm\Automation\Helper::prepareUserSelectorEntities(
		$dialog->getDocumentType(),
		$dialog->getCurrentValue($map['Responsible']['FieldName'])
	),
	'multiple' => $map['Responsible']['Multiple'],
	'required' => true,
);

$configAttributeValue = htmlspecialcharsbx(\Thurly\Main\Web\Json::encode($config));
?>
<div class="crm-automation-popup-settings">
	<span class="crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete">
		<?=htmlspecialcharsbx($map['Responsible']['Name'])?>:
	</span>
	<div data-role="user-selector" data-config="<?=$configAttributeValue?>"></div>
</div>