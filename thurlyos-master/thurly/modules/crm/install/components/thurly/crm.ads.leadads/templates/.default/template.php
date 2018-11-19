<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Thurly\Main\Localization\Loc;

/** @var array $arParams */
/** @var array $arResult */

$links = $arResult['LINKS'];
$hasLinks = $arResult['HAS_LINKS'];

$containerNodeId = $arParams['CONTAINER_NODE_ID'];
$destroyEventName = $arParams['JS_DESTROY_EVENT_NAME'];
$accountId = $arParams['ACCOUNT_ID'] ?: $arResult['ACCOUNT_ID'];
$formId = $arParams['FORM_ID'];
$crmFormId = $arParams['CRM_FORM_ID'];
$provider = $arParams['PROVIDER'];
$data = $arResult['DATA'];
$type = htmlspecialcharsbx($provider['TYPE']);
$typeUpped = strtoupper($type);

$crmFormSuccessUrl = $data['CRM_FORM_RESULT_SUCCESS_URL'];
?>
<script id="template-crm-ads-dlg-settings" type="text/html">
	<div class="crm-ads-forms-block">
		<div class="crm-ads-forms-title">
			<?=Loc::getMessage('CRM_ADS_LEADADS_TITLE')?>
			<a target="_blank" href="https://www.facebook.com/business/a/lead-ads	">
				<?=Loc::getMessage('CRM_ADS_LEADADS_MORE')?>
			</a>
		</div>
	</div>

	<div data-bx-ads-block="loading" style="display: none;" class="crm-ads-forms-block">
		<div class="crm-ads-forms-ldr-user-loader-item">
			<div class="crm-ads-forms-ldr-loader">
				<svg class="crm-ads-forms-ldr-circular" viewBox="25 25 50 50">
					<circle class="crm-ads-forms-ldr-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
				</svg>
			</div>
		</div>
	</div>

	<div data-bx-ads-block="login" style="display: none;" class="crm-ads-forms-block">
		<div class="crm-ads-forms-social crm-ads-forms-social-<?=$type?>">
			<a
				target="_blank"
				href="javascript: void(0);"
				onclick="BX.util.popup('<?=htmlspecialcharsbx($provider['AUTH_URL'])?>', 800, 600);"
				class="webform-small-button webform-small-button-transparent">
				<?=Loc::getMessage('CRM_ADS_LEADADS_LOGIN')?>
			</a>
		</div>
	</div>


	<div data-bx-ads-block="auth" style="display: none;">
		<div class="crm-ads-forms-block">
			<div class="crm-ads-forms-social crm-ads-forms-social-<?=$type?>">
				<div class="crm-ads-forms-social-avatar">
					<div data-bx-ads-auth-avatar="" class="crm-ads-forms-social-avatar-icon"></div>
				</div>
				<div class="crm-ads-forms-social-user">
					<a target="_top" data-bx-ads-auth-link="" data-bx-ads-auth-name="" class="crm-ads-forms-social-user-link" title=""></a>
				</div>
				<div class="crm-ads-forms-social-shutoff">
					<span data-bx-ads-auth-logout="" class="crm-ads-forms-social-shutoff-link"><?=Loc::getMessage('CRM_ADS_LEADADS_LOGOUT')?></span>
				</div>
			</div>
		</div>
	</div>


	<div data-bx-ads-block="refresh" style="display: none;">
		<div class="crm-ads-forms-block crm-ads-forms-wrapper crm-ads-forms-wrapper-center">
			<?=Loc::getMessage('CRM_ADS_LEADADS_REFRESH_TEXT')?>
			<br>
			<br>
			<span data-bx-ads-refresh-btn="" class="webform-small-button webform-small-button-transparent">
				<?=Loc::getMessage('CRM_ADS_LEADADS_REFRESH')?>
			</span>
		</div>
	</div>


	<div data-bx-ads-block="main" style="display: none;">
		<div class="crm-ads-forms-block crm-ads-forms-wrapper">

			<div class="crm-ads-forms-block">
				<div class="crm-ads-forms-title-full"><?=Loc::getMessage('CRM_ADS_LEADADS_FORM_NAME')?>:</div>

				<table class="crm-ads-forms-table">
					<tr>
						<td>
							<input data-bx-ads-form-name="" value="<?=htmlspecialcharsbx($data['CRM_FORM_NAME'])?>" class="crm-ads-forms-input">
						</td>
					</tr>
				</table>
			</div>

			<div class="crm-ads-forms-block">
				<div class="crm-ads-forms-title-full"><?=Loc::getMessage('CRM_ADS_LEADADS_FORM_SUCCESS_URL')?>:</div>

				<table class="crm-ads-forms-table">
					<tr>
						<td>
							<input data-bx-ads-form-url="" value="<?=htmlspecialcharsbx($crmFormSuccessUrl)?>" placeholder="https://www.example.com/success.html" class="crm-ads-forms-input">
						</td>
					</tr>
				</table>
			</div>

			<div class="crm-ads-forms-block">
				<div class="crm-ads-forms-title-full"><?=Loc::getMessage('CRM_ADS_LEADADS_SELECT_ACCOUNT')?>:</div>

				<table class="crm-ads-forms-table crm-ads-forms-ldr-table">
					<tr>
						<td>
							<select disabled name="ACCOUNT_ID" data-bx-ads-account="" class="crm-ads-forms-dropdown">
							</select>
						</td>
						<td>
							<div data-bx-ads-account-loader="" class="crm-ads-forms-ldr-loader-sm" style="display: none;">
								<svg class="crm-ads-forms-ldr-circular" viewBox="25 25 50 50">
									<circle class="crm-ads-forms-ldr-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
								</svg>
							</div>
						</td>
						<td align="right">
							<a class="crm-ads-link-list" href="<?=htmlspecialcharsbx($provider['URL_ACCOUNT_LIST'])?>" target="_blank">
								<?=Loc::getMessage('CRM_ADS_LEADADS_LIST')?>
							</a>
							<a href="<?=htmlspecialcharsbx($provider['URL_ACCOUNT_LIST'])?>" target="_blank">
								<span class="crm-ads-link-copy"></span>
							</a>
						</td>
					</tr>
				</table>
			</div>

			<div data-bx-ads-account-not-found="" class="crm-ads-forms-block" style="display: none;">
				<div class="crm-ads-forms-alert">
					<?=Loc::getMessage(
						'CRM_ADS_LEADADS_ERROR_NO_ACCOUNTS',
						array(
							'%name%' => '<a data-bx-ads-audience-create-link="" href="' . htmlspecialcharsbx($provider['URL_AUDIENCE_LIST']) . '" '
								. 'target="_blank">'
								. Loc::getMessage('CRM_ADS_LEADADS_CABINET_' . $typeUpped)
								.'</a>'
						)
					)?>
				</div>
			</div>

			<div class="crm-ads-forms-block">
				<table class="crm-ads-forms-table">
					<tr>
						<td>
							<span data-bx-ads-btn-export="" data-bx-state="<?=($hasLinks ? '1' : '')?>"
								class="webform-small-button" style="display: none;"
								data-bx-text-send="<?=Loc::getMessage('CRM_ADS_LEADADS_BUTTON_EXPORT_' . $typeUpped)?>"
								data-bx-text-disconnect="<?=Loc::getMessage('CRM_ADS_LEADADS_BUTTON_UNLINK_' . $typeUpped)?>"
								data-bx-text-success="<?=Loc::getMessage('CRM_ADS_LEADADS_BUTTON_EXPORTED_SUCCESS')?>"
							>
								<?=Loc::getMessage('CRM_ADS_LEADADS_BUTTON_EXPORT_' . $typeUpped)?>
							</span>

							<span data-bx-ads-btn-date="" class="crm-ads-btn-date" data-bx-text-now="<?=Loc::getMessage('CRM_ADS_LEADADS_NOW')?>">
								<?=Loc::getMessage('CRM_ADS_LEADADS_IS_LINKED')?> <?=htmlspecialcharsbx($arResult['LINK_DATE'])?>
							</span>
						</td>
						<td class="crm-ads-vertical-align-top">
							<span data-bx-ads-btn-hint="" class="crm-ads-hint" data-bx-text-enabled="<?=Loc::getMessage('CRM_ADS_LEADADS_AFTER_ENABLE_' . $typeUpped)?>" data-bx-text-disabled="<?=Loc::getMessage('CRM_ADS_LEADADS_AFTER_DISABLE_' . $typeUpped)?>"></span>
						</td>
					</tr>
				</table>
			</div>
		</div>

	</div>

</script>

<script>
	BX.ready(function () {

		var r = (Date.now()/1000|0);
		BX.loadCSS('<?=$this->GetFolder()?>/configurator.css?' + r);
		BX.loadScript('<?=$this->GetFolder()?>/configurator.js?' + r, function()
		{
			new CrmAdsLeadAds(<?=\Thurly\Main\Web\Json::encode(array(
				'provider' => $provider,
				'accountId' => $accountId,
				'formId' => $formId,
				'crmFormId' => $crmFormId,
				'data' => $data,
				'containerId' => $containerNodeId,
				'destroyEventName' => $destroyEventName,
				'actionRequestUrl' => $this->getComponent()->getPath() . '/ajax.php',
				'mess' => array(
					'errorAction' => Loc::getMessage('CRM_ADS_LEADADS_ERROR_ACTION'),
					'dlgBtnClose' => Loc::getMessage('CRM_ADS_LEADADS_CLOSE'),
					'dlgBtnCancel' => Loc::getMessage('CRM_ADS_LEADADS_APPLY'),
				)
			))?>);
		});

	});
</script>