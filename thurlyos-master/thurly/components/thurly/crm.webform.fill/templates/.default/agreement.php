<?php
use Thurly\Main\Context;
use Thurly\Main\Text\HtmlFilter;
use Thurly\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/** @var CThurlyComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */

$licenceText = '';
if($arResult['USER_CONSENT']['IS_USED'])
{
	$licenceText = nl2br(htmlspecialcharsbx($arResult['USER_CONSENT']['TEXT']));
}

?>
<div class="crm-webform-license-wrapper">
	<?=$licenceText?>
</div>