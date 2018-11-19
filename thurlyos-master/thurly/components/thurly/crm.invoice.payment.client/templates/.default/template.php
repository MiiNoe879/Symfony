<?
use Thurly\Main\Localization\Loc,
	Thurly\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

/** @var $arResult array */
/** @global APPLICATION CMain */
global $APPLICATION;

if(isset($arResult['CUSTOMIZATION']) && $arResult['CUSTOMIZATION']['BACKGROUND_IMAGE_PATH'])
{
	$APPLICATION->SetPageProperty(
		"BodyClass",
		$APPLICATION->GetPageProperty("BodyClass") . ' page-theme-image'
	);
	$additionalCssString = ".page-theme-image {" .
		"\n" . 'background-image: url("' . $arResult['CUSTOMIZATION']['BACKGROUND_IMAGE_PATH'] . '");' .
		"\n" . '}';

	\Thurly\Main\Page\Asset::getInstance()->addString(
		'<style type="text/css">' . "\n" . $additionalCssString . "\n" . '</style>'
	);
}

if (!empty($arResult["errorMessage"]))
{
	if (!is_array($arResult["errorMessage"]))
	{
		?>
		<div id="pub-template-error" class="error-block" style="display: block;">
			<div id="pub-template-error-title" class="error-block-title"><?=$arResult["errorMessage"]?></div>
		</div>
		<?
	}
	else
	{
		foreach ($arResult["errorMessage"] as $errorMessage)
		{
			?>
			<div id="pub-template-error" class="error-block" style="display: block;">
				<div id="pub-template-error-title" class="error-block-title"><?=$errorMessage?></div>
			</div>
			<?
		}
	}
}
else
{
	Asset::getInstance()->addJs("/thurly/js/main/utils.js");
	?>
	<div id="crm-invoice-payment-client-wrapper" class="crm-invoice-payment-client-wrapper <?=(!isset($arResult['PAY_SYSTEM_PAID_ARRAY']) && !array_key_exists('PAY_SYSTEM_TEMPLATE', $arResult))?"crm-invoice-payment-deliver":""?>">
		<div class="crm-invoice-payment-client-template">
			<?
			if ($arParams['IS_AJAX_PAY'] === 'Y')
			{
				echo $arResult['BILL_TEMPLATE'];
			}
			elseif (empty($arResult['PAY_SYSTEM_PAID_ARRAY']) && !array_key_exists('PAY_SYSTEM_TEMPLATE', $arResult))
			{
				?>
				<iframe id="crm-invoice-payment-template-frame" frameborder="0"></iframe>
				<?
			}
			?>
		</div>
		<?
		if ($arParams['IS_AJAX_PAY'] !== 'Y' && empty($arResult['PAY_SYSTEM_PAID_ARRAY']) && !array_key_exists('PAY_SYSTEM_TEMPLATE', $arResult))
		{
			?>
			<div class="crm-invoice-payment-bar">
				<div class="crm-invoice-payment-button">
					<a class="crm-invoice-payment-button-download <?=(LANGUAGE_ID === 'de' ? "crm-invoice-payment-button-de":"")?>"
					   href="<?=$arResult['BUTTONS']['SAVE']?>" download><?=Loc::getMessage('CIPC_TPL_BUTTON_LOAD')?></a>
					<a class="crm-invoice-payment-button-print <?=(LANGUAGE_ID === 'de' ? "crm-invoice-payment-button-de":"")?>"
					   onclick="<?=$arResult['BUTTONS']['PRINT']?>"><?=Loc::getMessage('CIPC_TPL_BUTTON_PRINT')?></a>
				</div>
				<div class="crm-invoice-payment-total">
					<div class="crm-invoice-payment-total-title"><?=Loc::getMessage('CIPC_TPL_SUM_PAYMENT')?></div>
					<div class="crm-invoice-payment-total-sum"><?=$arResult['SUM']?></div>
				</div>
				<?
				if (!empty($arResult['PAYSYSTEMS_LIST']))
				{
					?>
					<div class="crm-invoice-payment-system">
						<div class="crm-invoice-payment-system-title"><?=Loc::getMessage('CIPC_TPL_PAY_FOR')?>:</div>
						<div class="crm-invoice-payment-system-array crm-invoice-payment-client-pp-company-graf-container">
							<?
							foreach ($arResult['PAYSYSTEMS_LIST'] as $key => $paySystem)
							{
								?>
								<div class="crm-invoice-payment-system-image-block">
									<div class="crm-invoice-payment-system-image"
										 style="background-image: url(<?=$paySystem['LOGOTIP']?>);background-size:contain;">
										<input id="id-1"
											   name="PAY_SYSTEM_ID"
											   value="<?=$paySystem['ID']?>"
											   type="hidden">
									</div>
									<div class="crm-invoice-payment-system-name" >
										<?=$paySystem['NAME']?>
									</div>
								</div>
								<?
							}
							?>
						</div>
					</div>
					<div class="crm-invoice-payment-system-template"></div>
					<div>
						<a class="crm-invoice-payment-system-return-list"><?=Loc::getMessage('CIPC_TPL_RETURN_LIST')?></a>
					</div>
					<?
				}
				if (!empty($arResult['BANK_PROPERTIES']))
				{
					?>
					<div class="crm-invoice-payment-requisites">
						<div class="crm-invoice-payment-requisites-title"><?=Loc::getMessage('CIPC_TPL_BANK_PROPS')?>:</div>
						<table class="crm-invoice-payment-requisites-table">
							<?
							foreach ($arResult['BANK_PROPERTIES'] as $keyName => $property)
							{
								if ($keyName === 'SELLER_COMPANY_NAME')
								{
									?>
									<tr>
										<td class="crm-invoice-payment-requisites-dark" colspan="2">
											<?=$property['VALUE']?>
										</td>
									</tr>
									<?
								}
								elseif (!empty($property['VALUE']))
								{
									?>
									<tr>
										<td><?=$property['NAME']?></td>
										<td><?=$property['VALUE']?></td>
									</tr>
									<?
								}
							}
							?>
						</table>
					</div>
					<?
				}
				$javascriptParams = array(
					"url" => "/pub/payment.php",
					"templateFolder" => CUtil::JSEscape($templateFolder),
					"accountNumber" => $arParams['ACCOUNT_NUMBER'],
					"hash" => $arParams['HASH'],
					"templateBill" => $arResult['BILL_TEMPLATE']
				);
				$javascriptParams = CUtil::PhpToJSObject($javascriptParams);
				?>
				<script>
					var sc = new BX.crmInvoicePaymentClient(<?=$javascriptParams?>);
				</script>
			</div>
			<?
			if (in_array(LANGUAGE_ID, array('ru', 'ua')))
			{
				$logoClass = 'crm-invoice-payment-copy-logo';
			}
			else
			{
				$logoClass = 'crm-invoice-payment-copy-logo-en';
			}
			if ( \Thurly\Main\ModuleManager::isModuleInstalled('thurlyos'))
			{
				?>
				<div class="crm-invoice-payment-copy">
					<?
						if (
							!\Thurly\Crm\Settings\InvoiceSettings::allowDisableSign() ||
							\Thurly\Crm\Settings\InvoiceSettings::getCurrent()->getEnableSignFlag()
						)
						{
							echo Loc::getMessage('CIPC_TPL_THURLY_SIGN',array("#LOGO#"=>'<a href='.$arResult['BUTTONS']['B24'].'><span class='.$logoClass.'></span></a>'));
						}
					?>
				</div>
				<?
			}
		}
		if (isset($arResult['PAY_SYSTEM_PAID_ARRAY']))
		{
			?>
			<div class="crm-invoice-payment-paid-table-wrapper">
				<table class="crm-invoice-payment-paid-table">
					<tr>
						<td class="crm-invoice-payment-paid-count-name" colspan="2">
							<?=Loc::getMessage('CIPC_TPL_PAID_TITLE',array(
								"#INVOICE_ID#"=>$arResult['PAY_SYSTEM_PAID_ARRAY']['ACCOUNT_NUMBER'],
								"#DATE_BILL#"=>$arResult['PAY_SYSTEM_PAID_ARRAY']['DATE_BILL']
							))?>
						</td>
					</tr>
					<tr class="crm-invoice-payment-paid-table-empty-row"></tr>
					<tr>
						<td class="crm-invoice-payment-paid-count">
							<?=Loc::getMessage('CIPC_TPL_PAID_SYSTEM')?>:
						</td>
						<td>
							<?=$arResult['PAY_SYSTEM_PAID_ARRAY']['PAY_SYSTEM_NAME']?>
						</td>
					</tr>
					<tr>
						<td class="crm-invoice-payment-paid-count">
							<?=Loc::getMessage('CIPC_TPL_PAID_SUM')?>:
						</td>
						<td>
							<?=$arResult['SUM']?>
						</td>
					</tr>
					<tr>
						<td class="crm-invoice-payment-paid-count">
							<?=Loc::getMessage('CIPC_TPL_PAID_DATE')?>:
						</td>
						<td>
							<?=$arResult['PAY_SYSTEM_PAID_ARRAY']['DATE_PAID']?>
						</td>
					</tr>
				</table>
			</div>
			<?
		}
		?>
		<?if (array_key_exists('PAY_SYSTEM_TEMPLATE', $arResult)):?>
			<div class="crm-invoice-payment-paid-table-wrapper">
				<table class="crm-invoice-payment-paid-table">
					<tr>
						<td class="crm-invoice-payment-paid-count-name" colspan="2">
							<?=$arResult['PAY_SYSTEM_TEMPLATE'];?>
						</td>
					</tr>
				</table>
			</div>
		<?endif;?>
	</div>
	<?
}
?>



