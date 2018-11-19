<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

/** @var array $arParams */
$arParams['INPUT_NAME_PREFIX'] = isset($arParams['INPUT_NAME_PREFIX']) ? $arParams['INPUT_NAME_PREFIX'] : '';

$this->IncludeComponentTemplate();