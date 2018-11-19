<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var \Thurly\Main\ErrorCollection $arResult['ERRORS']
 * @var CThurlyComponentTemplate $this
 * @global CMain $APPLICATION
 */

\Thurly\Main\Localization\Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/rest/scope.php');

if($arResult['ERRORS']->count() > 0)
{
	ShowError(implode('<br />', $arResult['ERRORS']->toArray()));
}
?>
<form action="<?=POST_FORM_ACTION_URI?>" method="post">
<?=thurly_sessid_post()?>

<table class="content-edit-form">
	<tr>
		<td style="padding-bottom: 20px;">
<?=\Thurly\Main\Localization\Loc::getMessage('RAPC_DESC', array(
	'#SITE_TITLE#' => \Thurly\Main\Text\Converter::getHtmlConverter()->encode($arResult['CLIENT_INFO']['TITLE']),
	'#AP_LINK#' => $arResult['AP_MANAGE_URL'],
))?>
		</td>
		<td>

		</td>
	</tr>
<?
if(count($arResult['CLIENT_ACCESS']) > 0)
{
?>
	<tr>
		<td style="padding-bottom: 20px;">
			<?=\Thurly\Main\Localization\Loc::getMessage('RAPC_ACCESS')?>
			<ul>
<?
	foreach($arResult['CLIENT_ACCESS'] as $scope)
	{
		$scopeName = GetMessage("REST_SCOPE_".toUpper($scope));
		if(strlen($scopeName) <= 0)
		{
			$scopeName = $scope;
		}

		$scopeName .= ' <small>('.$scope.')</small>';
		echo '<li>'.$scopeName.'</li>';
	}
?>
			</ul>
		</td>
	</tr>
<?
}
?>
	<tr>
		<td class="content-edit-form-buttons" style="border-top: 1px #eaeae1 solid; text-align:center">
			<input type="submit" name="agree" value="<?=\Thurly\Main\Localization\Loc::getMessage('RAPC_BTN_AGREE')?>" class="webform-button webform-button-create">
			<a href="/" class="webform-button-link webform-button-link-cancel"><?=\Thurly\Main\Localization\Loc::getMessage('RAPC_BTN_DISAGREE')?></a>
		</td>
	</tr>
</table>
</form>
