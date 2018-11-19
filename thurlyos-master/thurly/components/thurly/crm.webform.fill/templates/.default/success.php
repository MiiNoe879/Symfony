<?php
use Thurly\Main\Context;
use Thurly\Main\Text\HtmlFilter;
use Thurly\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/** @var CThurlyComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */

Loc::loadMessages(__DIR__ . '/template.php');
$text = $arResult['FORM']['RESULT_SUCCESS_TEXT'] ?: Loc::getMessage('CRM_WEBFORM_FILL_RESULT_SENT');
?>
<div class="crm-webform-success-block">
	<?=htmlspecialcharsbx($text)?>
</div>