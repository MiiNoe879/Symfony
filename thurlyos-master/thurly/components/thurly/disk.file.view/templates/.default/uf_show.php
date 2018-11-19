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
<table>
	<tbody>
		<? foreach($arResult["USER_FIELDS"] as $arUserField) {?>
		<tr>
			<td class="bx-disk-filepage-fileinfo-param"><?php echo htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"])?>:</td>
			<td class="bx-disk-filepage-fileinfo-value">
				<? $APPLICATION->includeComponent(
					"thurly:system.field.view",
					$arUserField["USER_TYPE"]["USER_TYPE_ID"],
					array("arUserField" => $arUserField),
					null,
					array("HIDE_ICONS"=>"Y")
				); ?>
			</td>
		</tr>
		<? }?>
	</tbody>
</table>


<script type="text/javascript">
	BX(function () {
	});
</script>

