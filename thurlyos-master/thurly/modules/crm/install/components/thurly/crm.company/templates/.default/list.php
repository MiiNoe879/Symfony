<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/** @var array $arResult */
$cpID = ($arResult['MYCOMPANY_MODE'] === 'Y') ? 'MYCOMPANY_LIST' : 'COMPANY_LIST';
$cpActiveItemID = ($arResult['MYCOMPANY_MODE'] === 'Y') ? '' : 'COMPANY';

$isMyCompanyMode = (isset($arResult['MYCOMPANY_MODE']) && $arResult['MYCOMPANY_MODE'] === 'Y');

/** @var CMain $APPLICATION */
$APPLICATION->IncludeComponent(
	'thurly:crm.control_panel',
	'',
	array(
		'ID' => $cpID,
		'ACTIVE_ITEM_ID' => $cpActiveItemID,
		'PATH_TO_COMPANY_LIST' => (isset($arResult['PATH_TO_COMPANY_LIST']) && !$isMyCompanyMode) ? $arResult['PATH_TO_COMPANY_LIST'] : '',
		'PATH_TO_COMPANY_EDIT' => (isset($arResult['PATH_TO_COMPANY_EDIT']) && !$isMyCompanyMode) ? $arResult['PATH_TO_COMPANY_EDIT'] : '',
		'PATH_TO_CONTACT_LIST' => isset($arResult['PATH_TO_CONTACT_LIST']) ? $arResult['PATH_TO_CONTACT_LIST'] : '',
		'PATH_TO_CONTACT_EDIT' => isset($arResult['PATH_TO_CONTACT_EDIT']) ? $arResult['PATH_TO_CONTACT_EDIT'] : '',
		'PATH_TO_DEAL_LIST' => isset($arResult['PATH_TO_DEAL_LIST']) ? $arResult['PATH_TO_DEAL_LIST'] : '',
		'PATH_TO_DEAL_EDIT' => isset($arResult['PATH_TO_DEAL_EDIT']) ? $arResult['PATH_TO_DEAL_EDIT'] : '',
		'PATH_TO_LEAD_LIST' => isset($arResult['PATH_TO_LEAD_LIST']) ? $arResult['PATH_TO_LEAD_LIST'] : '',
		'PATH_TO_LEAD_EDIT' => isset($arResult['PATH_TO_LEAD_EDIT']) ? $arResult['PATH_TO_LEAD_EDIT'] : '',
		'PATH_TO_QUOTE_LIST' => isset($arResult['PATH_TO_QUOTE_LIST']) ? $arResult['PATH_TO_QUOTE_LIST'] : '',
		'PATH_TO_QUOTE_EDIT' => isset($arResult['PATH_TO_QUOTE_EDIT']) ? $arResult['PATH_TO_QUOTE_EDIT'] : '',
		'PATH_TO_INVOICE_LIST' => isset($arResult['PATH_TO_INVOICE_LIST']) ? $arResult['PATH_TO_INVOICE_LIST'] : '',
		'PATH_TO_INVOICE_EDIT' => isset($arResult['PATH_TO_INVOICE_EDIT']) ? $arResult['PATH_TO_INVOICE_EDIT'] : '',
		'PATH_TO_REPORT_LIST' => isset($arResult['PATH_TO_REPORT_LIST']) ? $arResult['PATH_TO_REPORT_LIST'] : '',
		'PATH_TO_DEAL_FUNNEL' => isset($arResult['PATH_TO_DEAL_FUNNEL']) ? $arResult['PATH_TO_DEAL_FUNNEL'] : '',
		'PATH_TO_EVENT_LIST' => isset($arResult['PATH_TO_EVENT_LIST']) ? $arResult['PATH_TO_EVENT_LIST'] : '',
		'PATH_TO_PRODUCT_LIST' => isset($arResult['PATH_TO_PRODUCT_LIST']) ? $arResult['PATH_TO_PRODUCT_LIST'] : '',
		'MYCOMPANY_MODE' => ($arResult['MYCOMPANY_MODE'] === 'Y' ? 'Y' : 'N'),
		'PATH_TO_COMPANY_WIDGET' => isset($arResult['PATH_TO_COMPANY_WIDGET']) ? $arResult['PATH_TO_COMPANY_WIDGET'] : '',
		'PATH_TO_COMPANY_PORTRAIT' => isset($arResult['PATH_TO_COMPANY_PORTRAIT']) ? $arResult['PATH_TO_COMPANY_PORTRAIT'] : ''
	),
	$component
);

if(!Thurly\Crm\Integration\ThurlyOSManager::isAccessEnabled(CCrmOwnerType::Company))
{
	$APPLICATION->IncludeComponent('thurly:thurlyos.business.tools.info', '', array());
}
else
{
	$isThurlyOSTemplate = SITE_TEMPLATE_ID === 'thurlyos';
	if($isThurlyOSTemplate)
	{
		$this->SetViewTarget('below_pagetitle', 0);
	}

	if (!$isMyCompanyMode)
	{
		$APPLICATION->IncludeComponent(
			'thurly:crm.entity.counter.panel',
			'',
			array(
				'ENTITY_TYPE_NAME' => CCrmOwnerType::CompanyName,
				'EXTRAS' => array(),
				'PATH_TO_ENTITY_LIST' => $arResult['PATH_TO_COMPANY_LIST']
			)
		);
	}

	if($isThurlyOSTemplate)
	{
		$this->EndViewTarget();
	}

	$APPLICATION->ShowViewContent('crm-grid-filter');

	$APPLICATION->IncludeComponent(
		'thurly:crm.company.menu',
		'',
		array(
			'PATH_TO_COMPANY_LIST' => $arResult['PATH_TO_COMPANY_LIST'],
			'PATH_TO_COMPANY_SHOW' => $arResult['PATH_TO_COMPANY_SHOW'],
			'PATH_TO_COMPANY_EDIT' => $arResult['PATH_TO_COMPANY_EDIT'],
			'PATH_TO_COMPANY_IMPORT' => $arResult['PATH_TO_COMPANY_IMPORT'],
			'PATH_TO_COMPANY_DEDUPE' => $arResult['PATH_TO_COMPANY_DEDUPE'],
			'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
			'ELEMENT_ID' => $arResult['VARIABLES']['company_id'],
			'TYPE' => 'list',
			'MYCOMPANY_MODE' => ($arResult['MYCOMPANY_MODE'] === 'Y' ? 'Y' : 'N') 
		),
		$component
	);

	if(\Thurly\Main\ModuleManager::isModuleInstalled('rest'))
	{
		$APPLICATION->IncludeComponent(
			'thurly:app.placement',
			'menu',
			array(
				'PLACEMENT' => "CRM_COMPANY_LIST_MENU",
				"PLACEMENT_OPTIONS" => array(),
				'INTERFACE_EVENT' => 'onCrmCompanyMenuInterfaceInit',
				'MENU_EVENT_MODULE' => 'crm',
				'MENU_EVENT' => 'onCrmCompanyListItemBuildMenu',
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}

	$APPLICATION->IncludeComponent(
		'thurly:crm.company.list',
		'',
		array(
			'COMPANY_COUNT' => '20',
			'PATH_TO_COMPANY_LIST' => $arResult['PATH_TO_COMPANY_LIST'],
			'PATH_TO_COMPANY_SHOW' => $arResult['PATH_TO_COMPANY_SHOW'],
			'PATH_TO_COMPANY_EDIT' => $arResult['PATH_TO_COMPANY_EDIT'],
			'PATH_TO_COMPANY_WIDGET' => $arResult['PATH_TO_COMPANY_WIDGET'],
			'PATH_TO_CONTACT_EDIT' => $arResult['PATH_TO_CONTACT_EDIT'],
			'PATH_TO_DEAL_EDIT' => $arResult['PATH_TO_DEAL_EDIT'],
			'NAME_TEMPLATE' => $arParams['NAME_TEMPLATE'],
			'MYCOMPANY_MODE' => ($arResult['MYCOMPANY_MODE'] === 'Y' ? 'Y' : 'N'),
			'NAVIGATION_CONTEXT_ID' => $arResult['NAVIGATION_CONTEXT_ID']
		),
		$component
	);
}
?>