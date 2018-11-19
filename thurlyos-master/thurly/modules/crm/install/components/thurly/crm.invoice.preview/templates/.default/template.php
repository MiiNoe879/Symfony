<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/thurly/js/crm/css/crm-preview.css');
?>

<div class="crm-preview">
	<div class="crm-preview-header">
		<span class="crm-preview-header-icon crm-preview-header-icon-invoice"></span>
		<span class="crm-preview-header-title">
			<?=GetMessage("CRM_TITLE_INVOICE")?>:
			<a href="<?=htmlspecialcharsbx($arParams['URL'])?>" target="_blank"><?=htmlspecialcharsbx($arResult['ORDER_TOPIC'])?></a>
		</span>
	</div>
	<table class="crm-preview-info">
		<tr>
			<td><?= GetMessage('CRM_FIELD_ASSIGNED_BY')?>: </td>
			<td>
				<script>
					BX.tooltip(<?=htmlspecialcharsbx($arResult["RESPONSIBLE_ID"])?>, "a_<?=htmlspecialcharsbx($arResult['RESPONSIBLE_UNIQID'])?>", "");
				</script>
				<a id="a_<?=htmlspecialcharsbx($arResult['RESPONSIBLE_UNIQID'])?>" href="<?=htmlspecialcharsbx($arResult["RESPONSIBLE_PROFILE"])?>" target="_blank">
					<?=htmlspecialcharsbx($arResult['RESPONSIBLE_FORMATTED_NAME'])?>
				</a>
			</td>
			<td><?= GetMessage('CRM_FIELD_STATUS')?>: </td>
			<td><?=htmlspecialcharsbx($arResult['STATUS_TEXT'])?></td>
		</tr>
		<tr>
			<td><?= GetMessage('CRM_FIELD_DATE_BILL')?>:</td>
			<td><?=htmlspecialcharsbx($arResult['DATE_BILL'])?></td>
			<td><?= GetMessage('CRM_FIELD_DATE_PAY_BEFORE')?>:</td>
			<td><?=htmlspecialcharsbx($arResult['DATE_PAY_BEFORE'])?></td>
		</tr>
		<tr><td colspan="4"><div class="crm-preview-info-spacer"></div></td></tr>
		<tr>
			<td>
				<span <?=($arResult['UF_DEAL_ID'] == 0 ? 'style="display:none;"' : '' )?>>
					<?= GetMessage('CRM_FIELD_DEAL')?>:
				</span>
			</td>
			<td>
				<span <?=($arResult['UF_DEAL_ID'] == 0 ? 'style="display:none;"' : '' )?>>
					<a href="<?=htmlspecialcharsbx($arResult['DEAL_PROFILE'])?>" target="_blank"><?=htmlspecialcharsbx($arResult['UF_DEAL_TITLE'])?></a>
				</span>
			</td>
			<td>
				<span <?=($arResult['UF_QUOTE_ID'] == 0 ? 'style="display:none;"' : '' )?>>
					<?= GetMessage('CRM_FIELD_QUOTE')?>:
				</span>
			</td>
			<td>
				<span <?=($arResult['UF_QUOTE_ID'] == 0 ? 'style="display:none;"' : '' )?>>
					<a href="<?=htmlspecialcharsbx($arResult['QUOTE_PROFILE'])?>" target="_blank">
						<?=htmlspecialcharsbx($arResult['UF_QUOTE_TITLE'])?>
					</a>
				</span>
			</td>
		</tr>
		<tr>
			<td><?= GetMessage('CRM_FIELD_OPPORTUNITY')?>: </td>
			<td><?=htmlspecialcharsbx($arResult['FORMATTED_SUM'])?></td>
		</tr>
	</table>
</div>